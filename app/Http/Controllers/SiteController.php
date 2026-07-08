<?php



namespace App\Http\Controllers;



use App\Http\Controllers\Controller;

use App\Models\CmsPages;

use Illuminate\Support\Facades\Validator;

use App\Models\SiteContent;

use App\Models\Category;

use App\Models\Social;

use App\Models\Blog;

use App\Models\BlogImages;

use App\Models\BlogCategory;

use App\Models\User;

use App\Models\BlogViewCount;

use Illuminate\Http\Request;
use Carbon\Carbon;

use anlutro\LaravelSettings\Facade as Setting;

use Illuminate\Support\Facades\Session;

use Auth;

use App\Models\Vote;

use App\Models\Languages;

use App\Models\BookMarkPost;

use App\Models\Epaper;

use App\Models\EpaperTranslation;

use App\Models\LiveNewsTranslation;

use App\Models\LiveNews;

use App\Models\CategoryTranslation;

use App\Models\BlogTranslation;

use App\Models\CmsPagesTranslation;



class SiteController extends VoiceRSS
{



    public function index()
    {

        if (isset($_GET['home'])) {

            if (in_array($_GET['home'], ['home_1', 'home_2', 'home_3', 'home_4', 'home_5'])) {

                session(['active_home' => $_GET['home']]);
            } else {

                session('active_home', setting('homepage_theme'));
            }
        } else {

            session('active_home', setting('homepage_theme'));
        }



        $category = Category::getCategoryOnFilter(5, 'is_featured');

        $side_category = Category::getCategoryOnFilter(3);

        $not_featured_category = Category::getNotFeaturedCategory();

        $social = Social::where('status', 1)->get();

        $recent_blog = Blog::frontBlog('recent', 2);

        $side_recent_blog = Blog::frontBlog('recent', 3);

        $site_content = CmsPages::getCmsPages();



        if (setting('homepage_theme') == 'home_5')

            $slider_post = Blog::frontBlog('slider', 5);
        else

            $slider_post = Blog::frontBlog('slider', 8);



        $recent_middle_post = Blog::frontBlog('recent', 15);

        $top_of_week = Blog::frontBlog('top_of_week', 8);

        $editors_post = Blog::frontBlog('editor', 10);



        if (session('active_home')) {

            $homeName = session('active_home');
        } else {

            $homeName = setting('homepage_theme');
        }



        if ($homeName == 'home_5') {

            $slider_post = Blog::frontBlog('slider', 5);
        } else if ($homeName == 'home_1') {

            $slider_post = Blog::frontBlog('slider', 4);
        } else {

            $slider_post = Blog::frontBlog('slider', 8);
        }



        return view('site.home.' . $homeName . '.index', [

            'category' => $category,

            'social' => $social,

            'site_content' => $site_content,

            'side_category' => $side_category,

            'side_recent_blog' => $side_recent_blog,

            'slider_post' => $slider_post,

            'recent_middle_post' => $recent_middle_post,

            'top_of_week_post' => $top_of_week,

            'editors_post' => $editors_post,

            'recent_blog' => $recent_blog,

            'not_featured_category' => $not_featured_category,

        ]);
    }



    public function category_blogs(Request $request)
    {

        // dd("gdgd");

        $category_blogs = Category::where('slug', $request->category)->first();

        if (!$category_blogs) {

            return redirect('/');
        }

        $categoryBlog = Blog::getCategoryBlog($category_blogs->id);

        $category = Category::getCategoryOnFilter(5, 'is_featured');

        $side_category = Category::getCategoryOnFilter(3);

        $social = Social::where('status', 1)->get();

        $recent_blog = Blog::frontBlog('recent', 2);

        $side_recent_blog = Blog::frontBlog('recent', 3);

        $site_content = CmsPages::getCmsPages();

        $not_featured_category = Category::getNotFeaturedCategory();

        return view('site.blog.index', [

            'category' => $category,

            'category_blogs' => $category_blogs,

            'categoryBlog' => $categoryBlog,

            'social' => $social,

            'site_content' => $site_content,

            'side_category' => $side_category,

            'side_recent_blog' => $side_recent_blog,

            'recent_blog' => $recent_blog,

            'not_featured_category' => $not_featured_category,

        ]);
    }



