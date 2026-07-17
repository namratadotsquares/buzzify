<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RssFeed;
use App\Models\Category;
use App\Models\BlogCategory;
use App\Models\Blog;
use App\Models\BlogImages;
use App\Models\BlogTranslation;
use App\Models\BlogActionLog;
use App\Models\SiteContent;
use Illuminate\Support\Facades\Validator;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use UploadImage as Image;

class NewsApiController extends Controller
{

	function __construct()
	{
		$this->middleware('permission:news-api-post-list|news-api-post-save', ['only' => ['index', 'saveNewsApiPost']]);
		$this->middleware('permission:news-api-post-list', ['only' => ['index']]);
		$this->middleware('permission:news-api-post-save', ['only' => ['saveNewsApiPost']]);
	}

	/**
	 * Generate an image using OpenAI Images API from title/description and save variants.
	 */
	public function generateAndSaveImageFromOpenAI($title, $description, $blog_id)
	{
		try {
			$prompt = "Create a photorealistic news thumbnail image (no watermark or text) that represents the following article. Title: " . trim($title) . ". Description: " . trim($description) . ". Use realistic lighting and natural colors. Avoid cartoon or illustration styles. The subject should fill the frame edge-to-edge with no borders or empty space. Use a 3:2 aspect ratio.";
			$apiKey = env('OPENAI_API_KEY', env('chatgpt_key'));
			$imageSize = env('OPENAI_IMAGE_SIZE', '1536x1024');
			$response = Http::withToken($apiKey)->post('https://api.openai.com/v1/images/generations', [
				'model' => env('OPENAI_IMAGE_MODEL', 'gpt-image-1-mini'),
				'prompt' => $prompt,
				'n' => 1,
				'size' => $imageSize,
			]);

			if ($response->failed()) {
				$body = $response->body();
				\Helpers::markOpenAiQuotaExpiredIfNeeded($body);
				Log::warning('OpenAI image generation failed: ' . $body);
				return false;
			}

			\Helpers::clearOpenAiQuotaExpired();
			$body = $response->json();
			$imageData = null;
			if (isset($body['data'][0]['b64_json'])) {
				$imageData = base64_decode($body['data'][0]['b64_json']);
			}
			elseif (isset($body['data'][0]['url'])) {
				$url = $body['data'][0]['url'];
				$imageData = @file_get_contents($url);
			}

			if ($imageData) {
				$basePath = public_path('/upload/blog/banner/');
				@mkdir($basePath . 'original/', 0755, true);
				@mkdir($basePath . '800/', 0755, true);
				@mkdir($basePath . '360/', 0755, true);
				@mkdir($basePath . '200/', 0755, true);
				$name = time() . rand() . '.jpg';

				$img = Image::make($imageData);
				$img = $this->resizeToCanvas($img, 1200, 800);
				$encoded = $this->encodeJpegWithinSize($img, 120 * 1024, 600 * 1024);
				if (!$encoded) {
					Log::warning('AI image encode failed.');
					return false;
				}
				$destination = $basePath . 'original/' . $name;
				file_put_contents($destination, $encoded['data']);

				$this->resizeToCanvas(Image::make($destination), 800, 533)->save($basePath . '800/' . $name, 85, 'jpg');
				$this->resizeToCanvas(Image::make($destination), 360, 240)->save($basePath . '360/' . $name, 85, 'jpg');
				$this->resizeToCanvas(Image::make($destination), 200, 133)->save($basePath . '200/' . $name, 85, 'jpg');

				BlogImages::insert([
					'blog_id' => $blog_id,
					'image' => $name,
					'created_at' => now()
				]);
				return true;
			}
			Log::warning('OpenAI image generation returned empty image data.');
			return false;
		}
		catch (\Exception $e) {
			Log::warning('OpenAI image generation failed: ' . $e->getMessage());
			return false;
		}
	}

	private function encodeJpegWithinSize($img, $minBytes, $maxBytes)
	{
		$best = null;
		$bestDiff = PHP_INT_MAX;
		for ($q = 90; $q >= 10; $q -= 5) {
			$data = (string)$img->encode('jpg', $q);
			$size = strlen($data);
			if ($size >= $minBytes && $size <= $maxBytes) {
				return ['data' => $data, 'quality' => $q, 'size' => $size];
			}
			$diff = min(abs($size - $minBytes), abs($size - $maxBytes));
			if ($diff < $bestDiff) {
				$bestDiff = $diff;
				$best = ['data' => $data, 'quality' => $q, 'size' => $size];
			}
		}
		return $best;
	}

	private function resizeToCanvas($img, $width, $height)
	{
		$img->resize($width, $height, function ($constraint) {
			$constraint->aspectRatio();
			$constraint->upsize();
		});
		// Ensure exact target dimensions when aspect ratio already matches closely.
		if (method_exists($img, 'width') && method_exists($img, 'height')) {
			$currentW = (int)$img->width();
			$currentH = (int)$img->height();
			if ($currentW > 0 && $currentH > 0 && ($currentW !== (int)$width || $currentH !== (int)$height)) {
				$currentRatio = $currentW / $currentH;
				$targetRatio = ((int)$width) / ((int)$height);
				if (abs($currentRatio - $targetRatio) < 0.02) {
					$img->resize($width, $height);
				}
			}
		}
		return $img;
	}

	private function fetchImageContentFromUrl($url)
	{
		if (!$url) {
			return null;
		}
		try {
			// Parse the URL to use as a Referer
			$parsed = parse_url($url);
			$referer = (isset($parsed['scheme']) ? $parsed['scheme'] : 'https') . '://' . (isset($parsed['host']) ? $parsed['host'] : '');

			$response = Http::withOptions([
				'allow_redirects' => true,
				'verify' => false, // Sometimes SSL cert issues occur with Indian news CDNs
			])->timeout(15)->get($url);

			if (!$response->successful()) {
				Log::warning("Image fetch failed for $url. Status: " . $response->status());
				return null;
			}

			$contentType = $response->header('Content-Type');
			if (!$contentType || stripos($contentType, 'image/') !== 0) {
				Log::warning("Link is not a valid image ($contentType) for $url");
				return null;
			}

			$body = $response->body();
			if (!$body || strlen($body) === 0) {
				return null;
			}

			// Validate image signature
			$info = @getimagesizefromstring($body);
			if (!$info || !isset($info[0], $info[1]) || $info[0] <= 0 || $info[1] <= 0) {
				Log::warning("Downloaded content for $url is not a valid image according to getimagesize");
				return null;
			}
			return $body;
		}
		catch (\Exception $e) {
			Log::error('Image fetch exception for ' . $url . ': ' . $e->getMessage());
			return null;
		}
	}

