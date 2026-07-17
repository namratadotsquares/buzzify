<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Author;
use App\Models\SiteContent;
use App\Models\SearchLog;
use App\Models\BookMarkPost;
use App\Models\BlogViewCount;
use App\Models\BlogCategory;
use App\Models\BlogImages;
use App\Models\Vote;
use App\Models\CategoryTranslation;
use App\Models\BlogTranslation;
use App\Models\User;
use App\Models\Ads;
use App\Models\Ads_images;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use Illuminate\Pagination\LengthAwarePaginator;

class CategoryAPIController extends Controller
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


    /**
     * Show Category view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {


        try {
            $search = $request->all();

            $header = $request->header('userData');
            $pagination_no = 100;
            $readids = json_decode($request->input('blog_ids'));
            if (isset($search['per_page']) && !empty($search['per_page'])) {
                $pagination_no = $search['per_page'];
                //  return $pagination_no;
            }
            if ($header != null) {
                $viewedBlogIds = [];
                if (isset($search['device_id']) && !empty($search['device_id']) && $header != null) {
                    $viewedBlogIds = BlogViewCount::where('device_id', $search['device_id'])
                        ->orderBy('created_at', 'desc')->where('user_id', $header)->pluck('blog_id')->toArray();
                } else if (isset($search['device_id']) && !empty($search['device_id'])) {
                    $viewedBlogIds = BlogViewCount::where('device_id', $search['device_id'])
                        ->orderBy('created_at', 'desc')->pluck('blog_id')->toArray();
                } else if ($header != null) {
                    $viewedBlogIds = BlogViewCount::where('user_id', $header)
                        ->orderBy('created_at', 'desc')->pluck('blog_id')->toArray();
                }
                $category = Category::where('status', 1)
                    ->orderByRaw("CAST(`order` AS UNSIGNED) ASC")
                    // ->whereNotIn('name', ['Personalization']) 
                    ->leftjoin('user_feed', function ($join) use ($header) {
                        $join->on('user_feed.category_id', '=', 'category.id')->where('user_feed.user_id', $header);
                    })
                    ->select('category.*', 'user_feed.id as user_feed_id')

                    ->orderBy('order', 'ASC')->get();
                $sliders = Blog::where('status', 1)
                    ->where('schedule_date', "<=", date("Y-m-d H:i:s"))
                    ->where(function ($query) {
                        $query->where('is_featured', 1)
                            ->orWhere('is_slider', 1);
                    })
                    ->orderBy('schedule_date', 'Desc')
                    ->get();
                  $sliders = $sliders->sortBy(function ($item) use ($viewedBlogIds) {
                    $viewIndex = array_search($item->id, $viewedBlogIds);

                    if ($viewIndex === false) {
                        // Unviewed → stay at top
                        return 0;
                    }

                    // Viewed → push down (add large offset + reverse order so recent views are last)
                    return 100000 + (count($viewedBlogIds) - $viewIndex);
                })->values();

                // $sliders = Blog::query()
                // ->where('status', 1)
                // ->where(function($q) {
                //     $q->where('is_slider', 1)
                //     ->orWhere('is_featured', 1);
                // })
                // ->leftJoin('blog_view_count', function($join) use ($header){
                //     $join->on('blog.id', '=', 'blog_view_count.blog_id')
                //         ->where('blog_view_count.user_id', $header); // track by user
                // })
                // ->select('blog.*')
                // ->selectRaw('CASE WHEN dp_blog_view_count.id IS NULL THEN 0 ELSE 1 END as has_viewed')
                // ->selectRaw('MAX(dp_blog_view_count.created_at) as last_viewed_at')
                // ->groupBy('blog.id')
                // ->orderBy('has_viewed', 'ASC')
                // ->orderBy('last_viewed_at', 'DESC')
                // ->orderBy('order', 'ASC')
                // ->get();

            } else {
                $viewedBlogIds = [];
                if (isset($search['device_id']) && !empty($search['device_id']) && $header != null) {
                    $viewedBlogIds = BlogViewCount::where('device_id', $search['device_id'])
                        ->orderBy('created_at', 'desc')->where('user_id', $header)->pluck('blog_id')->toArray();
                } else if (isset($search['device_id']) && !empty($search['device_id'])) {
                    $viewedBlogIds = BlogViewCount::where('device_id', $search['device_id'])
                        ->orderBy('created_at', 'desc')->pluck('blog_id')->toArray();
                } else if ($header != null) {
                    $viewedBlogIds = BlogViewCount::where('user_id', $header)
                        ->orderBy('created_at', 'desc')->pluck('blog_id')->toArray();
                }

                $category = Category::where('status', 1)
                    ->orderByRaw("CAST(`order` AS UNSIGNED) ASC")
                    ->whereNotIn('name', ['Personalization'])

                    ->orderBy('order', 'ASC')->get();
                // $sliders = Blog::where('status', 1)
                //     ->where('is_slider', '1')
                //     ->orderBy('order', 'ASC')->get();
                $sliders = Blog::where('status', 1)
                    ->where('schedule_date', "<=", date("Y-m-d H:i:s"))
                    ->where(function ($query) {
                        $query->where('is_featured', 1)
                            ->orWhere('is_slider', 1);
                    })
                    ->orderBy('schedule_date', 'Desc')
                    ->get();
                $sliders = $sliders->sortBy(function ($item) use ($viewedBlogIds) {
                    $viewIndex = array_search($item->id, $viewedBlogIds);

                    if ($viewIndex === false) {
                        // Unviewed → stay at top
                        return 0;
                    }

                    // Viewed → push down (add large offset + reverse order so recent views are last)
                    return 100000 + (count($viewedBlogIds) - $viewIndex);
                })->values();

                // $sliders = Blog::query()
                //     ->where('status', 1)
                //     ->where(function($q) {
                //         $q->where('is_slider', 1)
                //         ->orWhere('is_featured', 1);
                //     })
                //     ->leftJoin('blog_view_count', function($join){
                //         $join->on('blog.id', '=', 'blog_view_count.blog_id');
                //             // ->where('blog_view_count.user_id', $userId); // track by user
                //     })
                //     ->select('blog.*')
                //     ->selectRaw('CASE WHEN dp_blog_view_count.id IS NULL THEN 0 ELSE 1 END as has_viewed')
                //     ->selectRaw('MAX(dp_blog_view_count.created_at) as last_viewed_at')
                //     ->groupBy('blog.id')
                //     ->orderBy('has_viewed', 'ASC')
                //     ->orderBy('last_viewed_at', 'DESC')
                //     ->orderBy('order', 'ASC')
                //     ->get();

            }
            $final_arr = array();
            $i = 0;

            $final_arr['slider'] = array();
            foreach ($sliders as $slider) {
                $blogTranslate = BlogTranslation::where('blog_id', $slider->id)->where('language_code', $this->language)->first();
                if ($blogTranslate) {
                    $slider->title = $blogTranslate->title;
                    $slider->tags = $blogTranslate->tags;
                    $slider->description = $blogTranslate->description;
                    $slider->seo_title = $blogTranslate->seo_title;
                    $slider->seo_keyword = $blogTranslate->seo_keyword;
                    $slider->seo_tag = $blogTranslate->seo_tag;
                    $slider->seo_description = $blogTranslate->seo_description;
                }
                $slider->trimed_description = strip_tags($slider->description);
                $slider->trimed_description =  str_replace("&nbsp;", '', $slider->trimed_description);
                $slider->trimed_description =  str_replace("&#39;", "'", $slider->trimed_description);
                $slider->created_at = date("d M Y h:i a", strtotime($slider->created_at));
                if ($slider->thumb_image != '') {
                    $slider->thumb_image = url('upload/blog/thumb/360/' . $slider->thumb_image);
                } else {
                    $slider->thumb_image = url('upload/blog/thumb/default.png');
                }
                $check_image = BlogImages::where('blog_id', $slider->id)->pluck('image');
                $blog_image = array();
                if (count($check_image)) {
                    foreach ($check_image as $value) {
                        $value = url('upload/blog/banner/800/' . $value);
                        array_push($blog_image, $value);
                    }
                    $slider->banner_image = $blog_image;
                } else {
                    $blog_image[0] = url('upload/author/default.png');
                    $slider->banner_image =  $blog_image;
                }
                array_push($final_arr['slider'], $slider);
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
            $final_arr['category'] = array();
            foreach ($category as $row) {
                $blog_arr = array();
                $getBlogs = BlogCategory::where('category_id', $row->id)->get();
                if (count($getBlogs)) {
                    foreach ($getBlogs as $getBlogs_data) {
                        array_push($blog_arr, $getBlogs_data->blog_id);
                    }
                }
                $catTranslate = CategoryTranslation::where('category_id', $row->id)->where('language_code', $this->language)->first();
                if ($catTranslate) {
                    $row->name = $catTranslate->name;
                }

                if ($row->image != '') {
                    $row->image = url('upload/category/original/' . $row->image);
                } else {
                    $row->image = url('upload/category/default.png');
                }
                // $row->blog = array();

                $blog = Blog::where('status', 1)
                    ->whereIn('id', $blog_arr)
                    ->where('schedule_date', "<=", date("Y-m-d H:i:s"))
                    ->with('blog_category')
                    ->when(!empty($readids), function ($query) use ($readids) {
                        $query->whereNotIn('id', $readids);
                    })
                    ->orderBy('created_at', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->paginate($pagination_no)
                    ->appends('per_page', $pagination_no);

                if ($blog) {
                    // foreach ($blog as $detail) {

                    //     $blogTranslate = BlogTranslation::where('blog_id',$detail->id)->where('language_code',$this->language)->first();
                    //     if ($blogTranslate) {
                    //         $detail->title = $blogTranslate->title;
                    //         $detail->tags = $blogTranslate->tags;
                    //         $detail->description = $blogTranslate->description;
                    //         $detail->seo_title = $blogTranslate->seo_title;
                    //         $detail->seo_keyword = $blogTranslate->seo_keyword;
                    //         $detail->seo_tag = $blogTranslate->seo_tag;
                    //         $detail->seo_description = $blogTranslate->seo_description;
                    //     }
                    //     $detail->trimed_description = strip_tags($detail->description);
                    //     $detail->trimed_description =  str_replace("&nbsp;",'',$detail->trimed_description);
                    //     $detail->trimed_description =  str_replace("&#39;","'",$detail->trimed_description);
                    //     $detail->created_at = date("d M Y h:i a",strtotime($detail->created_at));
                    //     if($detail->thumb_image!=''){
                    //         $detail->thumb_image= url('upload/blog/thumb/360/'.$detail->thumb_image);
                    //     }else{
                    //         $detail->thumb_image= url('upload/blog/thumb/default.png');
                    //     }
                    //     $check_image = BlogImages::where('blog_id',$detail->id)->pluck('image');
                    //     $blog_image = array();
                    //     if(count($check_image)){
                    //         foreach ($check_image as $value) {
                    //             $value = url('upload/blog/banner/800/'.$value);
                    //             array_push($blog_image,$value);
                    //         }
                    //         $detail->banner_image = $blog_image;
                    //     }else{
                    //         $blog_image[0] = url('upload/author/default.png');
                    //         $detail->banner_image =  $blog_image;
                    //     }
                    //     if($header!=null){
                    //         $vote = Vote::where('user_id',$header)->where('blog_id',$detail->id)->first();
                    //         if($vote){
                    //             $detail->is_vote = 1;
                    //         }else{
                    //             $detail->is_vote = 0;
                    //         }
                    //         $bookmarked = BookMarkPost::where('user_id',$header)->where('blog_id',$detail->id)->first();
                    //         if($bookmarked){
                    //             $detail->is_bookmark = 1;
                    //         }else{
                    //             $detail->is_bookmark = 0;
                    //         }
                    //     }else{
                    //         $detail->is_vote = 0;
                    //         $detail->is_bookmark = 0;
                    //     }
                    //     $total_votes = Vote::where('blog_id',$detail->id)->count();
                    //     $yes_votes = Vote::where('blog_id',$detail->id)->where('vote',1)->count();
                    //     $no_votes = Vote::where('blog_id',$detail->id)->where('vote',0)->count();
                    //     $detail->view_count = BlogViewCount::where('blog_id',$detail->id)->count();
                    //     if($yes_votes!=0){
                    //         $yes_percent = ($yes_votes/$total_votes)*100;
                    //     }else{
                    //         $yes_percent = 0;
                    //     }
                    //     if($no_votes!=0){
                    //         $no_percent = ($no_votes/$total_votes)*100;
                    //     }else{
                    //         $no_percent = 0;
                    //     }
                    //     $detail->type = "post";
                    //     $detail->yes_percent = round($yes_percent);
                    //     $detail->no_percent = round($no_percent);
                    //     $author = Author::where('status',1)->where('id',$detail->author_id)->first();
                    //     if($author){
                    //         $detail->author_name = $author->name;
                    //         if($author->image!=''){
                    //             $detail->image = url('upload/author/original/'.$author->image);
                    //         }else{
                    //             $detail->image = url('upload/author/default.png');
                    //         }
                    //     }else{
                    //         $detail->author_name = "";
                    //         $detail->image = url('upload/author/default.png');
                    //     }
                    //     $detail->media = BlogImages::where('blog_id', $detail->id)->get();
                    //     if(count($detail->media)){
                    //         foreach($detail->media as $media){
                    //             if($media->type=='image'){
                    //                 $media->image = url('upload/blog/banner/original/' .$media->image);
                    //             }
                    //         }
                    //     }
                    //     $detail->category_name = $row->name;
                    //     $detail->color = $row->color;
                    //     $detail->time = $detail->time. " min";
                    //     $detail->create_date = date("d M Y // h:i a",strtotime($detail->schedule_date));
                    // }
                    // $row['blog'] = $blog;
                    if ($row->user_feed_id != null) {
                        $row['isMyFeed'] = true;
                    } else {
                        $row['isMyFeed'] = false;
                    }

                    $result = [];
                    $adCount = count($ads_list);
                    $adIndex = 0;

                    $BlogCount = 0;
                    foreach ($blog as $key => $item) {
                        if (count($ads_list)) {
                            if ($BlogCount == $ads_list[$adIndex]['frequency']) {
                                $ads_list[$adIndex]['type'] = "ads";
                                $result[] = $ads_list[$adIndex];
                                $adIndex = ($adIndex + 1) % $adCount;
                                $BlogCount = 0;
                            }
                            $result[] = $item;
                            $BlogCount++;
                        } else {
                            $result[] = $item;
                        }
                    }

                    $dataCheck = $this->arrayPaginator($result, $request);
                    $row->index = $i;
                    array_push($final_arr['category'], $row);
                    $i++;
                }
            }
            // return $final_arr;
            return $this->sendResponse($final_arr, __('message_alerts.category_list'));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }
    }
    public function list_with_blog(Request $request)
    {


        try {
            $header = $request->header('userData');
            $pagination_no = 100;
            $readids = json_decode($request->input('blog_ids'));
            if (isset($search['per_page']) && !empty($search['per_page'])) {
                $pagination_no = $search['per_page'];
                //  return $pagination_no;
            }
            if ($header != null) {
                $category = Category::where('status', 1)
                    ->orderByRaw("CAST(`order` AS UNSIGNED) ASC")
                    // ->whereNotIn('name', ['Personalization']) 
                    ->leftjoin('user_feed', function ($join) use ($header) {
                        $join->on('user_feed.category_id', '=', 'category.id')->where('user_feed.user_id', $header);
                    })
                    ->select('category.*', 'user_feed.id as user_feed_id')

                    ->orderBy('order', 'ASC')->get();
                $sliders = Blog::where('status', 1)
                    ->where('is_slider', '1')
                    ->orderBy('order', 'ASC')->get();
            } else {
                $category = Category::where('status', 1)
                    ->orderByRaw("CAST(`order` AS UNSIGNED) ASC")
                    ->whereNotIn('name', ['Personalization'])
                    ->orderBy('order', 'ASC')->get();
                $sliders = Blog::where('status', 1)
                    ->where('is_slider', '1')
                    ->orderBy('order', 'ASC')->get();
            }
            $final_arr = array();
            $i = 0;


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
            $final_arr['category'] = array();
            foreach ($category as $row) {
                $blog_arr = array();
                $getBlogs = BlogCategory::where('category_id', $row->id)->get();
                if (count($getBlogs)) {
                    foreach ($getBlogs as $getBlogs_data) {
                        array_push($blog_arr, $getBlogs_data->blog_id);
                    }
                }
                $catTranslate = CategoryTranslation::where('category_id', $row->id)->where('language_code', $this->language)->first();
                if ($catTranslate) {
                    $row->name = $catTranslate->name;
                }

                if ($row->image != '') {
                    $row->image = url('upload/category/original/' . $row->image);
                } else {
                    $row->image = url('upload/category/default.png');
                }
                // $row->blog = array();

                $blog = Blog::where('status', 1)
                    ->whereIn('id', $blog_arr)
                    ->where('schedule_date', "<=", date("Y-m-d H:i:s"))
                    ->with('blog_category')
                    ->when(!empty($readids), function ($query) use ($readids) {
                        $query->whereNotIn('id', $readids);
                    })
                    ->orderBy('created_at', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->paginate($pagination_no)
                    ->appends('per_page', $pagination_no);

                if ($blog) {
                    foreach ($blog as $detail) {

                        $blogTranslate = BlogTranslation::where('blog_id', $detail->id)->where('language_code', $this->language)->first();
                        if ($blogTranslate) {
                            $detail->title = $blogTranslate->title;
                            $detail->tags = $blogTranslate->tags;
                            $detail->description = $blogTranslate->description;
                            $detail->seo_title = $blogTranslate->seo_title;
                            $detail->seo_keyword = $blogTranslate->seo_keyword;
                            $detail->seo_tag = $blogTranslate->seo_tag;
                            $detail->seo_description = $blogTranslate->seo_description;
                        }
                        $detail->trimed_description = strip_tags($detail->description);
                        $detail->trimed_description =  str_replace("&nbsp;", '', $detail->trimed_description);
                        $detail->trimed_description =  str_replace("&#39;", "'", $detail->trimed_description);
                        $detail->created_at = date("d M Y h:i a", strtotime($detail->created_at));
                        if ($detail->thumb_image != '') {
                            $detail->thumb_image = url('upload/blog/thumb/360/' . $detail->thumb_image);
                        } else {
                            $detail->thumb_image = url('upload/blog/thumb/default.png');
                        }
                        $check_image = BlogImages::where('blog_id', $detail->id)->pluck('image');
                        $blog_image = array();
                        if (count($check_image)) {
                            foreach ($check_image as $value) {
                                $value = url('upload/blog/banner/800/' . $value);
                                array_push($blog_image, $value);
                            }
                            $detail->banner_image = $blog_image;
                        } else {
                            $blog_image[0] = url('upload/author/default.png');
                            $detail->banner_image =  $blog_image;
                        }
                        if ($header != null) {
                            $vote = Vote::where('user_id', $header)->where('blog_id', $detail->id)->first();
                            if ($vote) {
                                $detail->is_vote = 1;
                            } else {
                                $detail->is_vote = 0;
                            }
                            $bookmarked = BookMarkPost::where('user_id', $header)->where('blog_id', $detail->id)->first();
                            if ($bookmarked) {
                                $detail->is_bookmark = 1;
                            } else {
                                $detail->is_bookmark = 0;
                            }
                        } else {
                            $detail->is_vote = 0;
                            $detail->is_bookmark = 0;
                        }
                        $total_votes = Vote::where('blog_id', $detail->id)->count();
                        $yes_votes = Vote::where('blog_id', $detail->id)->where('vote', 1)->count();
                        $no_votes = Vote::where('blog_id', $detail->id)->where('vote', 0)->count();
                        $detail->view_count = BlogViewCount::where('blog_id', $detail->id)->count();
                        static $isStoryViewVisible = null;
                        if ($isStoryViewVisible === null) {
                            $isStoryViewVisible = \App\Models\SiteContent::where('key', 'story_view_visibility')->value('value') ?? '1';
                        }
                        $detail->story_view_count = $isStoryViewVisible == '1' ? \App\Models\StoryViewCount::where('story_id', $detail->id)->count() : 0;
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
                        $detail->type = "post";
                        $detail->yes_percent = round($yes_percent);
                        $detail->no_percent = round($no_percent);
                        $author = Author::where('status', 1)->where('id', $detail->author_id)->first();
                        if ($author) {
                            $detail->author_name = $author->name;
                            if ($author->image != '') {
                                $detail->image = url('upload/author/original/' . $author->image);
                            } else {
                                $detail->image = url('upload/author/default.png');
                            }
                        } else {
                            $detail->author_name = "";
                            $detail->image = url('upload/author/default.png');
                        }
                        $detail->media = BlogImages::where('blog_id', $detail->id)->get();
                        if (count($detail->media)) {
                            foreach ($detail->media as $media) {
                                if ($media->type == 'image') {
                                    $media->image = url('upload/blog/banner/original/' . $media->image);
                                }
                            }
                        }
                        $detail->category_name = $row->name;
                        $detail->color = $row->color;
                        $detail->time = $detail->time . " min";
                        $detail->create_date = date("d M Y // h:i a", strtotime($detail->schedule_date));
                    }
                    $row['blog'] = $blog;
                    if ($row->user_feed_id != null) {
                        $row['isMyFeed'] = true;
                    } else {
                        $row['isMyFeed'] = false;
                    }

                    $result = [];
                    $adCount = count($ads_list);
                    $adIndex = 0;

                    $BlogCount = 0;
                    foreach ($blog as $key => $item) {
                        if (count($ads_list)) {
                            if ($BlogCount == $ads_list[$adIndex]['frequency']) {
                                $ads_list[$adIndex]['type'] = "ads";
                                $result[] = $ads_list[$adIndex];
                                $adIndex = ($adIndex + 1) % $adCount;
                                $BlogCount = 0;
                            }
                            $result[] = $item;
                            $BlogCount++;
                        } else {
                            $result[] = $item;
                        }
                    }

                    $dataCheck = $this->arrayPaginator($result, $request);
                    $row->index = $i;
                    array_push($final_arr['category'], $row);
                    $i++;
                }
            }
            // return $final_arr;
            return $this->sendResponse($final_arr, __('message_alerts.category_list'));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }
    }


    public function categorylist(Request $request, $id)
    {


        try {
            $header = $request->header('userData');
            if ($header != null) {
                $user = User::where('id', $header)->first();
                if (!isset($user->id)) {
                    return $this->sendError('Your account has been deleted by the Admin', 401);
                }
            }

            $setting = SiteContent::where('key', 'personal_category_auto_remove')->first();
            $pagination_no = 100;
            $readids = json_decode($request->input('blog_ids'));
            if (isset($request['per_page']) && !empty($request['per_page'])) {
                $pagination_no = $request['per_page'];
                //  return $pagination_no;
            }
            if ($header != null) {
                $category = Category::where('category.id', '=', $id)->where('status', 1)
                    ->orderByRaw("CAST(`order` AS UNSIGNED) ASC")
                    // ->whereNotIn('name', ['Personalization']) 
                    ->leftjoin('user_feed', function ($join) use ($header) {
                        $join->on('user_feed.category_id', '=', 'category.id')->where('user_feed.user_id', $header);
                    })
                    ->select('category.*', 'user_feed.id as user_feed_id')

                    ->orderBy('order', 'ASC')->get();
            } else {
                $category = Category::where('id', '=', $id)->where('status', 1)
                    ->orderByRaw("CAST(`order` AS UNSIGNED) ASC")
                    // ->whereNotIn('name', ['Personalization'])
                    ->orderBy('order', 'ASC')->get();
            }
            $viewedBlogIds = [];
            if (isset($request['device_id']) && !empty($request['device_id']) && $header != null) {
                $viewedBlogIds = BlogViewCount::where('device_id', $request['device_id'])
                    ->where('user_id', $header)->pluck('blog_id')->toArray();
            } else if (isset($request['device_id']) && !empty($request['device_id'])) {
                $viewedBlogIds = BlogViewCount::where('device_id', $request['device_id'])
                    ->pluck('blog_id')->toArray();
            } else if ($header != null) {
                $viewedBlogIds = BlogViewCount::where('user_id', $header)
                    ->pluck('blog_id')->toArray();
            }

            $final_arr = array();
            $i = 0;
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
            foreach ($category as $row) {
                $blog_arr = array();
                $getBlogs = BlogCategory::where('category_id', $row->id)->get();
                if (count($getBlogs)) {
                    foreach ($getBlogs as $getBlogs_data) {
                        array_push($blog_arr, $getBlogs_data->blog_id);
                    }
                }
                $catTranslate = CategoryTranslation::where('category_id', $row->id)->where('language_code', $this->language)->first();
                if ($catTranslate) {
                    $row->name = $catTranslate->name;
                    $cat_name = $catTranslate->name;
                }
                if ($row->image != '') {
                    $row->image = url('upload/category/original/' . $row->image);
                } else {
                    $row->image = url('upload/category/default.png');
                }
                $row->blog = array();

                // $blog = Blog::where('status', 1)
                //     ->whereIn('id', $blog_arr)
                //     ->where('schedule_date', "<=", date("Y-m-d H:i:s"))
                //     ->with('blog_category')
                //     ->when(!empty($readids), function ($query) use ($readids) {
                //         $query->whereNotIn('id', $readids);
                //     })
                //     ->orderBy('created_at', 'DESC')
                //     ->paginate($pagination_no)
                //     ->appends('per_page', $pagination_no);
                $lat = $search['lat'] ?? null;
                $lng = $search['long'] ?? null;
                $radius = 50; // km
                $blog = Blog::select('blog.*')
                    ->where('status', 1)
                    ->where('is_location_radius', '0')
                    ->whereIn('id', $blog_arr)
                    ->whereNotIn('id', $viewedBlogIds)
                    ->where('schedule_date', '<=', now())
                    ->with('blog_category');
                if ($row->slug == 'personal-news') {
                    $cutoffDate = Carbon::today()->subDays($setting->value);
                    $blog = $blog->where('schedule_date', '>', $cutoffDate);
                    // $blog = $blog->where('created_at', '<', Carbon::today()->subDays($setting->value));
                }
                if (isset($lat) && !empty($lat) && isset($lng) && !empty($lng)) {
                    $blog = $blog->when(!empty($readids), function ($query) use ($readids) {
                        $query->whereNotIn('id', $readids);
                    })
                        ->when($lat && $lng, function ($query) use ($lat, $lng, $radius) {
                            $query->selectRaw("
                            CASE
                                WHEN latitude IS NOT NULL AND longitude IS NOT NULL AND (
                                    6371 * acos(
                                        cos(radians(?)) * cos(radians(latitude)) *
                                        cos(radians(longitude) - radians(?)) +
                                        sin(radians(?)) * sin(radians(latitude))
                                    )
                                ) <= ?
                                THEN (
                                    6371 * acos(
                                        cos(radians(?)) * cos(radians(latitude)) *
                                        cos(radians(longitude) - radians(?)) +
                                        sin(radians(?)) * sin(radians(latitude))
                                    )
                                )
                                ELSE NULL
                            END AS distance", [$lat, $lng, $lat, $radius, $lat, $lng, $lat]);

                            $query->orderByRaw("
                            CASE
                                WHEN latitude IS NOT NULL AND longitude IS NOT NULL AND (
                                    6371 * acos(
                                        cos(radians(?)) * cos(radians(latitude)) *
                                        cos(radians(longitude) - radians(?)) +
                                        sin(radians(?)) * sin(radians(latitude))
                                    )
                                ) <= ? THEN 0 ELSE 1
                            END ASC", [$lat, $lng, $lat, $radius]);

                            $query->orderByRaw("distance ASC");
                        });
                }

                $blog = $blog->orderBy('schedule_date', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->paginate($pagination_no)
                    ->appends(['per_page' => $pagination_no]);

                if ($blog) {
                    foreach ($blog as $detail) {

                        $blogTranslate = BlogTranslation::where('blog_id', $detail->id)->where('language_code', $this->language)->first();
                        if ($blogTranslate) {
                            $detail->title = $blogTranslate->title;
                            $detail->tags = $blogTranslate->tags;
                            $detail->description = $blogTranslate->description;
                            $detail->seo_title = $blogTranslate->seo_title;
                            $detail->seo_keyword = $blogTranslate->seo_keyword;
                            $detail->seo_tag = $blogTranslate->seo_tag;
                            $detail->seo_description = $blogTranslate->seo_description;
                        }
                        // dd($detail->blog_category);
                        $detail->blog_category[0]->category->name = $cat_name;
                        $detail->trimed_description = strip_tags($detail->description);
                        $detail->trimed_description =  str_replace("&nbsp;", '', $detail->trimed_description);
                        $detail->trimed_description =  str_replace("&#39;", "'", $detail->trimed_description);
                        $detail->created_at = date("d M Y h:i a", strtotime($detail->created_at));
                        if ($detail->thumb_image != '') {
                            $detail->thumb_image = url('upload/blog/thumb/360/' . $detail->thumb_image);
                        } else {
                            $detail->thumb_image = url('upload/blog/thumb/default.png');
                        }
                        $check_image = BlogImages::where('blog_id', $detail->id)->pluck('image');
                        $blog_image = array();
                        if (count($check_image)) {
                            foreach ($check_image as $value) {
                                $value = url('upload/blog/banner/800/' . $value);
                                array_push($blog_image, $value);
                            }
                            $detail->banner_image = $blog_image;
                        } else {
                            $blog_image[0] = url('upload/author/default.png');
                            $detail->banner_image =  $blog_image;
                        }
                        if ($header != null) {
                            $vote = Vote::where('user_id', $header)->where('blog_id', $detail->id)->first();
                            if ($vote) {
                                $detail->is_vote = 1;
                            } else {
                                $detail->is_vote = 0;
                            }
                            $bookmarked = BookMarkPost::where('user_id', $header)->where('blog_id', $detail->id)->first();
                            if ($bookmarked) {
                                $detail->is_bookmark = 1;
                            } else {
                                $detail->is_bookmark = 0;
                            }
                        } else {
                            $detail->is_vote = 0;
                            $detail->is_bookmark = 0;
                        }
                        $total_votes = Vote::where('blog_id', $detail->id)->count();
                        $yes_votes = Vote::where('blog_id', $detail->id)->where('vote', 1)->count();
                        $no_votes = Vote::where('blog_id', $detail->id)->where('vote', 0)->count();
                        $detail->view_count = BlogViewCount::where('blog_id', $detail->id)->count();
                        static $isStoryViewVisible = null;
                        if ($isStoryViewVisible === null) {
                            $isStoryViewVisible = \App\Models\SiteContent::where('key', 'story_view_visibility')->value('value') ?? '1';
                        }
                        $detail->story_view_count = $isStoryViewVisible == '1' ? \App\Models\StoryViewCount::where('story_id', $detail->id)->count() : 0;
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
                        $detail->type = "post";
                        $detail->yes_percent = round($yes_percent);
                        $detail->no_percent = round($no_percent);
                        $author = User::where('id', $detail->created_by)->first();
                        if ($author) {
                            $detail->author_name = $author->name;
                            if ($author->photo != null || $author->photo != '') {
                                $detail->image = $author->photo;
                            } else {
                                $detail->image = url('upload/user/default.png');
                            }
                        } else {
                            $detail->author_name = "";
                            $detail->image = url('upload/user/default.png');
                        }
                        $detail->media = BlogImages::where('blog_id', $detail->id)->get();
                        if (count($detail->media)) {
                            foreach ($detail->media as $media) {
                                if ($media->type == 'image') {
                                    $media->image = url('upload/blog/banner/original/' . $media->image);
                                }
                            }
                        }
                        $detail->category_name = $cat_name;
                        $detail->color = $row->color;
                        $detail->time = $detail->time . " min";
                        $detail->create_date = date("d M Y // h:i a", strtotime($detail->schedule_date));
                    }
                    $row['blog'] = $blog;
                    if ($row->user_feed_id != null) {
                        $row['isMyFeed'] = true;
                    } else {
                        $row['isMyFeed'] = false;
                    }

                    $result = [];
                    $adCount = count($ads_list);
                    $adIndex = 0;

                    $BlogCount = 0;
                    foreach ($blog as $key => $item) {
                        if (count($ads_list)) {
                            if ($BlogCount == $ads_list[$adIndex]['frequency']) {
                                $ads_list[$adIndex]['type'] = "ads";
                                $result[] = $ads_list[$adIndex];
                                $adIndex = ($adIndex + 1) % $adCount;
                                $BlogCount = 0;
                            }
                            $result[] = $item;
                            $BlogCount++;
                        } else {
                            $result[] = $item;
                        }
                    }

                    $dataCheck = $this->arrayPaginator($result, $request);
                    $row->index = $i;
                    array_push($final_arr, $row);
                    $i++;
                }
            }
            return $this->sendResponse($final_arr, __('message_alerts.category_list'));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }
    }
    // public function list(Request $request)
    // {
    //     try {
    //         $header = $request->header('userData');
    //         $pagination_no = 10;
    //         $readids = json_decode($request->input('blog_ids'));

    //         if (isset($request->per_page) && !empty($request->per_page)) {
    //             $pagination_no = $request->per_page;
    //         }

    //         // Get the categories based on the user header
    //         if ($header != null) {
    //             $category = Category::where('status', 1)
    //                 ->orderByRaw("CAST(`order` AS UNSIGNED) ASC")
    //                 ->leftjoin('user_feed', function ($join) use ($header) {
    //                     $join->on('user_feed.category_id', '=', 'category.id')->where('user_feed.user_id', $header);
    //                 })
    //                 ->select('category.*', 'user_feed.id as user_feed_id')
    //                 ->orderBy('order', 'ASC')->get();
    //         } else {
    //             $category = Category::where('status', 1)
    //                 ->orderByRaw("CAST(`order` AS UNSIGNED) ASC")
    //                 ->whereNotIn('name', ['Personalization'])
    //                 ->orderBy('order', 'ASC')->get();
    //         }

    //         $final_arr = array();
    //         $i = 0;
    //         $ads_list = Ads::where('status', 1)
    //             ->where('end_date', ">=", date('Y-m-d'))
    //             ->withCount('click')
    //             ->withCount('view')
    //             ->with('media')
    //             ->orderBy('order', 'ASC')
    //             ->get();

    //         // Process ads media URLs
    //         if (count($ads_list)) {
    //             foreach ($ads_list as $ads_list_data) {
    //                 foreach ($ads_list_data->media as $media_list) {
    //                     if ($media_list->type == 'image') {
    //                         $media_list->file = url('storage/ads/' . $media_list->file);
    //                     } else if ($media_list->type == 'video') {
    //                         $media_list->file = url('storage/ads/videos/' . $media_list->file);
    //                     }
    //                 }
    //             }
    //         }

    //         // Process categories
    //         foreach ($category as $row) {
    //             $blog_arr = array();
    //             $getBlogs = BlogCategory::where('category_id', $row->id)->get();
    //             if (count($getBlogs)) {
    //                 foreach ($getBlogs as $getBlogs_data) {
    //                     array_push($blog_arr, $getBlogs_data->blog_id);
    //                 }
    //             }

    //             $catTranslate = CategoryTranslation::where('category_id', $row->id)
    //                 ->where('language_code', $this->language)
    //                 ->first();
    //             if ($catTranslate) {
    //                 $row->name = $catTranslate->name;
    //             }

    //             if ($row->image != '') {
    //                 $row->image = url('upload/category/original/' . $row->image);
    //             } else {
    //                 $row->image = url('upload/category/default.png');
    //             }
    //             $row->blog = array();

    //             // Fetch blogs for the current category
    //             $blog = Blog::where('status', 1)
    //                 ->whereIn('id', $blog_arr)
    //                 ->where('schedule_date', "<=", date("Y-m-d H:i:s"))
    //                 ->with('blog_category')

    //                 ->orderBy('schedule_date', 'DESC')
    //                 ->paginate($pagination_no)
    //                 ->appends('per_page', $pagination_no);

    //             //  $blog = Blog::where('status', 1)
    //             //  ->where('schedule_date', "<=", date("Y-m-d H:i:s"))
    //             //  ->with('blog_category')
    //             //  ->orderBy('schedule_date', 'DESC');
    //             // if (!empty($blog_arr)) {
    //             //     $blog->whereIn('id', $blog_arr);
    //             // }
    //             // if (!empty($readids)) {
    //             //     $blog->whereNotIn('id', $readids);
    //             // }
    //             // $blog = $blog->paginate($pagination_no)
    //             //   ->appends('per_page', $pagination_no);

    //             // Process blogs
    //             if ($blog) {
    //                 foreach ($blog as $detail) {
    //                     $blogTranslate = BlogTranslation::where('blog_id', $detail->id)
    //                         ->where('language_code', $this->language)
    //                         ->first();
    //                     if ($blogTranslate) {
    //                         $detail->title = $blogTranslate->title;
    //                         $detail->tags = $blogTranslate->tags;
    //                         $detail->description = $blogTranslate->description;
    //                         $detail->seo_title = $blogTranslate->seo_title;
    //                         $detail->seo_keyword = $blogTranslate->seo_keyword;
    //                         $detail->seo_tag = $blogTranslate->seo_tag;
    //                         $detail->seo_description = $blogTranslate->seo_description;
    //                     }
    //                     $detail->trimed_description = strip_tags($detail->description);
    //                     $detail->trimed_description = str_replace("&nbsp;", '', $detail->trimed_description);
    //                     $detail->trimed_description = str_replace("&#39;", "'", $detail->trimed_description);
    //                     $detail->created_at = date("d M Y h:i a", strtotime($detail->created_at));
    //                     if ($detail->thumb_image != '') {
    //                         $detail->thumb_image = url('upload/blog/thumb/360/' . $detail->thumb_image);
    //                     } else {
    //                         $detail->thumb_image = url('upload/blog/thumb/default.png');
    //                     }
    //                     $check_image = BlogImages::where('blog_id', $detail->id)->pluck('image');
    //                     $blog_image = array();
    //                     if (count($check_image)) {
    //                         foreach ($check_image as $value) {
    //                             $value = url('upload/blog/banner/800/' . $value);
    //                             array_push($blog_image, $value);
    //                         }
    //                         $detail->banner_image = $blog_image;
    //                     } else {
    //                         $blog_image[0] = url('upload/author/default.png');
    //                         $detail->banner_image = $blog_image;
    //                     }
    //                     if ($header != null) {
    //                         $vote = Vote::where('user_id', $header)
    //                             ->where('blog_id', $detail->id)
    //                             ->first();
    //                         $detail->is_vote = $vote ? 1 : 0;

    //                         $bookmarked = BookMarkPost::where('user_id', $header)
    //                             ->where('blog_id', $detail->id)
    //                             ->first();
    //                         $detail->is_bookmark = $bookmarked ? 1 : 0;
    //                     } else {
    //                         $detail->is_vote = 0;
    //                         $detail->is_bookmark = 0;
    //                     }
    //                     $total_votes = Vote::where('blog_id', $detail->id)->count();
    //                     $yes_votes = Vote::where('blog_id', $detail->id)->where('vote', 1)->count();
    //                     $no_votes = Vote::where('blog_id', $detail->id)->where('vote', 0)->count();
    //                     $detail->view_count = BlogViewCount::where('blog_id', $detail->id)->count();
    //                     $yes_percent = $yes_votes != 0 ? ($yes_votes / $total_votes) * 100 : 0;
    //                     $no_percent = $no_votes != 0 ? ($no_votes / $total_votes) * 100 : 0;
    //                     $detail->type = "post";
    //                     $detail->yes_percent = round($yes_percent);
    //                     $detail->no_percent = round($no_percent);

    //                     $author = Author::where('status', 1)
    //                         ->where('id', $detail->author_id)
    //                         ->first();
    //                     if ($author) {
    //                         $detail->author_name = $author->name;
    //                         if ($author->image != '') {
    //                             $detail->image = url('upload/author/original/' . $author->image);
    //                         } else {
    //                             $detail->image = url('upload/author/default.png');
    //                         }
    //                     } else {
    //                         $detail->author_name = "";
    //                         $detail->image = url('upload/author/default.png');
    //                     }
    //                     $detail->media = BlogImages::where('blog_id', $detail->id)->get();
    //                     if (count($detail->media)) {
    //                         foreach ($detail->media as $media) {
    //                             if ($media->type == 'image') {
    //                                 $media->image = url('upload/blog/banner/original/' . $media->image);
    //                             }
    //                         }
    //                     }
    //                     $detail->category_name = $row->name;
    //                     $detail->color = $row->color;
    //                     $detail->time = $detail->time . " min";
    //                     $detail->create_date = date("d M Y // h:i a", strtotime($detail->schedule_date));
    //                 }

    //                 // Insert ads between blogs
    //                 $result = [];
    //                 $adCount = count($ads_list);
    //                 $adIndex = 0;
    //                 $BlogCount = 0;
    //                 foreach ($blog as $key => $item) {
    //                     if (count($ads_list)) {
    //                         if ($BlogCount == $ads_list[$adIndex]['frequency']) {
    //                             $ads_list[$adIndex]['type'] = "ads";
    //                             $result[] = $ads_list[$adIndex];
    //                             $adIndex = ($adIndex + 1) % $adCount;
    //                             $BlogCount = 0;
    //                         }
    //                         $result[] = $item;
    //                         $BlogCount++;
    //                     } else {
    //                         $result[] = $item;
    //                     }
    //                 }

    //                 $dataCheck = $this->arrayPaginator($result, $request);
    //                 $row['blog'] = $dataCheck;
    //                 $row['index'] = $i;
    //                 if ($row->user_feed_id != null) {
    //                     $row['isMyFeed'] = true;
    //                 } else {
    //                     $row['isMyFeed'] = false;
    //                 }
    //                 array_push($final_arr, $row);
    //                 $i++;
    //             }
    //         }

    //         // Fetch additional categories if needed
    //         $allCategoryIds = Category::where('status', 1)->pluck('id')->toArray();
    //         $existingCategoryIds = $category->pluck('id')->toArray();
    //         $missingCategoryIds = array_diff($allCategoryIds, $existingCategoryIds);

    //         $additionalCategories = Category::whereIn('id', $missingCategoryIds)
    //             ->orderByRaw("CAST(`order` AS UNSIGNED) ASC")
    //             ->get();

    //         // Process additional categories similarly
    //         foreach ($additionalCategories as $row) {
    //             // Process each additional category similarly to the above logic
    //             // Add to $final_arr if needed
    //         }

    //         return $this->sendResponse($final_arr, __('message_alerts.category_list'));
    //     } catch (\Exception $e) {
    //         return $this->sendError($e->getMessage(), 401);
    //     }
    // }

    // public function listNews(Request $request, $id)
    // {
    //     try {
    //         // $header = $request->header('userData');
    //         $pagination_no = 10;
    //         if (isset($search['per_page']) && !empty($search['per_page'])) {
    //             $pagination_no = $search['per_page'];
    //         }
    //         $blog = Blog::where('category_id', '=', $id)->where('status', 1)->where('schedule_date', "<=", date("Y-m-d H:i:s"))
    //             ->with('blog_category')->orderBy('schedule_date', 'DESC')->paginate($pagination_no)->appends('per_page', $pagination_no);
    //         return $this->sendResponse($blog, __('message_alerts.category_list'));
    //     } catch (\Throwable $e) {
    //         return $this->sendError($e->getMessage(), 401);
    //     }
    // }
    public function listNews(Request $request, $id)
    {
        try {
            // $header = $request->header('userData');
            $pagination_no = 10;
            $i = 0;
            $result = [];
            if (isset($search['per_page']) && !empty($search['per_page'])) {
                $pagination_no = $search['per_page'];
            }
            $blog = Blog::where('category_id', '=', $id)->where('status', 1)
                ->get();

            return $this->sendResponse($blog, __('message_alerts.category_list'));
        } catch (\Throwable $e) {
            return $this->sendError($e->getMessage(), 401);
        }
    }

    public function prefernceData(Request $request)
    {
        try {
            $header = $request->header('userData');
            $pagination_no = 10;
            if (isset($search['per_page']) && !empty($search['per_page'])) {
                $pagination_no = $search['per_page'];
            }
            if ($header != null) {
                $category = Category::where('status', 1)
                    ->whereNotIn('name', ['Personalization'])
                    ->leftjoin('user_feed', function ($join) use ($header) {
                        $join->on('user_feed.category_id', '=', 'category.id')->where('user_feed.user_id', $header);
                    })
                    ->select('category.*', 'user_feed.id as user_feed_id')

                    ->orderBy('order', 'ASC')->get();
            } else {
                $category = Category::where('status', 1)
                    ->whereNotIn('name', ['Personalization'])
                    ->orderBy('order', 'ASC')->get();
            }

            $final_arr = array();
            $i = 0;
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
            foreach ($category as $row) {
                $blog_arr = array();
                $getBlogs = BlogCategory::where('category_id', $row->id)->get();
                if (count($getBlogs)) {
                    foreach ($getBlogs as $getBlogs_data) {
                        array_push($blog_arr, $getBlogs_data->blog_id);
                    }
                }
                $catTranslate = CategoryTranslation::where('category_id', $row->id)->where('language_code', $this->language)->first();
                if ($catTranslate) {
                    $row->name = $catTranslate->name;
                }

                if ($row->image != '') {
                    $row->image = url('upload/category/original/' . $row->image);
                } else {
                    $row->image = url('upload/category/default.png');
                }
                $row->blog = array();
                $blog = Blog::where('status', 1)->whereIn('id', $blog_arr)->where('schedule_date', "<=", date("Y-m-d H:i:s"))->with('blog_category')->orderBy('schedule_date', 'DESC')->orderBy('id', 'DESC')->paginate($pagination_no)->appends('per_page', $pagination_no);
                //
                if ($blog) {
                    foreach ($blog as $detail) {

                        $blogTranslate = BlogTranslation::where('blog_id', $detail->id)->where('language_code', $this->language)->first();
                        if ($blogTranslate) {
                            $detail->title = $blogTranslate->title;
                            $detail->tags = $blogTranslate->tags;
                            $detail->description = $blogTranslate->description;
                            $detail->seo_title = $blogTranslate->seo_title;
                            $detail->seo_keyword = $blogTranslate->seo_keyword;
                            $detail->seo_tag = $blogTranslate->seo_tag;
                            $detail->seo_description = $blogTranslate->seo_description;
                        }

                        // if(str_word_count($detail->description)>62){
                        //     $detail->description = substr($detail->description,0,420);
                        //     $detail->description = $detail->description.".....";
                        // }
                        $detail->trimed_description = strip_tags($detail->description);
                        $detail->trimed_description =  str_replace("&nbsp;", '', $detail->trimed_description);
                        $detail->trimed_description =  str_replace("&#39;", "'", $detail->trimed_description);
                        $detail->created_at = date("d M Y h:i a", strtotime($detail->created_at));
                        if ($detail->thumb_image != '') {
                            $detail->thumb_image = url('upload/blog/thumb/360/' . $detail->thumb_image);
                        } else {
                            $detail->thumb_image = url('upload/blog/thumb/default.png');
                        }
                        $check_image = BlogImages::where('blog_id', $detail->id)->pluck('image');
                        $blog_image = array();
                        if (count($check_image)) {
                            foreach ($check_image as $value) {
                                $value = url('upload/blog/banner/800/' . $value);
                                array_push($blog_image, $value);
                            }
                            $detail->banner_image = $blog_image;
                        } else {
                            $blog_image[0] = url('upload/author/default.png');
                            $detail->banner_image =  $blog_image;
                        }
                        if ($header != null) {
                            $vote = Vote::where('user_id', $header)->where('blog_id', $detail->id)->first();
                            if ($vote) {
                                $detail->is_vote = 1;
                            } else {
                                $detail->is_vote = 0;
                            }
                            $bookmarked = BookMarkPost::where('user_id', $header)->where('blog_id', $detail->id)->first();
                            if ($bookmarked) {
                                $detail->is_bookmark = 1;
                            } else {
                                $detail->is_bookmark = 0;
                            }
                        } else {
                            $detail->is_vote = 0;
                            $detail->is_bookmark = 0;
                        }
                        $total_votes = Vote::where('blog_id', $detail->id)->count();
                        $yes_votes = Vote::where('blog_id', $detail->id)->where('vote', 1)->count();
                        $no_votes = Vote::where('blog_id', $detail->id)->where('vote', 0)->count();
                        $detail->view_count = BlogViewCount::where('blog_id', $detail->id)->count();
                        static $isStoryViewVisible = null;
                        if ($isStoryViewVisible === null) {
                            $isStoryViewVisible = \App\Models\SiteContent::where('key', 'story_view_visibility')->value('value') ?? '1';
                        }
                        $detail->story_view_count = $isStoryViewVisible == '1' ? \App\Models\StoryViewCount::where('story_id', $detail->id)->count() : 0;
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
                        $detail->type = "post";
                        $detail->yes_percent = round($yes_percent);
                        $detail->no_percent = round($no_percent);
                        $author = Author::where('status', 1)->where('id', $detail->author_id)->first();
                        if ($author) {
                            $detail->author_name = $author->name;
                            if ($author->image != '') {
                                $detail->image = url('upload/author/original/' . $author->image);
                            } else {
                                $detail->image = url('upload/author/default.png');
                            }
                        } else {
                            $detail->author_name = "";
                            $detail->image = url('upload/author/default.png');
                        }
                        $detail->media = BlogImages::where('blog_id', $detail->id)->get();
                        if (count($detail->media)) {
                            foreach ($detail->media as $media) {
                                if ($media->type == 'image') {
                                    $media->image = url('upload/blog/banner/original/' . $media->image);
                                }
                            }
                        }
                        $detail->category_name = $row->name;
                        $detail->color = $row->color;
                        $detail->time = $detail->time . " min";
                        $detail->create_date = date("d M Y // h:i a", strtotime($detail->schedule_date));
                    }
                    $row['blog'] = $blog;
                    if ($row->user_feed_id != null) {
                        $row['isMyFeed'] = true;
                    } else {
                        $row['isMyFeed'] = false;
                    }

                    // if(count($blog)>0){
                    //     $row->index = $i;
                    //     array_push($final_arr,$row);
                    //     $i++;
                    // }
                    $result = [];
                    $adCount = count($ads_list);
                    $adIndex = 0;

                    $BlogCount = 0;
                    foreach ($blog as $key => $item) {
                        if (count($ads_list)) {
                            if ($BlogCount == $ads_list[$adIndex]['frequency']) {
                                $ads_list[$adIndex]['type'] = "ads";
                                $result[] = $ads_list[$adIndex];
                                $adIndex = ($adIndex + 1) % $adCount;
                                $BlogCount = 0;
                            }
                            $result[] = $item;
                            $BlogCount++;
                        } else {
                            $result[] = $item;
                        }
                    }

                    $dataCheck = $this->arrayPaginator($result, $request);
                    $row['blog'] = $dataCheck;
                    if (count($dataCheck) > 0) {
                        $row->index = $i;
                        array_push($final_arr, $row);
                        $i++;
                    }
                }
            }
            return $this->sendResponse($final_arr, __('message_alerts.category_list'));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
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