    public function blog_detail($slug)
    {

        $blog_detail = Blog::getSingleBlog($slug);

        if ($blog_detail == false) {

            $blog_detail = Blog::getSingleBlog($slug);
            if ($blog_detail == false) {

                return redirect('/');
            }
        }



        if ($blog_detail) {

            if (Auth::user()) {

                $views = BlogViewCount::where('user_id', Auth::user()->id)->where('blog_id', $blog_detail->id)->first();

                if (!$views) {

                    $postData = array(

                        'user_id' => Auth::user()->id,

                        'blog_id' => $blog_detail->id,

                        'created_at' => date('Y-m-d H:i:s')

                    );

                    BlogViewCount::insertGetID($postData);
                }
            } else {

                $postData = array(

                    'user_id' => 0,

                    'blog_id' => $blog_detail->id,

                    'created_at' => date('Y-m-d H:i:s')

                );

                BlogViewCount::insertGetID($postData);
            }

            $cat_id_arr = array();

            $blog_cat = BlogCategory::where('blog_id', $blog_detail->id)->get();

            if (count($blog_cat)) {

                foreach ($blog_cat as $blog_cat_data) {

                    array_push($cat_id_arr, $blog_cat_data->category_id);
                }
            }

            $related_blogs = Blog::getRelatedBlog($blog_detail->id, $cat_id_arr, 4);
        } else {

            $related_blogs = array();
        }



        $category = Category::getCategoryOnFilter(5, 'is_featured');

        $side_category = Category::getCategoryOnFilter(3);

        $social = Social::where('status', 1)->get();

        $recent_blog = Blog::frontBlog('recent', 2);

        $side_recent_blog = Blog::frontBlog('recent', 3);



        $site_content = CmsPages::getCmsPages();

        $not_featured_category = Category::getNotFeaturedCategory();

        return view('site.blog.blog_layout.' . setting('layout') . '.index', [

            'category' => $category,

            'related_blogs' => $related_blogs,

            'social' => $social,

            'site_content' => $site_content,

            'side_category' => $side_category,

            'side_recent_blog' => $side_recent_blog,

            'recent_blog' => $recent_blog,

            'blog_detail' => $blog_detail,

            'not_featured_category' => $not_featured_category

        ]);
    }



    public function cms($page)
    {

        $data = array();

        $content = CmsPages::where('page_name', $page)->first();

        if ($content) {

            $language = \Helpers::returnUserLangCode();

            $page = CmsPagesTranslation::where('cms_id', $content->id)->where('language_code', $language)->first();

            if ($page) {

                $content->title = $page->title;

                $content->page_title = $page->page_title;

                $content->description = $page->description;

                $content->meta_char = $page->meta_char;

                $content->meta_desc = $page->meta_desc;
            }



            if ($content->image) {

                if (file_exists(public_path() . '/upload/cms/original/' . $content->image)) {

                    $content->image = url('upload/cms/original/' . $content->image);
                } else {

                    $content->image = url('site/img/1920x982.png');
                }
            } else {

                $content->image = url('site/img/1920x982.png');
            }

            $category = Category::getCategoryOnFilter(5, 'is_featured');

            $side_category = Category::getCategoryOnFilter(3);

            $social = Social::where('status', 1)->get();

            $recent_blog = Blog::frontBlog('recent', 2);

            $side_recent_blog = Blog::frontBlog('recent', 3);

            $site_content = CmsPages::getCmsPages();

            $not_featured_category = Category::getNotFeaturedCategory();

            return view('site.cms.index', [

                'category' => $category,

                'social' => $social,

                'content' => $content,

                'site_content' => $site_content,

                'side_category' => $side_category,

                'side_recent_blog' => $side_recent_blog,

                'recent_blog' => $recent_blog,

                'data' => $data,

                'not_featured_category' => $not_featured_category,

            ]);
        } else {

            return redirect('/');
        }
    }



    public function setLangugae(Request $request)
    {

        $post = $request->all();

        if (isset($post['lang_code'])) {

            if (auth()->user() && auth()->user()->type == 'user') {

                User::where('id', auth()->user()->id)->update(['lang_code' => $post['lang_code']]);

                Session::put('locale', $post['lang_code']);

                setcookie('lang_code', $post['lang_code'], time() + 60 * 60 * 24 * 365);
            } else {

                Session::put('locale', $post['lang_code']);

                setcookie('lang_code', $post['lang_code'], time() + 60 * 60 * 24 * 365);
            }
        }

        return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.success'), []));
    }



