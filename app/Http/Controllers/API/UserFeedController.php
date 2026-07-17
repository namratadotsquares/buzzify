<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\UserFeed;
use App\Models\Category;
use App\Models\Blog;
use App\Models\Ads;
use App\Models\Ads_images;
use App\Models\BlogTranslation;
use App\Models\CategoryTranslation;
use App\Models\Vote;
use App\Models\User;
use App\Models\BookMarkPost;
use App\Models\BlogViewCount;
use App\Models\BlogImages;
use App\Models\Author;
use App\Models\SiteContent;
use App\Models\SearchLog;
use App\Models\BlogCategory;


use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class UserFeedController extends Controller
{
    private $language;
    public function __construct(Request $request)
    {
        $this->request = $request;
        $newUserId = $this->request->header('userData');

        $user  =  User::find($newUserId);
        if ($user && $user->lang_code != '') {
            $this->language = ($request->header('lang-code') && $request->header('lang-code') != '' ? $request->header('lang-code') : $user->lang_code);
        } else {
            $this->language = ($request->header('lang-code') && $request->header('lang-code') != '' ? $request->header('lang-code') : setting('preferred_site_language'));
        }
    }

    public function index(Request $request, $userID)
    {
        $pagination_no = 10;
        if (isset($search['per_page']) && !empty($search['per_page'])) {
            $pagination_no = $search['per_page'];
        }
        $blogCategory = UserFeed::where('user_id', $userID)->pluck('category_id')->toArray();
        $blog_image = array();
        $final_blog = array();
        $blogIdArr = array();
        $readids = json_decode($request->input('blog_ids'));
        $user_id = $request->input('$user_id');
        $device_id = $request->input('device_id');
        $blogsData = BlogCategory::whereIn('category_id', $blogCategory)->get();
        if (count($blogsData)) {
            foreach ($blogsData as $blogsData_data) {
                array_push($blogIdArr, $blogsData_data->blog_id);
            }
        }
        $ads_list = Ads::where('status', 1)->where('end_date', ">=", date('Y-m-d'))->withCount('click')->withCount('view')->with('media')->orderBy('order', 'ASC')->get();
        if (count($ads_list)) {
            foreach ($ads_list as $ads_list_data) {
                foreach ($ads_list_data->media  as $media_list) {
                    if ($media_list->type == 'image') {
                        $media_list->file = url('storage/ads/' . $media_list->file);
                    } else if ($media_list->type == 'video') {
                        $media_list->file = url('storage/ads/videos/' . $media_list->file);
                    }
                }
            }
        }
        $blog = Blog::where('status', 1)->whereIn('id', $blogIdArr)->where('schedule_date', "<=", date("Y-m-d H:i:s"))->with('blog_category')
            ->when(true, function ($query) use ($device_id, $user_id) {
                $query->whereDoesntHave('viewStories', function ($query) use ($device_id, $user_id) {
                    if ($user_id && $device_id) {
                        $query->where(function ($q) use ($user_id, $device_id) {
                            $q->where('user_id', $user_id)
                              ->orWhere('device_id', $device_id);
                        });
                    } else if ($user_id) {
                        $query->where('user_id', $user_id);
                    } else if ($device_id) {
                        $query->where('device_id', $device_id);
                    }
                });
            })->orderBy('schedule_date', 'DESC');

        if (!empty($readids)) {
            $blog->whereNotIn('id', $readids);
        }


        $blog = $blog->get();


        //paginate($pagination_no)->appends('per_page', $pagination_no);
        foreach ($blog as $row) {
            $flag = false;
            $blogTranslate = BlogTranslation::where('blog_id', $row->id)->where('language_code', $this->language)->first();
            if ($blogTranslate) {
                $flag = true;
                $row->type = 'blog';
                $row->title = $blogTranslate->title;
                $row->tags = $blogTranslate->tags;
                $row->description = $blogTranslate->description;
                $row->seo_title = $blogTranslate->seo_title;
                $row->seo_keyword = $blogTranslate->seo_keyword;
                $row->seo_tag = $blogTranslate->seo_tag;
                $row->seo_description = $blogTranslate->seo_description;
                $row->language_code = $blogTranslate->language_code;
            }
            $row->trimed_description = strip_tags($row->description);
            $row->trimed_description = str_replace("&nbsp;", '', $row->trimed_description);
            $row->trimed_description = str_replace("&#39;", "'", $row->trimed_description);

            if ($row->thumb_image != '') {
                $row->thumb_image = url('upload/blog/thumb/360/' . $row->thumb_image);
            } else {
                $row->thumb_image = url('upload/blog/thumb/default.png');
            }
            $check_image = BlogImages::where('blog_id', $row->id)->pluck('image');
            $blog_image = array();
            if (count($check_image)) {
                foreach ($check_image as $value) {
                    $value = url('upload/blog/banner/800/' . $value);
                    array_push($blog_image, $value);
                }
                $row->banner_image = $blog_image;
            } else {
                $blog_image[0] = url('upload/author/default.png');
                $row->banner_image = $blog_image;
            }
            if ($userID != null) {
                $vote = Vote::where('user_id', $userID)->where('blog_id', $row->id)->first();
                if ($vote) {
                    $row->is_vote = 1;
                } else {
                    $row->is_vote = 0;
                }
                $bookmarked = BookMarkPost::where('user_id', $userID)->where('blog_id', $row->id)->first();
                if ($bookmarked) {
                    $row->is_bookmark = 1;
                } else {
                    $row->is_bookmark = 0;
                }
            } else {
                $row->is_vote = 0;
                $row->is_bookmark = 0;
            }
            $row->view_count = BlogViewCount::where('blog_id', $row->id)->count();
            $row->story_view_count = \App\Models\StoryViewCount::where('story_id', $row->id)->count();
            $total_votes = Vote::where('blog_id', $row->id)->count();
            $yes_votes = Vote::where('blog_id', $row->id)->where('vote', 1)->count();
            $no_votes = Vote::where('blog_id', $row->id)->where('vote', 0)->count();
            if ($yes_votes != 0) {
                $yes_percent = ($yes_votes / $total_votes) * 100;
            } else {
                $yes_percent = 0;
            }
            if ($no_votes != 0) {
                $no_percent = ($no_votes / $total_votes) * 100;
            } else {
                $no_percent = 0;
            }
            $row->yes_percent = round($yes_percent);
            $row->no_percent = round($no_percent);
            $author = Author::where('id', $row->author_id)->first();
            if ($author) {
                $row->author_name = $author->name;
                if ($author->image != null || $author->image != '') {
                    $row->image = url('upload/author/original/' . $author->image);
                } else {
                    $row->image = url('upload/author/default.png');
                }
            } else {
                $row->author_name = "";
                $row->image = url('upload/author/default.png');
            }
            $category = Category::where('id', $row->category_id)->first();
            if ($category) {
                $catTranslate = CategoryTranslation::where('category_id', $category->id)->where('language_code', $this->language)->first();
                if ($catTranslate) {
                    $category->name = $catTranslate->name;
                }

                $row->category_name = $category->name;
                $row->color = $category->color;
            } else {
                $row->category_name = "";
                $row->color = "";
            }
            $row->time = $row->time . " min";
            $row->created_at = date("d-M-Y", strtotime($row->created_at));
            $row->create_date = date("d M Y // h:i a", strtotime($row->schedule_date));
            $blog_image = array();
        }
        // return $this->sendResponse($blog, __('message_alerts.blog_list'));
        $result = [];
        $adCount = count($ads_list);
        $adIndex = 0;

        $BlogCount = 0;
        if (count($ads_list)) {
            foreach ($blog as $key => $item) {
                if ($BlogCount == $ads_list[$adIndex]['frequency']) {
                    $ads_list[$adIndex]['type'] = "ads";
                    $result[] = $ads_list[$adIndex];
                    $adIndex = ($adIndex + 1) % $adCount;
                    $BlogCount = 0;
                }
                $result[] = $item;
                $BlogCount++;
            }
        } else {
            foreach ($blog as $key => $item) {
                $result[] = $item;
            }
        }


        $dataCheck = $this->arrayPaginator($result, $request);
        return $this->sendResponse($dataCheck, __('message_alerts.blog_list'));
    }

    public function index2(Request $request, $userID)
    {
        $pagination_no = 10;
        if (isset($search['per_page']) && !empty($search['per_page'])) {
            $pagination_no = $search['per_page'];
        }

        // Get the user's selected categories
        $blogCategory = UserFeed::where('user_id', $userID)->pluck('category_id')->toArray();

        // Exclude the user's selected categories from the blog data query
        $blogIdArr = BlogCategory::whereNotIn('category_id', $blogCategory)->pluck('blog_id')->toArray();

        $ads_list = Ads::where('status', 1)
            ->where('end_date', ">=", date('Y-m-d'))
            ->withCount('click')
            ->withCount('view')
            ->with('media')
            ->orderBy('order', 'ASC')
            ->get();

        foreach ($ads_list as $ads_list_data) {
            foreach ($ads_list_data->media as $media_list) {
                if ($media_list->type == 'image') {
                    $media_list->file = url('storage/ads/' . $media_list->file);
                } else if ($media_list->type == 'video') {
                    $media_list->file = url('storage/ads/videos/' . $media_list->file);
                }
            }
        }

        $blogs = Blog::where('status', 1)
            ->whereIn('id', $blogIdArr)
            ->where('schedule_date', "<=", date("Y-m-d H:i:s"))
            // ->with('blog_category')
            ->whereDoesntHave('blog_category', function ($query) {
                $query->whereHas('category', function ($query) {
                    $query->where('name', 'Personalization'); // Exclude blogs with category name "Personalization"
                });
            })
            ->with([
                'blog_category' => function ($query) {
                    $query->whereHas('category', function ($query) {
                        $query->where('status', 1);
                    });
                }
            ])
            ->orderBy('schedule_date', 'DESC')
            ->get();

        foreach ($blogs as $row) {
            $flag = false;
            $blogTranslate = BlogTranslation::where('blog_id', $row->id)->where('language_code', $this->language)->first();
            if ($blogTranslate) {
                $flag = true;
                $row->type = 'blog';
                $row->title = $blogTranslate->title;
                $row->tags = $blogTranslate->tags;
                $row->description = $blogTranslate->description;
                $row->seo_title = $blogTranslate->seo_title;
                $row->seo_keyword = $blogTranslate->seo_keyword;
                $row->seo_tag = $blogTranslate->seo_tag;
                $row->seo_description = $blogTranslate->seo_description;
                $row->language_code = $blogTranslate->language_code;
            }
            $row->trimed_description = strip_tags($row->description);
            $row->trimed_description = str_replace("&nbsp;", '', $row->trimed_description);
            $row->trimed_description = str_replace("&#39;", "'", $row->trimed_description);

            $row->thumb_image = $row->thumb_image ? url('upload/blog/thumb/360/' . $row->thumb_image) : url('upload/blog/thumb/default.png');

            $check_image = BlogImages::where('blog_id', $row->id)->pluck('image');
            $blog_image = [];
            foreach ($check_image as $value) {
                $value = url('upload/blog/banner/800/' . $value);
                $blog_image[] = $value;
            }
            $row->banner_image = count($blog_image) ? $blog_image : [url('upload/author/default.png')];

            if ($userID != null) {
                $vote = Vote::where('user_id', $userID)->where('blog_id', $row->id)->first();
                $row->is_vote = $vote ? 1 : 0;

                $bookmarked = BookMarkPost::where('user_id', $userID)->where('blog_id', $row->id)->first();
                $row->is_bookmark = $bookmarked ? 1 : 0;
            } else {
                $row->is_vote = 0;
                $row->is_bookmark = 0;
            }

            $row->view_count = BlogViewCount::where('blog_id', $row->id)->count();
            $row->story_view_count = \App\Models\StoryViewCount::where('story_id', $row->id)->count();

            $total_votes = Vote::where('blog_id', $row->id)->count();
            $yes_votes = Vote::where('blog_id', $row->id)->where('vote', 1)->count();
            $no_votes = Vote::where('blog_id', $row->id)->where('vote', 0)->count();
            $row->yes_percent = $yes_votes ? round(($yes_votes / $total_votes) * 100) : 0;
            $row->no_percent = $no_votes ? round(($no_votes / $total_votes) * 100) : 0;

            $author = Author::where('id', $row->author_id)->first();
            $row->author_name = $author ? $author->name : "";
            $row->image = $author && $author->image ? url('upload/author/original/' . $author->image) : url('upload/author/default.png');

            $category = Category::where('id', $row->category_id)->first();
            if ($category) {
                $catTranslate = CategoryTranslation::where('category_id', $category->id)->where('language_code', $this->language)->first();
                $category->name = $catTranslate ? $catTranslate->name : $category->name;

                $row->category_name = $category->name;
                $row->color = $category->color;
            } else {
                $row->category_name = "";
                $row->color = "";
            }
            $row->time = $row->time . " min";
            $row->created_at = date("d-M-Y", strtotime($row->created_at));
            $row->create_date = date("d M Y // h:i a", strtotime($row->schedule_date));
        }

        $result = [];
        $adCount = count($ads_list);
        $adIndex = 0;

        $BlogCount = 0;
        if ($adCount) {
            foreach ($blogs as $item) {
                if ($BlogCount == $ads_list[$adIndex]['frequency']) {
                    $ads_list[$adIndex]['type'] = "ads";
                    $result[] = $ads_list[$adIndex];
                    $adIndex = ($adIndex + 1) % $adCount;
                    $BlogCount = 0;
                }
                $result[] = $item;
                $BlogCount++;
            }
        } else {
            foreach ($blogs as $item) {
                $result[] = $item;
            }
        }

        $dataCheck = $this->arrayPaginator($result, $request);
        return $this->sendResponse($dataCheck, __('message_alerts.blog_list'));
    }
    public function get_local_news(Request $request)
    {
        $search = $request->all();
        $location_radius = SiteContent::where('key', 'location_radius')->first();
        $pagination_no = 10;
        if (isset($search['per_page']) && !empty($search['per_page'])) {
            $pagination_no = $search['per_page'];
        }
        //$blogCategory = UserFeed::where('user_id',$userID)->pluck('category_id')->toArray();
        //$blogCategory = BlogCategory::pluck('category_id')->toArray();
        $blog_image = array();
        $final_blog = array();
        $header = $request->header('userData');
        if ($header != null) {
            $user = User::where('id', $header)->first();
            if (!isset($user->id)) {
                return $this->sendError('Your account has been deleted by the Admin', 401);
            }
        }
        $viewedBlogIds = [];

        if (isset($search['device_id']) && !empty($search['device_id']) && $header != null) {
            $viewedBlogIds = BlogViewCount::where(function ($query) use ($search, $header) {
                $query->where('device_id', $search['device_id'])
                      ->orWhere('user_id', $header);
            })->pluck('blog_id')->toArray();
        } else if (isset($search['device_id']) && !empty($search['device_id'])) {
            $viewedBlogIds = BlogViewCount::where('device_id', $search['device_id'])
                ->pluck('blog_id')->toArray();
        } else if ($header != null) {
            $viewedBlogIds = BlogViewCount::where('user_id', $header)
                ->pluck('blog_id')->toArray();
        }
        if (isset($search['lat']) && !empty($search['lat']) && isset($search['long']) && !empty($search['long'])) {
            $lat = $search['lat'];   // e.g., 28.6139
            $lng = $search['long'];  // e.g., 77.2090
            $radius =  $location_radius->value ?? 50; // in kilometers
            $blog = Blog::select('blog.*')
                ->where('status', 1)
                ->where('schedule_date', '<=', now())
                ->whereNotIn('id', $viewedBlogIds)
                ->with('blog_category')
                ->selectRaw("
                    6371 * acos(
                        cos(radians(?)) * cos(radians(latitude)) *
                        cos(radians(longitude) - radians(?)) +
                        sin(radians(?)) * sin(radians(latitude))
                    ) AS distance", [$lat, $lng, $lat])
                ->havingRaw("distance <= ?", [$radius])
                ->orderBy("distance", "ASC")
                ->orderBy("schedule_date", "DESC")
                ->paginate($pagination_no)
                ->appends(['per_page' => $pagination_no]);
        }

        foreach ($blog as $row) {
            $flag = false;
            $blogTranslate = BlogTranslation::where('blog_id', $row->id)->where('language_code', $this->language)->first();
            if ($blogTranslate) {
                $flag = true;
                $row->type = 'blog';

                $row->title = $blogTranslate->title;
                $row->tags = $blogTranslate->tags;
                $row->description = $blogTranslate->description;
                $row->seo_title = $blogTranslate->seo_title;
                $row->seo_keyword = $blogTranslate->seo_keyword;
                $row->seo_tag = $blogTranslate->seo_tag;
                $row->seo_description = $blogTranslate->seo_description;
                $row->language_code = $blogTranslate->language_code;
            }
            $row->already_viewed = in_array($row->id, $viewedBlogIds);
            $row->trimed_description = strip_tags($row->description);
            $row->trimed_description = str_replace("&nbsp;", '', $row->trimed_description);
            $row->trimed_description = str_replace("&#39;", "'", $row->trimed_description);

            if ($row->thumb_image != '') {
                $row->thumb_image = url('upload/blog/thumb/360/' . $row->thumb_image);
            } else {
                $row->thumb_image = url('upload/blog/thumb/default.png');
            }
            $check_image = BlogImages::where('blog_id', $row->id)->pluck('image');
            $blog_image = array();
            if (count($check_image)) {
                foreach ($check_image as $value) {
                    $value = url('upload/blog/banner/800/' . $value);
                    array_push($blog_image, $value);
                }
                $row->banner_image = $blog_image;
            } else {
                $blog_image[0] = url('upload/author/default.png');
                $row->banner_image = $blog_image;
            }
            if ($header != null) {
                $vote = Vote::where('user_id', $header)->where('blog_id', $row->id)->first();
                if ($vote) {
                    $row->is_vote = 1;
                } else {
                    $row->is_vote = 0;
                }
                $bookmarked = BookMarkPost::where('user_id', $header)->where('blog_id', $row->id)->first();
                if ($bookmarked) {
                    $row->is_bookmark = 1;
                } else {
                    $row->is_bookmark = 0;
                }
            } else {
                $row->is_vote = 0;
                $row->is_bookmark = 0;
            }
            $row->view_count = BlogViewCount::where('blog_id', $row->id)->count();
            $row->story_view_count = \App\Models\StoryViewCount::where('story_id', $row->id)->count();
            $total_votes = Vote::where('blog_id', $row->id)->count();
            $yes_votes = Vote::where('blog_id', $row->id)->where('vote', 1)->count();
            $no_votes = Vote::where('blog_id', $row->id)->where('vote', 0)->count();
            if ($yes_votes != 0) {
                $yes_percent = ($yes_votes / $total_votes) * 100;
            } else {
                $yes_percent = 0;
            }
            if ($no_votes != 0) {
                $no_percent = ($no_votes / $total_votes) * 100;
            } else {
                $no_percent = 0;
            }
            $row->yes_percent = round($yes_percent);
            $row->no_percent = round($no_percent);
            $author = User::where('id', $row->created_by)->first();
            if ($author) {
                $row->author_name = $author->name;
                if ($author->photo != null || $author->photo != '') {
                    $row->image = $author->photo;
                } else {
                    $row->image = url('upload/user/default.png');
                }
            } else {
                $row->author_name = "";
                $row->image = url('upload/user/default.png');
            }
            $cat_id = BlogCategory::where('blog_id', $row->id)->value('category_id');

            $category = Category::where('id', $cat_id)->first();
            if ($category) {
                $catTranslate = CategoryTranslation::where('category_id', $category->id)->where('language_code', $this->language)->first();
                if ($catTranslate) {
                    $category->name = $catTranslate->name;
                }

                $row->category_name = $category->name;
                $row->color = $category->color;
            } else {
                $row->category_name = "";
                $row->color = "";
            }
            $row->time = $row->time . " min";
            $row->created_at = date("d-M-Y", strtotime($row->created_at));
            $row->create_date = date("d M Y // h:i a", strtotime($row->schedule_date));
            $blog_image = array();
        }
        return $this->sendResponse($blog, __('message_alerts.blog_list'));
    }
    public function getAllFeed(Request $request)
    {

         $search = $request->all();
       
        $location_radius = SiteContent::where('key', 'location_radius')->first();
        $feature_news_id = isset($search['feature_news_id']) ? (int) $search['feature_news_id'] : 0;
       if(isset($search['feature_news_id']) && !empty($search['feature_news_id'])){
        $all_feature_news = Blog::where('status', 1)->where('schedule_date', '<=', now())
                    ->where(function ($query) {
                        $query->where('is_featured', 1)
                            ->orWhere('is_slider', 1);
                    })
                    ->orderBy('schedule_date', 'Desc')->get();
       } else{
         $all_feature_news = Blog::where('status', 50)->where('schedule_date', '<=', now())
                  
                    ->orderBy('schedule_date', 'Desc')->get();
       }
        
        $feature_news = Blog::where('status', 1)->where('id', $feature_news_id)->first();
        if(!$feature_news){
            $feature_news_id = 0;
        }
        $pagination_no = 10;
        if (isset($search['per_page']) && !empty($search['per_page'])) {
            $pagination_no = $search['per_page'];
        }
        //$blogCategory = UserFeed::where('user_id',$userID)->pluck('category_id')->toArray();
        //$blogCategory = BlogCategory::pluck('category_id')->toArray();
        $blog_image = array();
        $final_blog = array();
        $header = $request->header('userData');
        if ($header != null) {
            $user = User::where('id', $header)->first();
            if (!isset($user->id)) {
                return $this->sendError('Your account has been deleted by the Admin', 401);
            }
        }
        $viewedBlogIds = [];

        if (isset($search['device_id']) && !empty($search['device_id']) && $header != null) {
            $viewedBlogIds = BlogViewCount::where(function ($query) use ($search, $header) {
                $query->where('device_id', $search['device_id'])
                      ->orWhere('user_id', $header);
            })->pluck('blog_id')->toArray();
        } else if (isset($search['device_id']) && !empty($search['device_id'])) {
            $viewedBlogIds = BlogViewCount::where('device_id', $search['device_id'])
                ->pluck('blog_id')->toArray();
        } else if ($header != null) {
            $viewedBlogIds = BlogViewCount::where('user_id', $header)
                ->pluck('blog_id')->toArray();
        }

        if (!empty($feature_news_id)) {
            $viewedBlogIds = array_filter($viewedBlogIds, function ($id) use ($feature_news_id) {
                return $id != $feature_news_id;
            });

            // Reindex array keys
            $viewedBlogIds = array_values($viewedBlogIds);
        }
         
        $all_feature_news = $all_feature_news->pluck('id')->toArray();
        $all_feature_news = array_diff($all_feature_news, $viewedBlogIds);
        
        $all_feature_news = array_diff($all_feature_news, [$feature_news_id]);
        array_unshift($all_feature_news, $feature_news_id);
        $all_feature_news = array_values($all_feature_news);
        
        
        if (isset($search['lat']) && !empty($search['lat']) && isset($search['long']) && !empty($search['long'])) {
            if ($search['lat'] !== null && $search['long'] !== null) {
                $lat = $search['lat'];   // e.g., 28.6139
                $lng = $search['long'];  // e.g., 77.2090
                $radius = $location_radius->value ?? 50; // in kilometers
                $blog = Blog::select('blog.*')
                    ->where('status', 1)
                    ->where('schedule_date', '<=', now())
                    ->whereNotIn('id', $viewedBlogIds)
                    ->orWhereIn('id', [$feature_news_id])
                    ->with('blog_category')
                    ->selectRaw("
                        6371 * acos(
                            cos(radians(?)) * cos(radians(latitude)) *
                            cos(radians(longitude) - radians(?)) +
                            sin(radians(?)) * sin(radians(latitude))
                        ) AS distance", [$lat, $lng, $lat])
                    ->havingRaw("distance <= ?", [$radius])
                    ->orderBy("distance", "ASC")
                    ->orderBy("schedule_date", "DESC")
                    ->paginate($pagination_no)
                    ->appends(['per_page' => $pagination_no]);
            } else {
                $blog = array();
            }
        } else {

            if ($header != null) {
                if (isset($search['category_ids']) && !empty($search['category_ids'])) {
                    $interestedCategoryIds = explode(',', $search['category_ids']);
                } else {
                    $interestedCategoryIds = UserFeed::where('user_id', $header)->pluck('category_id')->toArray();
                }
                $intrested_blog = Blog::where('blog.status', 1)
                    ->where('blog.schedule_date', '<=', now())
                    ->whereHas('blog_category', function ($query) use ($interestedCategoryIds) {
                        $query->whereIn('category_id', $interestedCategoryIds);
                    })
                    ->select('blog.*')
                    ->orderBy('schedule_date', 'desc');

                $interestedBlogIds = $intrested_blog->pluck('id')->toArray();

                $blogQuery = Blog::where('status', 1)
                    ->where('schedule_date', '<=', now())
                    ->where('is_location_radius', '0')
                    ->where(function ($query) use ($viewedBlogIds, $all_feature_news) {
                        $query->whereNotIn('id', $viewedBlogIds)
                            ->orWhereIn('id', $all_feature_news);
                    })
                    ->select('blog.*');

                if (!empty($all_feature_news)) {
                    $blogQuery->orderByRaw("FIELD(id, " . implode(',', array_reverse($all_feature_news)) . ") DESC");
                }

                if (!empty($interestedBlogIds)) {
                    $blogQuery->orderByRaw("id IN (" . implode(',', $interestedBlogIds) . ") DESC");
                }

                $blog = $blogQuery->orderBy('schedule_date', 'DESC')
                    ->paginate($pagination_no)
                    ->appends('per_page', $pagination_no);
            } else if (isset($search['category_ids']) && !empty($search['category_ids'])) {
                $interestedCategoryIds = explode(',', $search['category_ids']);
                $intrested_blog = Blog::where('blog.status', 1)
                    ->where('blog.schedule_date', '<=', now())
                    ->whereHas('blog_category', function ($query) use ($interestedCategoryIds) {
                        $query->whereIn('category_id', $interestedCategoryIds);
                    })
                    ->select('blog.*')
                    ->orderBy('schedule_date', 'desc');
                $interestedBlogIds = $intrested_blog->pluck('id')->toArray();
                
                $blogQuery = Blog::where('status', 1)
                    ->where('schedule_date', '<=', now())
                    ->where('is_location_radius', '0')
                    ->where(function ($query) use ($viewedBlogIds, $all_feature_news) {
                        $query->whereNotIn('id', $viewedBlogIds)
                            ->orWhereIn('id', $all_feature_news);
                    })
                    ->select('blog.*');

                if (!empty($all_feature_news)) {
                    $blogQuery->orderByRaw("FIELD(id, " . implode(',', array_reverse($all_feature_news)) . ") DESC");
                }

                if (!empty($interestedBlogIds)) {
                    $blogQuery->orderByRaw("id IN (" . implode(',', $interestedBlogIds) . ") DESC");
                }

                $blog = $blogQuery->orderBy('schedule_date', 'DESC')
                    ->paginate($pagination_no)
                    ->appends('per_page', $pagination_no);
            } else {
                $blogQuery = Blog::where('status', 1)
                    ->where('schedule_date', "<=", date("Y-m-d H:i:s"))
                    ->where('is_location_radius', '0')
                    ->where(function ($query) use ($viewedBlogIds, $all_feature_news) {
                        $query->whereNotIn('id', $viewedBlogIds)
                            ->orWhereIn('id', $all_feature_news);
                    })
                    ->with('blog_category');

                if (!empty($all_feature_news)) {
                    $blogQuery->orderByRaw("FIELD(id, " . implode(',', array_reverse($all_feature_news)) . ") DESC");
                }

                $blog = $blogQuery->orderBy('schedule_date', 'DESC')
                    ->paginate($pagination_no)->appends('per_page', $pagination_no);
            }
        }

        foreach ($blog as $row) {
            $flag = false;
            $blogTranslate = BlogTranslation::where('blog_id', $row->id)->where('language_code', $this->language)->first();
            if ($blogTranslate) {
                $flag = true;
                $row->type = 'blog';

                $row->title = $blogTranslate->title;
                $row->tags = $blogTranslate->tags;
                $row->description = $blogTranslate->description;
                $row->seo_title = $blogTranslate->seo_title;
                $row->seo_keyword = $blogTranslate->seo_keyword;
                $row->seo_tag = $blogTranslate->seo_tag;
                $row->seo_description = $blogTranslate->seo_description;
                $row->language_code = $blogTranslate->language_code;
            }
            $row->already_viewed = in_array($row->id, $viewedBlogIds);
            $row->trimed_description = strip_tags($row->description);
            $row->trimed_description = str_replace("&nbsp;", '', $row->trimed_description);
            $row->trimed_description = str_replace("&#39;", "'", $row->trimed_description);

            if ($row->thumb_image != '') {
                $row->thumb_image = url('upload/blog/thumb/360/' . $row->thumb_image);
            } else {
                $row->thumb_image = url('upload/blog/thumb/default.png');
            }
            $check_image = BlogImages::where('blog_id', $row->id)->pluck('image');
            $blog_image = array();
            if (count($check_image)) {
                foreach ($check_image as $value) {
                    $value = url('upload/blog/banner/800/' . $value);
                    array_push($blog_image, $value);
                }
                $row->banner_image = $blog_image;
            } else {
                $blog_image[0] = url('upload/author/default.png');
                $row->banner_image = $blog_image;
            }
            //if ($userID != null) {
            //  $vote = Vote::where('user_id', $userID)->where('blog_id', $row->id)->first();
            //if ($vote) {
            //  $row->is_vote = 1;
            //} else {
            //  $row->is_vote = 0;
            //}
            //$bookmarked = BookMarkPost::where('user_id', $userID)->where('blog_id', $row->id)->first();
            //if ($bookmarked) {
            // $row->is_bookmark = 1;
            //} else {
            //  $row->is_bookmark = 0;
            //}
            //}else {
            // $row->is_vote = 0;
            //$row->is_bookmark = 0;
            //}
            if ($header != null) {
                $vote = Vote::where('user_id', $header)->where('blog_id', $row->id)->first();
                if ($vote) {
                    $row->is_vote = 1;
                } else {
                    $row->is_vote = 0;
                }
                $bookmarked = BookMarkPost::where('user_id', $header)->where('blog_id', $row->id)->first();
                if ($bookmarked) {
                    $row->is_bookmark = 1;
                } else {
                    $row->is_bookmark = 0;
                }
            } else {
                $row->is_vote = 0;
                $row->is_bookmark = 0;
            }
            $row->view_count = BlogViewCount::where('blog_id', $row->id)->count();
            $row->story_view_count = \App\Models\StoryViewCount::where('story_id', $row->id)->count();
            $total_votes = Vote::where('blog_id', $row->id)->count();
            $yes_votes = Vote::where('blog_id', $row->id)->where('vote', 1)->count();
            $no_votes = Vote::where('blog_id', $row->id)->where('vote', 0)->count();
            if ($yes_votes != 0) {
                $yes_percent = ($yes_votes / $total_votes) * 100;
            } else {
                $yes_percent = 0;
            }
            if ($no_votes != 0) {
                $no_percent = ($no_votes / $total_votes) * 100;
            } else {
                $no_percent = 0;
            }
            $row->yes_percent = round($yes_percent);
            $row->no_percent = round($no_percent);
            $author = User::where('id', $row->created_by)->first();
            if ($author) {
                $row->author_name = $author->name;
                if ($author->photo != null || $author->photo != '') {
                    $row->image = $author->photo;
                } else {
                    $row->image = url('upload/user/default.png');
                }
            } else {
                $row->author_name = "";
                $row->image = url('upload/user/default.png');
            }
            $cat_id = BlogCategory::where('blog_id', $row->id)->value('category_id');

            $category = Category::where('id', $cat_id)->first();
            if ($category) {
                $catTranslate = CategoryTranslation::where('category_id', $category->id)->where('language_code', $this->language)->first();
                if ($catTranslate) {
                    $category->name = $catTranslate->name;
                }

                $row->category_name = $category->name;
                $row->color = $category->color;
            } else {
                $row->category_name = "";
                $row->color = "";
            }
            $row->time = $row->time . " min";
            $row->created_at = date("d-M-Y", strtotime($row->created_at));
            $row->create_date = date("d M Y // h:i a", strtotime($row->schedule_date));
            $blog_image = array();
        }
        return $this->sendResponse($blog, __('message_alerts.blog_list'));
    }
    public function getAllFeeds(Request $request)
    {
        $search = $request->all();
        $pagination_no = 10;
        if (isset($search['per_page']) && !empty($search['per_page'])) {
            $pagination_no = $search['per_page'];
        }
        //$blogCategory = UserFeed::where('user_id',$userID)->pluck('category_id')->toArray();
        //$blogCategory = BlogCategory::pluck('category_id')->toArray();
        $blog_image = array();
        $final_blog = array();
        $header = $request->header('userData');
        $viewedBlogIds = [];

        if (isset($search['device_id']) && !empty($search['device_id']) && $header != null) {
            $viewedBlogIds = BlogViewCount::where('device_id', $search['device_id'])
                ->where('user_id', $header)->pluck('blog_id')->toArray();
        } else if (isset($search['device_id']) && !empty($search['device_id'])) {
            $viewedBlogIds = BlogViewCount::where('device_id', $search['device_id'])
                ->pluck('blog_id')->toArray();
        } else if ($header != null) {
            $viewedBlogIds = BlogViewCount::where('user_id', $header)
                ->pluck('blog_id')->toArray();
        }

        $blog = Blog::where('status', 1)
            ->where('schedule_date', "<=", date("Y-m-d H:i:s"))
            ->whereNotIn('id', $viewedBlogIds)
            ->with('blog_category')
            ->orderBy('schedule_date', 'DESC')
            ->paginate($pagination_no)->appends('per_page', $pagination_no);

        //whereIn('category_id',$blogCategory)->     


        foreach ($blog as $row) {
            $flag = false;
            $blogTranslate = BlogTranslation::where('blog_id', $row->id)->where('language_code', $this->language)->first();
            if ($blogTranslate) {
                $flag = true;
                $row->type = 'blog';

                $row->title = $blogTranslate->title;
                $row->tags = $blogTranslate->tags;
                $row->description = $blogTranslate->description;
                $row->seo_title = $blogTranslate->seo_title;
                $row->seo_keyword = $blogTranslate->seo_keyword;
                $row->seo_tag = $blogTranslate->seo_tag;
                $row->seo_description = $blogTranslate->seo_description;
                $row->language_code = $blogTranslate->language_code;
            }
            $row->already_viewed = in_array($row->id, $viewedBlogIds);
            $row->trimed_description = strip_tags($row->description);
            $row->trimed_description = str_replace("&nbsp;", '', $row->trimed_description);
            $row->trimed_description = str_replace("&#39;", "'", $row->trimed_description);

            if ($row->thumb_image != '') {
                $row->thumb_image = url('upload/blog/thumb/360/' . $row->thumb_image);
            } else {
                $row->thumb_image = url('upload/blog/thumb/default.png');
            }
            $check_image = BlogImages::where('blog_id', $row->id)->pluck('image');
            $blog_image = array();
            if (count($check_image)) {
                foreach ($check_image as $value) {
                    $value = url('upload/blog/banner/800/' . $value);
                    array_push($blog_image, $value);
                }
                $row->banner_image = $blog_image;
            } else {
                $blog_image[0] = url('upload/author/default.png');
                $row->banner_image = $blog_image;
            }
            //if ($userID != null) {
            //  $vote = Vote::where('user_id', $userID)->where('blog_id', $row->id)->first();
            //if ($vote) {
            //  $row->is_vote = 1;
            //} else {
            //  $row->is_vote = 0;
            //}
            //$bookmarked = BookMarkPost::where('user_id', $userID)->where('blog_id', $row->id)->first();
            //if ($bookmarked) {
            // $row->is_bookmark = 1;
            //} else {
            //  $row->is_bookmark = 0;
            //}
            //}else {
            // $row->is_vote = 0;
            //$row->is_bookmark = 0;
            //}
            if ($header != null) {
                $vote = Vote::where('user_id', $header)->where('blog_id', $row->id)->first();
                if ($vote) {
                    $row->is_vote = 1;
                } else {
                    $row->is_vote = 0;
                }
                $bookmarked = BookMarkPost::where('user_id', $header)->where('blog_id', $row->id)->first();
                if ($bookmarked) {
                    $row->is_bookmark = 1;
                } else {
                    $row->is_bookmark = 0;
                }
            } else {
                $row->is_vote = 0;
                $row->is_bookmark = 0;
            }
            $row->view_count = BlogViewCount::where('blog_id', $row->id)->count();
            $row->story_view_count = \App\Models\StoryViewCount::where('story_id', $row->id)->count();
            $total_votes = Vote::where('blog_id', $row->id)->count();
            $yes_votes = Vote::where('blog_id', $row->id)->where('vote', 1)->count();
            $no_votes = Vote::where('blog_id', $row->id)->where('vote', 0)->count();
            if ($yes_votes != 0) {
                $yes_percent = ($yes_votes / $total_votes) * 100;
            } else {
                $yes_percent = 0;
            }
            if ($no_votes != 0) {
                $no_percent = ($no_votes / $total_votes) * 100;
            } else {
                $no_percent = 0;
            }
            $row->yes_percent = round($yes_percent);
            $row->no_percent = round($no_percent);
            $author = Author::where('id', $row->author_id)->first();
            if ($author) {
                $row->author_name = $author->name;
                if ($author->image != null || $author->image != '') {
                    $row->image = url('upload/author/original/' . $author->image);
                } else {
                    $row->image = url('upload/author/default.png');
                }
            } else {
                $row->author_name = "";
                $row->image = url('upload/author/default.png');
            }
            $category = Category::where('id', $row->category_id)->first();
            if ($category) {
                $catTranslate = CategoryTranslation::where('category_id', $category->id)->where('language_code', $this->language)->first();
                if ($catTranslate) {
                    $category->name = $catTranslate->name;
                }

                $row->category_name = $category->name;
                $row->color = $category->color;
            } else {
                $row->category_name = "";
                $row->color = "";
            }
            $row->time = $row->time . " min";
            $row->created_at = date("d-M-Y", strtotime($row->created_at));
            $row->create_date = date("d M Y // h:i a", strtotime($row->schedule_date));
            $blog_image = array();
        }
        return $this->sendResponse($blog, __('message_alerts.blog_list'));
    }

    public function store(Request $request)
    {
        $validate = [
            'userId' => 'required',
            'feed' => 'required',
        ];
        $validator = Validator::make($request->all(), $validate);
        if ($validator->fails()) {
            $data['error'] = $validator->errors();

            return response(\Helpers::sendFailureAjaxResponse($data['error']));
        } else {

            $feed_array = explode(',', $request->feed);
            UserFeed::where('user_id', $request->userId)->delete();
            foreach ($feed_array as $key => $value) {

                UserFeed::updateOrCreate(['category_id' => $value, 'user_id' => $request->userId], []);
            }

            return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.success'), UserFeed::where('user_id', $request->userId)->get()));
        }
    }

    public function arrayPaginator($array, $request)
    {
        $post = $request->all();
        $per_page_number = 10;
        $page = (isset($post['page']) && !empty($post['page'])) ? $post['page'] : 1;
        $perPage = (isset($post['perpage'])) ? $post['perpage'] : $per_page_number;
        $offset = ($page * $perPage) - $perPage;
        $sliceArray = array_slice($array, $offset, $perPage, true);
        $finalArray = array();
        foreach ($sliceArray as $row) {
            array_push($finalArray, $row);
        }

        return new LengthAwarePaginator($finalArray, count($array), $perPage, $page, ['path' => $request->url(), 'query' => $request->query()]);
    }
}