	private function extractCategoryNames($categorys)
	{
		if (!is_string($categorys) || trim($categorys) === '') {
			return [];
		}

		$normalized = str_replace(["\r\n", "\r", ","], "\n", $categorys);
		$parts = explode("\n", $normalized);
		$cleaned = [];

		foreach ($parts as $part) {
			$item = trim($part);
			$item = preg_replace('/^[-*]\s*/', '', $item);
			$item = trim($item);
			if ($item !== '') {
				$cleaned[] = $item;
			}
		}

		return array_values(array_unique($cleaned));
	}

	private function resolveNewsCategory($categoryName)
	{
		$categoryName = trim((string)$categoryName);

		if ($categoryName !== '') {
			$category = Category::where('status', 1)->where('name', $categoryName)->first();
			if ($category) {
				return $category;
			}

			$category = Category::where('status', 1)
				->whereRaw('LOWER(name) = ?', [strtolower($categoryName)])
				->first();
			if ($category) {
				return $category;
			}
		}

		$others = Category::where('status', 1)->where('name', 'Others')->first();
		if ($others) {
			return $others;
		}

		return Category::where('status', 1)
			->where('name', '!=', 'Personalization')
			->orderBy('id', 'ASC')
			->first();
	}


	/**
	 * Show News API view.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request, $layout = 'side-menu', $theme = 'light')
	{
		// Initialize suggest variables
		$suggestedCategories = [];
		$suggestedSources = [];
		$suggestedLocations = [];
		$suggestedConcepts = [];
		$sourceGroups = [];
		$data = [];




		// Fetch categories for suggestion field
		$catParams = ['type' => 'categories', 'prefix' => ''];
		$categories_result = $this->fetchEventRegistryFilters($catParams, true);
		$suggestedCategories = [];
		if (is_array($categories_result)) {
			$suggestedCategories = array_map(function ($c) {
				$name = isset($c['label']) ? $c['label'] : (isset($c['title']) ? $c['title'] : (isset($c['eng']) ? $c['eng'] : ($c['uri'] ?? '')));
				if (is_array($name)) {
					$name = $name['eng'] ?? reset($name);
				}
				return [
				'id' => $c['wikiUri'] ?? ($c['uri'] ?? ''),
				'name' => $name,
				'parent' => $c['parentUri'] ?? ''
				];
			}, array_slice($categories_result, 0, 50));
		}

		// Fetch sources for suggestion field
		$sourceSuggestParams = ['type' => 'sources', 'prefix' => ''];
		$sources_suggest_result = $this->fetchEventRegistryFilters($sourceSuggestParams, true);
		$suggestedSources = [];
		if (is_array($sources_suggest_result)) {
			$suggestedSources = array_map(function ($s) {
				$name = isset($s['title']) ? $s['title'] : (isset($s['label']) ? $s['label'] : (isset($s['eng']) ? $s['eng'] : ($s['uri'] ?? '')));
				if (is_array($name)) {
					$name = $name['eng'] ?? reset($name);
				}
				return [
				'id' => $s['wikiUri'] ?? ($s['uri'] ?? ''),
				'name' => $name
				];
			}, array_slice($sources_suggest_result, 0, 50));
		}

		// Fetch locations for suggestion field
		$locSuggestParams = ['type' => 'locations', 'prefix' => ''];
		$loc_suggest_result = $this->fetchEventRegistryFilters($locSuggestParams, true);
		if (is_array($loc_suggest_result)) {
			$suggestedLocations = array_map(function ($l) {
				$name = isset($l['label']) ? $l['label'] : (isset($l['title']) ? $l['title'] : (isset($l['eng']) ? $l['eng'] : ($l['uri'] ?? '')));
				if (is_array($name)) {
					$name = $name['eng'] ?? reset($name);
				}
				return [
				'id' => $l['wikiUri'] ?? ($l['uri'] ?? ''),
				'name' => $name
				];
			}, array_slice($loc_suggest_result, 0, 50));
		}

		// Fetch concepts for suggestion field
		$conceptSuggestParams = ['type' => 'concepts', 'prefix' => ''];
		$concept_suggest_result = $this->fetchEventRegistryFilters($conceptSuggestParams, true);
		if (is_array($concept_suggest_result)) {
			$suggestedConcepts = array_map(function ($c) {
				$name = isset($c['label']) ? $c['label'] : (isset($c['title']) ? $c['title'] : (isset($c['eng']) ? $c['eng'] : ($c['uri'] ?? '')));
				if (is_array($name)) {
					$name = $name['eng'] ?? reset($name);
				}
				return [
				'id' => $c['wikiUri'] ?? ($c['uri'] ?? ''),
				'name' => $name
				];
			}, array_slice($concept_suggest_result, 0, 50));
		}

		if ($request->has('submitted')) { // Only fetch when user submits the search form
			$lang = $request->input('language', 'en');
			if (strlen($lang) === 2) {
				$langMapping = [
				    'en' => 'eng', 'hi' => 'hin', 'bn' => 'ben', 'te' => 'tel',
				    'mr' => 'mar', 'ta' => 'tam', 'gu' => 'guj', 'kn' => 'kan',
				    'ml' => 'mal', 'pa' => 'pan', 'or' => 'ori', 'as' => 'asm',
				    'ar' => 'ara', 'de' => 'deu', 'es' => 'spa', 'fr' => 'fra', 
				    'he' => 'heb', 'it' => 'ita', 'nl' => 'nld', 'no' => 'nor', 
				    'pt' => 'por', 'ru' => 'rus', 'se' => 'sme', 'zh' => 'zho'
				];
				$lang = $langMapping[$lang] ?? $lang;
			}

			$perPage = $request->input('per_page', 100);
			$page = $request->input('page', 1);

			$fromDate = $request->input('from', date('Y-m-d'));
			$fromTime = $request->input('from_time', '00:00');

			$toDate = $request->input('to', date('Y-m-d'));
			$toTime = $request->input('to_time', '23:59');

			$params = [
				'dateStart' => $fromDate,
				'timeStart' => $fromTime . ':00',
				'dateEnd' => $toDate,
				'timeEnd' => $toTime . ':59',
				'articlesCount' => $perPage,
				'articlesPage' => $page,
				'articlesSortBy' => $request->input('articlesSortBy', 'date'),
				'resultType' => 'articles'
			];

			// Only add lang filter when a specific language is selected
			if (!empty($lang)) {
				$params['lang'] = $lang;
			}

			if ($request->has('q') && !empty($request->q)) {
				$params['keyword'] = $request->q;
			}

			if ($request->has('sources') && $request->sources != '') {
				$params['sourceUri'] = $request->sources;
			}

			if ($request->has('sourceUri') && !empty($request->sourceUri)) {
				$params['sourceUri'] = $request->sourceUri;
			}

			if ($request->has('categoryUri') && !empty($request->categoryUri)) {
				$params['categoryUri'] = $request->categoryUri;
			}

			if ($request->has('locationUri') && !empty($request->locationUri)) {
				$params['locationUri'] = $request->locationUri;
			}

			if ($request->has('conceptUri') && !empty($request->conceptUri)) {
				$params['conceptUri'] = $request->conceptUri;
			}

			if ($request->has('source_group') && !empty($request->source_group)) {
				$params['sourceGroupUri'] = $request->source_group;
			}

			if ($request->has('location') && $request->location != '') {
				$params['locationUri'] = $request->location;
			}

			if ($request->has('dataType')) {
				$params['dataType'] = $request->input('dataType');
			}

			$result = $this->fetchEventRegistryNews($params, true);

			if (isset($result['articles']['results'])) {
				$startDateTime = strtotime($fromDate . ' ' . $fromTime . ':00');
				$endDateTime = strtotime($toDate . ' ' . $toTime . ':59');

				$result['articles']['results'] = array_filter($result['articles']['results'], function($article) use ($startDateTime, $endDateTime) {
					$pubDateStr = $article['dateTimePub'] ?? ($article['date'] ?? null);
					if (!$pubDateStr) return false;
					$pubTime = strtotime($pubDateStr);
					return $pubTime >= $startDateTime && $pubTime <= $endDateTime;
				});

				$totalResults = $result['articles']['totalResults'] ?? 0;
				$currentPage = $result['articles']['page'] ?? 1;

				// Map Event Registry structure to the format expected by the view
				$mappedData = array_map(function ($article) {
					return [
					'source' => [
					'id' => $article['source']['uri'] ?? null,
					'name' => $article['source']['title'] ?? 'Unknown'
					],
					'author' => $article['authors'][0]['name'] ?? 'Unknown',
					'title' => html_entity_decode($article['title'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'),
					'description' => html_entity_decode($article['body'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'),
					'url' => $article['url'] ?? '',
					'urlToImage' => $article['image'] ?? null,
					'publishedAt' => $article['dateTimePub'] ?? ($article['date'] ?? ''),
					'content' => html_entity_decode($article['body'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')
					];
				}, $result['articles']['results']);

				$data = new LengthAwarePaginator(
					$mappedData,
					$totalResults,
					$perPage,
					$currentPage,
				['path' => $request->url(), 'query' => $request->query()]
					);
			}
		}

		if ($request->has('cat_name') && $request->cat_name != '') {
			$this->saveNewsApiPostsBulk($data, $request->cat_name);
		}

		// Optionally pass the bulk data method...

		$news_api_language = config('constant.news_api_language');
		return view('super-admin/news-api.index', [
			'theme' => $theme,
			'page_name' => 'index',
			'side_menu' => array(),
			'layout' => $layout,
			'data' => $data,
			'suggestedCategories' => $suggestedCategories,
			'suggestedSources' => $suggestedSources,
			'suggestedLocations' => $suggestedLocations,
			'suggestedConcepts' => $suggestedConcepts,
			'news_api_language' => $news_api_language,
			'breadcrumb' => '<a href="' . url('/') . '" class="breadcrumb">' . trans("admin.dashboard") . '</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="' . url('/rss-feed-src/side-menu/light') . '" class="breadcrumb--active">' . trans("admin.news_api") . '</a>'
		]);
	}

	/**
	 * Method to save news api post
	 * @param array $request
	 */
	public function saveNewsApiPost(Request $request)
	{

		try {
			// Handle bulk posts (JSON array) from UI
			if ($request->has('posts') && is_array($request->posts)) {

				$results = $this->saveNewsApiPostsBulk($request->posts);
				// summarize
				$created = 0;
				$skipped = 0;
				$failed = 0;
				foreach ($results as $r) {
					if ($r['status'] == 'created')
						$created++;
					elseif ($r['status'] == 'skipped')
						$skipped++;
					else
						$failed++;
				}
				$message = [];
				if ($created)
					$message[] = "$created added";
				if ($skipped)
					$message[] = "$skipped skipped (already exists)";
				if ($failed)
					$message[] = "$failed failed";
				if (empty($message))
					$message = ['No items processed'];
				return response()->json(['success' => true, 'message' => implode(', ', $message), 'results' => $results]);
			}
			$category_list = Category::where('status', 1)->where('name', '!=', 'Personalization')->where('name', '!=', 'Others')->pluck('name')->toArray();


			shuffle($category_list);
			$categorys = "- " . implode("\n- ", $category_list);
			$post = $request->all();
			if (isset($post['title'])) {
				$post['title'] = html_entity_decode($post['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
			}
			if (isset($post['description'])) {
				$post['description'] = html_entity_decode($post['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
			}
			if (isset($post['content'])) {
				$post['content'] = html_entity_decode($post['content'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
			}
			// $post['urlToImage'] = strtok($post['urlToImage'], '?'); // Use original URL

			$slug = \Helpers::createSlug($post['title'], 'blog', 0, false);
			$existing = Blog::where('slug', $slug)
				->orWhere('url', $post['url'])
				->first();

			if ($existing) {
				// Optional: Log or notify duplicate
				\Log::info('Duplicate news skipped: ' . $post['title']);
				return redirect()->back()->with('success', 'News already exists, skipped duplicate.');
			}






			$cleanContent = preg_replace('/\s?\[\+\d+\schars\]$/', '', $post['content'] ?? '');
			// Ensure it ends with a complete sentence if interrupted
			if (strlen($cleanContent) > 20 && !in_array(substr($cleanContent, -1), ['.', '!', '?'])) {
				$lastPeriod = strrpos($cleanContent, '.');
				if ($lastPeriod !== false && $lastPeriod > (strlen($cleanContent) * 0.7)) {
					$cleanContent = substr($cleanContent, 0, $lastPeriod + 1);
				}
			}
			$datagpt = $this->translateAndCategorizeNewsArticle($post['title'], $cleanContent ?? '', $categorys);
			$category = $this->resolveNewsCategory($datagpt['category'] ?? '');
			// Insert blog
			$inject = [
				'slug' => $slug,
				'title' => $datagpt['english_title'] ?? $post['title'],
				'short_description' => $datagpt['english_description'] ?? $post['description'],
				'url' => $post['url'],
				'source_name' => $post['source'],
				'source_published_at' => isset($post['publishedAt']) && $post['publishedAt'] != '' ?Carbon::parse($post['publishedAt'])->toDateTimeString() : null,
				'description' => $datagpt['english_description'] ?? $post['description'],
				'created_by' => Auth::user()->id,
				'category_id' => $category ? $category->id : null,
				'content_type' => 'text',
				'schedule_date' => now(),
				'status' => 2,
				'blog_accent_code' => setting('blog_accent_code'),
				'created_at' => now(),
			];

			$blog_id = Blog::insertGetId($inject);
			BlogActionLog::record('blog_created', (int)$blog_id, Auth::check() ? (int)Auth::id() : null, [
				'source' => 'news_api',
				'status' => (int)($inject['status'] ?? 0),
				'ai_rewrite' => true,
			]);
			BlogActionLog::record('blog_translated', (int)$blog_id, Auth::check() ? (int)Auth::id() : null, [
				'source' => 'news_api',
				'languages' => ['en', 'hi'],
			]);

			if ($category) {
				BlogCategory::create([
					'category_id' => $category->id,
					'blog_id' => $blog_id
				]);
			}

			// Save translations
			BlogTranslation::updateOrInsert(
			['blog_id' => $blog_id, 'language_code' => 'en'],
			[
				'title' => $datagpt['english_title'] ?? $post['title'],
				'description' => $datagpt['english_description'] ?? $post['description'],
				'created_at' => now(),
			]
			);

			BlogTranslation::updateOrInsert(
			['blog_id' => $blog_id, 'language_code' => 'hi'],
			[
				'title' => $datagpt['hindi_title'] ?? $post['title'],
				'description' => $datagpt['hindi_description'] ?? $cleanContent,
				'created_at' => now(),
			]
			);
			// Process image
			try {
				$fileUrl = isset($post['urlToImage']) ? $post['urlToImage'] : '';
				\Log::info("News API Image processing started for blog $blog_id. URL: " . $fileUrl);

				$needsGenerated = empty($fileUrl);
				$imageContent = $this->fetchImageContentFromUrl($fileUrl);

				\Log::info("Image content fetch result: " . ($imageContent ? 'SUCCESS (' . strlen($imageContent) . ' bytes)' : 'FAILURE'));

				// if (!$needsGenerated && $imageContent) {
				// 	$info = @getimagesizefromstring($imageContent);
				// 	if ($info && isset($info[0], $info[1]) && $info[0] == 200 && $info[1] == 200) {
				// 		$needsGenerated = true;
				// 	}
				// }

				// if ($needsGenerated || !$imageContent) {
				// 	$this->generateAndSaveImageFromOpenAI($post['title'] ?? '', $post['description'] ?? $post['content'] ?? '', $blog_id);
				if ($imageContent) {
					$ext = pathinfo($fileUrl, PATHINFO_EXTENSION) ?: 'jpg';
					// Remove query string from ext if any
					$extArr = explode('?', $ext);
					$ext = $extArr[0] ?: 'jpg';

					$name = time() . rand() . '.' . $ext;
					$basePath = public_path('/upload/blog/banner/');
					@mkdir($basePath . 'original/', 0755, true);
					@mkdir($basePath . '800/', 0755, true);
					@mkdir($basePath . '360/', 0755, true);
					@mkdir($basePath . '200/', 0755, true);
					$destination = $basePath . 'original/' . $name;

					\Log::info("Saving image to: " . $destination);
					file_put_contents($destination, $imageContent);

					$img = Image::make($destination);
					$img->resize(800, null, function ($constraint) {
						$constraint->aspectRatio();
					})->save($basePath . '800/' . $name);
					$img->resize(360, null, function ($constraint) {
						$constraint->aspectRatio();
					})->save($basePath . '360/' . $name);
					// Add 200x200 thumbnail
					$img->fit(200, 200)->save($basePath . '200/' . $name);

					BlogImages::insert([
						'blog_id' => $blog_id,
						'image' => $name,
						'created_at' => now()
					]);
					\Log::info("Image record inserted for blog $blog_id: $name");
				}
				else {
					\Log::warning("No image content to process for blog $blog_id");
				}
			}
			catch (\Exception $e) {
				Log::warning('Image processing check failed: ' . $e->getMessage());
			}

			return redirect()
				->route('blog', ['layout' => 'side-menu', 'theme' => 'light'])
				->with('success', __('message_alerts.success'));
		}
		catch (\Exception $e) {
			\Log::error('Blog Save Failed: ' . $e->getMessage());
			return redirect()->back()->with('error', 'Something went wrong. Please try again.');
		}
	}


	/**
	 * Method to save multiple news api posts in bulk
	 * @param array $posts
	 */
	public function saveNewsApiPostsBulk(array $posts, $cat_name = 'Social')
	{
		$category_list = Category::where('status', 1)->where('name', '!=', 'Personalization')->pluck('name')->toArray();
		$categorys = implode(',', $category_list);
		$userId = Auth::check() ?Auth::user()->id : 1;
		$now = now();
		$results = [];
		foreach ($posts as $index => $post) {
			$clientIdx = isset($post['idx']) ? $post['idx'] : $index;
			try {
				if (isset($post['title'])) {
					$post['title'] = html_entity_decode($post['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
				}
				if (isset($post['description'])) {
					$post['description'] = html_entity_decode($post['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
				}
				if (isset($post['content'])) {
					$post['content'] = html_entity_decode($post['content'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
				}
				$post['urlToImage'] = isset($post['urlToImage']) ? strtok($post['urlToImage'], '?') : '';
				$cleanContent = preg_replace('/\s?\[\+\d+\schars\]$/', '', $post['description'] ?? '');
				// Ensure it ends with a complete sentence if interrupted
				if (strlen($cleanContent) > 20 && !in_array(substr($cleanContent, -1), ['.', '!', '?'])) {
					$lastPeriod = strrpos($cleanContent, '.');
					if ($lastPeriod !== false && $lastPeriod > (strlen($cleanContent) * 0.7)) {
						$cleanContent = substr($cleanContent, 0, $lastPeriod + 1);
					}
				}

				$datagpt = $this->translateAndCategorizeNewsArticle($post['title'], $cleanContent, $categorys);

				$slug = \Helpers::createSlug($post['title'], 'blog', 0, false);
				$category = $this->resolveNewsCategory($datagpt['category'] ?? '');

				// Skip if duplicate
				$existing = Blog::where('url', $post['url'])->orWhere('slug', $slug)->first();
				if ($existing) {
					$results[] = ['idx' => $clientIdx, 'title' => $post['title'] ?? '', 'status' => 'skipped', 'message' => 'Already exists'];
					continue;
				}

				$blog = Blog::create([
					'slug' => $slug,
					'title' => $datagpt['english_title'] ?? $post['title'],
					'short_description' => $datagpt['english_description'] ?? $post['description'] ?? '',
					'url' => $post['url'] ?? '',
					'source_name' => is_array($post['source']) ? ($post['source']['name'] ?? '') : ($post['source'] ?? ''),
					'description' => $datagpt['english_description'] ?? $post['content'] ?? '',
					'created_by' => $userId,
					'category_id' => $category->id ?? null,
					'content_type' => 'text',
					'schedule_date' => $now,
					'status' => 2,
					'blog_accent_code' => setting('blog_accent_code'),
					'source_published_at' => isset($post['publishedAt']) && $post['publishedAt'] != '' ?Carbon::parse($post['publishedAt'])->toDateTimeString() : null,
					'created_at' => $now,
				]);

				BlogActionLog::record('blog_created', (int)$blog->id, (int)$userId, [
					'source' => 'news_api_bulk',
					'status' => 2,
					'ai_rewrite' => true,
				]);
				BlogActionLog::record('blog_translated', (int)$blog->id, (int)$userId, [
					'source' => 'news_api_bulk',
					'languages' => ['en', 'hi'],
				]);

				if ($category) {
					BlogCategory::create(['category_id' => $category->id, 'blog_id' => $blog->id]);
				}

				BlogTranslation::updateOrInsert(
				['blog_id' => $blog->id, 'language_code' => 'en'],
				['title' => $datagpt['english_title'] ?? $post['title'], 'description' => $datagpt['english_description'] ?? $post['description'] ?? '', 'created_at' => $now]
				);

				BlogTranslation::updateOrInsert(
				['blog_id' => $blog->id, 'language_code' => 'hi'],
				['title' => $datagpt['hindi_title'] ?? $post['title'], 'description' => $datagpt['hindi_description'] ?? $post['content'] ?? '', 'created_at' => $now]
				);

				// Image handling (same as cron and single save)
				$fileUrl = $post['urlToImage'] ?? '';
				$basePath = public_path('/upload/blog/banner/');
				$imageContent = $this->fetchImageContentFromUrl($fileUrl);
				// $needsGenerated = empty($fileUrl) || !$imageContent;
				// if (!$needsGenerated && $imageContent) {
				// 	$info = @getimagesizefromstring($imageContent);
				// 	if ($info && isset($info[0], $info[1]) && $info[0] == 200 && $info[1] == 200) {
				// 		$needsGenerated = true;
				// 	}
				// }
				// if ($needsGenerated) {
				// 	$this->generateAndSaveImageFromOpenAI($post['title'] ?? '', $post['description'] ?? $post['content'] ?? '', $blog->id);
				// } else {
				if ($imageContent) {
					$ext = \Helpers::get_file_extension($fileUrl) ?: 'jpg';
					$name = time() . rand() . '.' . $ext;
					@mkdir($basePath . 'original/', 0755, true);
					@mkdir($basePath . '800/', 0755, true);
					@mkdir($basePath . '360/', 0755, true);
					@mkdir($basePath . '200/', 0755, true);
					$destination = $basePath . 'original/' . $name;
					file_put_contents($destination, $imageContent);
					$img = Image::make($destination);
					$img->resize(800, null, function ($constraint) {
						$constraint->aspectRatio();
					})->save($basePath . '800/' . $name);
					$img->resize(360, null, function ($constraint) {
						$constraint->aspectRatio();
					})->save($basePath . '360/' . $name);
					$img->fit(200, 200)->save($basePath . '200/' . $name);
					BlogImages::insertGetId(['blog_id' => $blog->id, 'image' => $name, 'created_at' => $now]);
				}
				// }
				$results[] = ['idx' => $clientIdx, 'title' => $post['title'] ?? '', 'status' => 'created', 'blog_id' => $blog->id];
			}
			catch (\Exception $e) {
				Log::warning('Bulk save failed for article: ' . ($post['title'] ?? '') . ' - ' . $e->getMessage());
				$results[] = ['idx' => $clientIdx, 'title' => $post['title'] ?? '', 'status' => 'failed', 'message' => $e->getMessage()];
				continue;
			}
		}
		return $results;
	}

	public function runCronNews()
	{
		// Fetch categories for the AI prompt
		$category_list = Category::where('status', 1)->where('name', '!=', 'Personalization')->pluck('name')->toArray();
		$categorys = implode(', ', $category_list);

		// Parameters for Event Registry (Fetch latest 50 articles)
		$params = [
			'keyword' => '', // Broad fetch
			'articlesCount' => 50,
			'articlesSortBy' => 'date',
			'dataType' => 'news',
			'lang' => 'eng'
		];

		// Fetch from Event Registry
		$articles = $this->fetchEventRegistryNews($params, true);

		if (!$articles || !is_array($articles)) {
			Log::error('Event Registry Cron fetch failed or returned empty results.');
			return 'Failed to fetch news from Event Registry.';
		}

		$createdBy = Auth::check() ?Auth::user()->id : 1; // fallback to admin
		$now = now();
		$count = 0;

		foreach ($articles as $article) {
			try {
				$slug = \Helpers::createSlug($article['title'], 'blog', 0, false);

				// Check for duplicates
				$existingBlog = Blog::where('url', $article['url'])->orWhere('slug', $slug)->first();
				if ($existingBlog) {
					continue;
				}

				$cleanContent = preg_replace('/\s?\[\+\d+\schars\]$/', '', $article['description'] ?? '');

				// Process with AI for categorization and Hindi translation
				$datagpt = $this->translateAndCategorizeNewsArticle($article['title'], $cleanContent ?? '', $categorys);
				$category = $this->resolveNewsCategory($datagpt['category'] ?? '');

				// Create Blog
				$blog = Blog::create([
					'slug' => $slug,
					'title' => $datagpt['english_title'] ?? $article['title'],
					'short_description' => $article['description'] ?? '',
					'url' => $article['url'],
					'source_name' => $article['source']['name'] ?? '',
					'description' => $datagpt['english_description'] ?? $article['content'],
					'created_by' => $createdBy,
					'category_id' => $category ? $category->id : null,
					'content_type' => 'text',
					'schedule_date' => $now,
					'status' => 2,
					'blog_accent_code' => setting('blog_accent_code'),
					'source_published_at' => isset($article['publishedAt']) ?Carbon::parse($article['publishedAt'])->toDateTimeString() : null,
					'created_at' => $now,
				]);

				if ($category) {
					BlogCategory::create(['category_id' => $category->id, 'blog_id' => $blog->id]);
				}

				// Translations
				BlogTranslation::updateOrInsert(
				['blog_id' => $blog->id, 'language_code' => 'en'],
				['title' => $datagpt['english_title'] ?? $article['title'], 'description' => $datagpt['english_description'] ?? $article['description'], 'created_at' => $now]
				);
				BlogTranslation::updateOrInsert(
				['blog_id' => $blog->id, 'language_code' => 'hi'],
				['title' => $datagpt['hindi_title'] ?? $article['title'], 'description' => $datagpt['hindi_description'] ?? $article['content'], 'created_at' => $now]
				);

				// Image handling
				$fileUrl = $article['urlToImage'] ?? '';
				if ($fileUrl) {
					$imageContent = $this->fetchImageContentFromUrl($fileUrl);
					if ($imageContent) {
						$ext = pathinfo($fileUrl, PATHINFO_EXTENSION) ?: 'jpg';
						$extArr = explode('?', $ext);
						$ext = $extArr[0] ?: 'jpg';
						$name = time() . rand() . '.' . $ext;
						$basePath = public_path('/upload/blog/banner/');
						@mkdir($basePath . 'original/', 0755, true);
						@mkdir($basePath . '800/', 0755, true);
						@mkdir($basePath . '360/', 0755, true);
						@mkdir($basePath . '200/', 0755, true);
						$destination = $basePath . 'original/' . $name;
						file_put_contents($destination, $imageContent);
						$img = Image::make($destination);
						$img->resize(800, null, function ($constraint) {
							$constraint->aspectRatio();
						})->save($basePath . '800/' . $name);
						$img->resize(360, null, function ($constraint) {
							$constraint->aspectRatio();
						})->save($basePath . '360/' . $name);
						$img->fit(200, 200)->save($basePath . '200/' . $name);
						BlogImages::insert(['blog_id' => $blog->id, 'image' => $name, 'created_at' => $now]);
					}
				}
				$count++;
			}
			catch (\Exception $e) {
				Log::warning('Cron news processing failed for article at index: ' . $article['title'] . ' Error: ' . $e->getMessage());
				continue;
			}
		}

		return "Cron finished. $count new articles added from Event Registry.";
	}

	/*
	 public function processNewsArticle($title, $description, $categorys)
	 {
	 $max_words = SiteContent::where('key', 'news_max_words')->first()->value ?? 60;
	 $min_words = $max_words - 10;
	 $allowedCategories = $this->extractCategoryNames($categorys);
	 $prompt = <<<EOT	 You are a precise and creative AI assistant that classifies and rewrites news content.
	 Follow these instructions carefully and return **only valid JSON**.
	 ---
	 ### Step 1: Category Selection  	 From the list below, choose **exactly one** category that best matches the article.  	 Categories: $categorys	 If no listed category is suitable, return category as exactly "Others".
	 ### Step 2: English Rewrite  	 - Rewrite the news title in a **positive, professional** tone using **exactly 15 words**.  	 - Rewrite the news description in the same tone using **between $min_words and $max_words words**, but absolutely **do not exceed $max_words words**.  	 - Keep the meaning accurate, clear, and engaging. Avoid generic phrases.  	 - Avoid repetitive emotional terms and avoid using the same word/root twice in the title.  	 - Do **not** use “joy”, “joyful”, “exciting”, “excited”, “exciting news”, or similar hype words.  	 - Do **not** start the title with phrases like “Exciting News!”, “Breaking News!”, or “Good News!”.
	 ### Step 3: Hindi Translation  	 Translate both rewritten English parts into **Hindi** while keeping the tone and clarity the same.  	 - Hindi title: 15 words  	 - Hindi description: between $min_words and $max_words words  
	 ---
	 Return **only** this JSON object (no explanations or extra text):	 {
	 "category": "chosen category name",
	 "english_title": "15-word rewritten English title",
	 "english_description": "rewritten English description (between $min_words and $max_words words)",
	 "hindi_title": "15-word Hindi title",
	 "hindi_description": "Hindi description (between $min_words and $max_words words)"	 }
	 ---
	 ### Example:	 News Title: Google unveils new AI tool for education.  	 News Description: Google has announced a new AI tool aimed at helping students learn faster and more efficiently. The tool will be released globally next month.
	 ---
	 ### Now process this:	 News Title: $title  	 News Description: $description	 EOT;
	 $response = Http::withToken(env('chatgpt_key'))->post('https://api.openai.com/v1/chat/completions', [
	 'model' => env('OPENAI_MODEL', 'gpt-5-mini'),
	 'messages' => [
	 ['role' => 'user', 'content' => $prompt]
	 ]
	 ]);
	 if ($response->failed()) {
	 $payload = $response->json() ?: $response->body();
	 \Helpers::markOpenAiQuotaExpiredIfNeeded($payload);
	 $fallbackCategory = 'Others';
	 return [
	 'category' => $fallbackCategory,
	 'english_title' => $title,
	 'english_description' => $description,
	 'hindi_title' => $title,
	 'hindi_description' => $description,
	 ];
	 }
	 \Helpers::clearOpenAiQuotaExpired();
	 // ... rest of the function in code
	 }
	 */

	/**
	 * Direct translation and categorization (No Rewrite)
	 * Uses original content for English and provides Hindi translation.
	 */
	public function translateAndCategorizeNewsArticle($title, $description, $categorys)
	{
		$prompt = <<<EOT
You are an expert news classifier and translator.

Follow these instructions perfectly and return a JSON object ONLY.

---

### Step 1: Category Selection  
Analyze the **News Title** and **News Description** provided below. Match it against the following list of exact available categories:
[ $categorys ]

You MUST choose **exactly one** category from that list that best describes the news topic. 
Your chosen category MUST match the text of a category from the list exactly. Do not invent categories. 
If and only if absolutely no listed category is suitable, return your category as exactly "Others".

### Step 2: Translation  
- Translate the original **English Title** into **Hindi**.  
- Translate the original **English Description** into **Hindi**.  

---

Output a valid JSON object matching this structure EXACTLY:
{
  "category": "Exact matched category name from the available categories list",
  "hindi_title": "Hindi translation of the title",
  "hindi_description": "Hindi translation of the description"
}

---

### Content to Process:
News Title: $title
News Description: $description
EOT;

		$response = Http::withToken(env('chatgpt_key'))
			->timeout(60)
			->post('https://api.openai.com/v1/chat/completions', [
			'model' => env('OPENAI_MODEL', 'gpt-5-mini'),
			'response_format' => ['type' => 'json_object'],
			'messages' => [
				['role' => 'system', 'content' => 'You are an AI that strictly returns valid JSON.'],
				['role' => 'user', 'content' => $prompt]
			]
		]);

		if ($response->failed()) {
			return [
				'category' => 'Others',
				'english_title' => $title,
				'english_description' => $description,
				'hindi_title' => $title,
				'hindi_description' => $description,
			];
		}

		$output = $response->json()['choices'][0]['message']['content'];
		$output = trim($output);

		// Strip out markdown if any models leak it despite json_object
		if (str_starts_with($output, '```json')) {
			$output = substr($output, 7);
			$output = preg_replace('/```$/', '', trim($output));
			$output = trim($output);
		}
		elseif (str_starts_with($output, '```')) {
			$output = substr($output, 3);
			$output = preg_replace('/```$/', '', trim($output));
			$output = trim($output);
		}

		$data = json_decode($output, true);

		if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
			return [
				'category' => $data['category'] ?? 'Others',
				'english_title' => $title,
				'english_description' => $description,
				'hindi_title' => $data['hindi_title'] ?? $title,
				'hindi_description' => $data['hindi_description'] ?? $description,
			];
		}

		return [
			'category' => 'Others',
			'english_title' => $title,
			'english_description' => $description,
			'hindi_title' => $title,
			'hindi_description' => $description,
		];
	}

	/**
	 * Fetch news from Event Registry (NewsAPI.ai)
	 * Documentation: https://newsapi.ai/documentation?tab=searchArticles&lang=rest
	 */
	public function fetchEventRegistryNews($inputParams = null, $internal = false)
	{
		try {
			$apiKey = setting('event_registry_api_key') ?: "a7e33fa2-2055-4ec9-a31c-b7f3f551d984";
			$url = 'https://eventregistry.org/api/v1/article/getArticles';

			$request = request();
			$params = is_array($inputParams) ? $inputParams : [];

			$payload = [
				'apiKey' => $apiKey,
				'action' => 'getArticles',
				'keyword' => $params['keyword'] ?? $request->input('keyword'),
				'ignoreKeyword' => $params['ignoreKeyword'] ?? $request->input('ignoreKeyword'),
				'conceptUri' => $params['conceptUri'] ?? $request->input('conceptUri'),
				'conceptOper' => $params['conceptOper'] ?? $request->input('conceptOper', 'and'),
				'ignoreConceptUri' => $params['ignoreConceptUri'] ?? $request->input('ignoreConceptUri'),
				'categoryUri' => $params['categoryUri'] ?? $request->input('categoryUri'),
				'categoryOper' => $params['categoryOper'] ?? $request->input('categoryOper', 'and'),
				'ignoreCategoryUri' => $params['ignoreCategoryUri'] ?? $request->input('ignoreCategoryUri'),
				'sourceUri' => $params['sourceUri'] ?? $request->input('sourceUri'),
				'ignoreSourceUri' => $params['ignoreSourceUri'] ?? $request->input('ignoreSourceUri'),
				'sourceLocationUri' => $params['sourceLocationUri'] ?? $request->input('sourceLocationUri'),
				'sourceGroupUri' => $params['sourceGroupUri'] ?? $request->input('sourceGroupUri'),
				'authorUri' => $params['authorUri'] ?? $request->input('authorUri'),
				'locationUri' => $params['locationUri'] ?? $request->input('locationUri'),
				'lang' => $params['lang'] ?? $request->input('lang'),
				'dateStart' => $params['dateStart'] ?? $request->input('dateStart'),
				'timeStart' => $params['timeStart'] ?? $request->input('timeStart'),
				'dateEnd' => $params['dateEnd'] ?? $request->input('dateEnd'),
				'timeEnd' => $params['timeEnd'] ?? $request->input('timeEnd'),
				'sentimentMin' => $params['sentimentMin'] ?? $request->input('sentimentMin'),
				'sentimentMax' => $params['sentimentMax'] ?? $request->input('sentimentMax'),
				'isDuplicate' => $params['isDuplicate'] ?? $request->input('isDuplicate', 'skipDuplicates'),
				'resultType' => $params['resultType'] ?? $request->input('resultType', 'articles'),
				'articlesCount' => min($params['articlesCount'] ?? $request->input('articlesCount', 100), 200),
				'articlesSortBy' => $params['articlesSortBy'] ?? $request->input('articlesSortBy', 'date'),
				'articlesSortByAsc' => $params['articlesSortByAsc'] ?? $request->input('articlesSortByAsc', false),
				'articlesPage' => $params['articlesPage'] ?? $request->input('articlesPage', 1),
				'dataType' => $params['dataType'] ?? $request->input('dataType', 'news')
			];

			// Only include non-null/non-empty parameters in the payload
			$payload = array_filter($payload, function ($value) {
				return !is_null($value) && $value !== '' && (!is_array($value) || !empty($value));
			});

			$response = Http::withHeaders([
				'Content-Type' => 'application/json'
			])->post($url, $payload);

			if ($response->failed()) {
				Log::error('Event Registry news fetch failed: ' . $response->body());
				if ($internal)
					return null;
				return response()->json([
					'status' => 'error',
					'message' => 'Failed to fetch news from Event Registry API.',
					'error' => $response->json()
				], $response->status());
			}

			if ($internal)
				return $response->json();
			return response()->json($response->json());
		}
		catch (\Exception $e) {
			Log::error('Event Registry news fetch exception: ' . $e->getMessage());
			if ($internal)
				return null;
			return response()->json([
				'status' => 'error',
				'message' => 'Internal server error: ' . $e->getMessage()
			], 500);
		}
	}

	/**
	 * Fetch filters (concepts, categories, sources, etc.) from Event Registry Autosuggest
	 */
	public function fetchEventRegistryFilters($inputParams = null, $internal = false)
	{
		try {
			$apiKey = setting('event_registry_api_key') ?: "a7e33fa2-2055-4ec9-a31c-b7f3f551d984";

			$request = request();
			$params = is_array($inputParams) ? $inputParams : [];

			$type = $params['type'] ?? $request->input('type', 'concepts'); // concepts, categories, sources, authors, locations
			$prefix = $params['prefix'] ?? $request->input('prefix', '');

			$endpoints = [
				'concepts' => 'https://eventregistry.org/api/v1/suggestConceptsFast',
				'categories' => 'https://eventregistry.org/api/v1/suggestCategoriesFast',
				'sources' => 'https://eventregistry.org/api/v1/suggestSourcesFast',
				'authors' => 'https://eventregistry.org/api/v1/suggestAuthorsFast',
				// Use suggestConceptsFast with source=loc for locations (matches official NewsAPI.ai website, returns more results)
				'locations' => 'https://eventregistry.org/api/v1/suggestConceptsFast',
				'sourceGroups' => 'https://eventregistry.org/api/v1/suggestSourceGroupsFast'
			];

			if (!isset($endpoints[$type])) {
				if ($internal)
					return null;
				return response()->json([
					'status' => 'error',
					'message' => 'Invalid filter type provided.'
				], 400);
			}

			$query = array_merge([
				'prefix' => $prefix,
				'apiKey' => $apiKey,
				'lang' => $params['lang'] ?? $request->input('lang', 'eng'),
				'count' => $params['count'] ?? $request->input('count', 50)
			], $params); // Merge any extra params passed programmatically

			if ($type === 'locations') {
				// Match the exact API call from the official NewsAPI.ai website:
				// suggestConceptsFast?prefix=X&lang=eng&lang=ticker&lang=isin&fullLocInfo=true&source=loc
				$query['source'] = 'loc';
				$query['lang'] = ['eng', 'ticker', 'isin'];
				$query['fullLocInfo'] = true;
			}

			// We use POST with JSON body to match the user's suggested curl format and support complex payloads safely.
			$response = Http::withHeaders([
				'Content-Type' => 'application/json'
			])->post($endpoints[$type], array_merge([
				'prefix' => $prefix,
				'apiKey' => $apiKey,
			], $query));

			if ($response->failed()) {
				Log::error("Event Registry Suggest ($type) failed: " . $response->body());
				if ($internal)
					return null;
				return response()->json([
					'status' => 'error',
					'message' => 'Failed to fetch filters from Event Registry.'
				], $response->status());
			}

			if ($internal)
				return $response->json();
			return response()->json($response->json());
		}
		catch (\Exception $e) {
			Log::error('Event Registry filters exception: ' . $e->getMessage());
			if ($internal)
				return null;
			return response()->json([
				'status' => 'error',
				'message' => 'Internal server error: ' . $e->getMessage()
			], 500);
		}
	}

	/**
	 * Ensures that the given text ends with a complete sentence.
	 * Prunes trailing truncated sentences or adds a period if necessary.
	 */
	private function ensureCompleteSentences(string $text): string
	{
		$text = trim($text);
		if ($text === '')
			return $text;

		// Strip Event Registry / NewsAPI truncation markers like "[+1234 chars]"
		$text = preg_replace('/\s?\[\+\d+\schars\]$/i', '', $text);

		// Remove common ellipses if they look like they were inserted by an API
		$text = preg_replace('/\.\.\.$/', '', $text);

		$sentenceEnders = ['.', '!', '?', '।', '"', '”', '»', ')'];
		$lastChar = function_exists('mb_substr') ? mb_substr($text, -1, 1, 'UTF-8') : substr($text, -1);

		// If it already ends with punctuation, we're good
		if (in_array($lastChar, $sentenceEnders, true))
			return $text;

		// Find the last occurrence of any major sentence ender
		$bestPos = -1;
		foreach ($sentenceEnders as $p) {
			$pos = function_exists('mb_strrpos') ? mb_strrpos($text, $p, 0, 'UTF-8') : strrpos($text, $p);
			if ($pos !== false && $pos > $bestPos)
				$bestPos = (int)$pos;
		}

		if ($bestPos !== -1) {
			// Trim back to the last complete sentence
			$text = function_exists('mb_substr') ? mb_substr($text, 0, $bestPos + 1, 'UTF-8') : substr($text, 0, $bestPos + 1);
		}
		elseif (strlen($text) > 5) {
			// Fallback: If no sentence ender is found, add a simple period or Hindi full stop
			$isHindiText = (bool)preg_match('/[\x{0900}-\x{097F}]/u', $text);
			$text .= ($isHindiText ? ' ।' : '.');
		}

		return trim($text);
	}
}