    public function loadDataAjax(Request $request)
    {

        $post = $request->all();

        $output = '';

        $loadedIds = [];



        $flag = false;

        $post['category_ids'] = array();





        $post['loaded_ids'] = explode(',', $post['loaded_ids']);

        if (isset($post['active_category_string']) && $post['active_category_string'] != '') {

            $post['category_ids'] = explode(',', $post['active_category_string']);
        }





        $blogs = Blog::where('status', 1)->whereIn('category_id', $post['category_ids'])->with('author')->paginate(1);

        if ($blogs) {

            foreach ($blogs as $blog) {

                if (!in_array($blog->id, $loadedIds)) {

                    array_push($loadedIds, $blog->id);
                }

                $language = \Helpers::returnUserLangCode();

                $blogTranslate = BlogTranslation::where('blog_id', $blog->id)->where('language_code', $language)->first();

                $blog->likes = Vote::where('blog_id', $blog->id)->get();

                $blog->viewcount = BlogViewCount::where('blog_id', $blog->id)->count();

                $blog->blog_category_data = BlogCategory::where('blog_id', $blog->id)->with('category')->first();

                if (Auth::user()) {

                    $bookmark = BookMarkPost::where('blog_id', $blog->id)->where('user_id', Auth::user()->id)->first();



                    if ($bookmark) {

                        $blog->isBookmarked = 1;
                    } else {

                        $blog->isBookmarked = 0;
                    }
                } else {

                    $blog->isBookmarked = 0;
                }

                $blog->BlogImages = BlogImages::where('blog_id', $blog->id)->first();



                $tempTags = [];

                if ($blogTranslate) {

                    $flag = true;

                    $blog->title = $blogTranslate->title;

                    $blog->tags = $blogTranslate->tags;

                    $blog->description = $blogTranslate->description;

                    $blog->seo_title = $blogTranslate->seo_title;

                    $blog->seo_keyword = $blogTranslate->seo_keyword;

                    $blog->seo_tag = $blogTranslate->seo_tag;

                    $blog->seo_description = $blogTranslate->seo_description;

                    $blog->tags = explode(',', $blog->tags);

                    $tempTags = $blog->tags;

                    $blog->tags_changed = $blog->tags;
                }



                $tags = '';

                if ($blog->tags != null) {

                    for ($i = 0; $i < count($tempTags); $i++) {

                        $tags .= '<li><a rel="tag">' . $tempTags[$i] . '</a></li>';
                    }
                }

                $url = $blog->url;

                $pieces = parse_url($url);

                $domain = isset($pieces['host']) ? $pieces['host'] : $pieces['path'];

                if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {

                    $blog->url_host = $regs['domain'];
                }

                $randomId = rand(0, 99999999);

                $iframe = '';
            }



            $view = view('site.blog.blog_layout.load-more-blog', compact('blogs'))->render();

            $data = [];

            $data['total_blog'] = count($loadedIds);

            $loadedIds = implode(',', $loadedIds);

            $data['loaded_ids'] = $loadedIds;

            if ($flag) {

                $data['output'] = $view;
            }



            return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.success'), $data));
        }
    }



    public function blogSpeech(Request $request)
    {

        $post = $request->all();

        if ($post['blog_id'] && $post['blog_id'] != '') {

            $blog = Blog::find($post['blog_id']);

            if ($blog) {

                $language = \Helpers::returnUserLangCode();

                $blogTranslate = BlogTranslation::where('blog_id', $blog->id)->where('language_code', $language)->first();

                $blog->blog_category_data = BlogCategory::where('blog_id', $likes->id)->with('category')->first();

                $desc = strip_tags($blog->description);

                if ($blogTranslate) {

                    $desc = strip_tags($blogTranslate->description);
                }



                if ($desc != '') {

                    $base64Voice = $this->getSpeech($desc, $language, $blog->voice, $blog->blog_accent_code);

                    return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.success'), ['voice' => $base64Voice]));
                } else {

                    return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.success'), ['voice' => '']));
                }
            } else {

                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error'), []));
            }
        }

        return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error'), []));
    }

    // public function newsDeletion(Request $request)
    // {
    //   $newsdelet = SiteContent::find(64);
    //   $deleted_days_limit = (int)$newsdelet->value;
    //   $dateBeforeSixDays = Carbon::now()->subDays($deleted_days_limit)->toDateString();


    //   $blog = Blog::where('schedule_date', '<', $dateBeforeSixDays)->where('id',1171)->where('status',1)->get();
    //   foreach($blog as $blogs)
    //   {
    //       $blogImage = BlogImages::where('blog_id',$blogs->id)->get();
    //       foreach($blogImage as $blogImages)
    //       {
    //             $imageUrl = 'upload/blog/banner/360/' . $blogImages->image;
    //             $fullImagePath = public_path($imageUrl);

    //             if (file_exists($fullImagePath)) {
    //                 unlink($fullImagePath);
    //             }
    //             $deleteImage = BlogImages::find($blogImages->id);
    //             $deleteImage->delete();
    //       }
    //     $deleteblog = Blog::find($blogs->id);
    //     $deleteblog->delete();
    //   }

    //   return dd('delete blog done');
    // }



}
