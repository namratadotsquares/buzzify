<?php



namespace App\Http\Controllers;



use App\Models\Ads;

use App\Models\Ads_images;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

use App\Models\Blog;

use App\Models\Category;

use App\Models\BlogCategory;

use App\Models\Author;
use DB;
use App\Models\SiteContent;
use App\Models\Social;
use App\Models\StoreViewed;
use App\Models\SearchLog;

use App\Models\BookMarkPost;

use App\Models\BlogViewCount;

use App\Models\BlogImages;

use App\Models\Vote;

use App\Models\User;

use App\Models\BlogTranslation;

use App\Models\CategoryTranslation;
use Illuminate\Support\Facades\Log;


use Carbon\Carbon;

use Illuminate\Pagination\LengthAwarePaginator;



class BlogAPIController extends Controller
{



    private $language;

    public function __construct(Request $request)
    {

        $this->request = $request;

        $newUserId = $this->request->header('userData');



        $user = User::find($newUserId);

        if ($user && $user->lang_code != '') {

            $this->language = ($request->header('lang-code') && $request->header('lang-code') != '' ? $request->header('lang-code') : $user->lang_code);
        } else {

            $this->language = ($request->header('lang-code') && $request->header('lang-code') != '' ? $request->header('lang-code') : setting('preferred_site_language'));
        }
    }





    /**

     * Show 7 Blogs.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function list(Request $request)
    {
        try {
            $header = $request->header('userData');
            if ($header != null) {
                $user = User::where('id', $header)->first();
                if (!isset($user->id)) {
                    return $this->sendError('Your account has been deleted by the Admin', 401);
                }
            }
            $pagination_no = 20;
            $readids = json_decode($request->input('blog_ids'));
            if (isset($search['per_page']) && !empty($search['per_page'])) {
                $pagination_no = $search['per_page'];
            }
            $blog_image = array();
            $final_blog = array();
            $blog = Blog::where('status', 1)
                ->where('is_featured', 1)
                ->where('schedule_date', '<=', date("Y-m-d H:i:s"))
                ->whereDoesntHave('blog_category', function ($query) {
                    $query->whereHas('category', function ($query) {
                        $query->where('name', 'Personalization');
                    });
                })
                ->with([
                    'blog_category' => function ($query) {
                        $query->whereHas('category', function ($query) {
                            $query->where('status', 1);
                        });
                    }
                ])
                ->when(!empty($readids), function ($query) use ($readids) {
                    $query->whereNotIn('id', $readids);
                })
                ->orderBy('schedule_date', 'DESC')
                ->orderBy('updated_at', 'DESC')
                ->orderBy('id', 'DESC')
                ->paginate($pagination_no)
                ->appends('per_page', $pagination_no);




            $ads_list = Ads::where('status', 1)->where('end_date', ">=", date('Y-m-d'))->withCount('click')->withCount('view')
                ->with('media')->orderBy('order', 'ASC')->get();
            if (count($ads_list)) {
                foreach ($ads_list as $ads_list_data) {
                    foreach ($ads_list_data->media as $media_list) {
                        if ($media_list->type == 'image') {
                            $media_list->file = url('storage/ads/' . $media_list->file);
                        } else if ($media_list->type == 'video') {
                            $media_list->file = url('storage/ads/videos/' . $media_list->file);
                        }
                    }
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
                $row->type = "post";
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
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage() . $e->getLine(), 401);
        }
    }



    /**

     * Show All Blogs.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    // public function allBloglist(Request $request)
    // {
    //     try {
    //         $header = $request->header('userData');
    //         $pagination_no = 10;
    //         if (isset($search['per_page']) && !empty($search['per_page'])) {
    //             $pagination_no = $search['per_page'];
    //         }
    //         $blog_image = array();
    //         $final_blog = array();
    //         $blog = Blog::where('status', 1)->where('schedule_date', "<=", date("Y-m-d H:i:s"))
    //         // ->with('blog_category')
    //         ->whereDoesntHave('blog_category', function ($query) {
    //         $query->whereHas('category', function ($query) {
    //             $query->where('name', 'Personalization'); // Exclude blogs with category name "Personalization"
    //         });
    //         })
    //         ->with([
    //             'blog_category' => function ($query) {
    //                 $query->whereHas('category', function ($query) {
    //                     $query->where('status', 1);
    //                 });
    //             }
    //         ])
    //         ->orderBy('schedule_date', 'DESC')->get();
    //         //paginate($pagination_no)->appends('per_page', $pagination_no);
    //         $ads_list = Ads::where('status', 1)->where('end_date', ">=", date('Y-m-d'))->withCount('click')->withCount('view')->with('media')->orderBy('order', 'ASC')->get();
    //         if (count($ads_list)) {
    //             foreach ($ads_list as $ads_list_data) {
    //                 foreach ($ads_list_data->media as $media_list) {
    //                     if ($media_list->type == 'image') {
    //                         $media_list->file = url('storage/ads/' . $media_list->file);
    //                     }
    //                 }
    //             }
    //         }
    //         foreach ($blog as $row) {

    //             $flag = false;
    //             $blogTranslate = BlogTranslation::where('blog_id', $row->id)->where('language_code', $this->language)->first();
    //             if ($blogTranslate) {
    //                 $flag = true;
    //                 $row->title = $blogTranslate->title;
    //                 $row->tags = $blogTranslate->tags;
    //                 $row->description = $blogTranslate->description;
    //                 $row->seo_title = $blogTranslate->seo_title;
    //                 $row->seo_keyword = $blogTranslate->seo_keyword;
    //                 $row->seo_tag = $blogTranslate->seo_tag;
    //                 $row->seo_description = $blogTranslate->seo_description;
    //             }



    //             // if(str_word_count($row->description)>62){

    //             //     $row->description = substr($row->description,0,420);

    //             //     $row->description = $row->description.".....";

    //             // }

    //             $row->trimed_description = strip_tags($row->description);

    //             $row->trimed_description = str_replace("&nbsp;", '', $row->trimed_description);

    //             $row->trimed_description = str_replace("&#39;", "'", $row->trimed_description);

    //             $row->created_at = date("d M Y h:i a", strtotime($row->created_at));

    //             if ($row->thumb_image != '') {

    //                 $row->thumb_image = url('upload/blog/thumb/360/' . $row->thumb_image);

    //             } else {

    //                 $row->thumb_image = url('upload/blog/thumb/default.png');

    //             }

    //             $check_image = BlogImages::where('blog_id', $row->id)->pluck('image');

    //             $blog_image = array();

    //             if (count($check_image)) {

    //                 foreach ($check_image as $value) {

    //                     $value = url('upload/blog/banner/800/' . $value);

    //                     array_push($blog_image, $value);

    //                 }

    //                 $row->banner_image = $blog_image;

    //             } else {

    //                 $blog_image[0] = url('upload/author/default.png');

    //                 $row->banner_image = $blog_image;

    //             }

    //             if ($header != null) {

    //                 $vote = Vote::where('user_id', $header)->where('blog_id', $row->id)->first();

    //                 if ($vote) {

    //                     $row->is_vote = 1;

    //                 } else {

    //                     $row->is_vote = 0;

    //                 }

    //                 $bookmarked = BookMarkPost::where('user_id', $header)->where('blog_id', $row->id)->first();

    //                 if ($bookmarked) {

    //                     $row->is_bookmark = 1;

    //                 } else {

    //                     $row->is_bookmark = 0;

    //                 }

    //             } else {

    //                 $row->is_vote = 0;

    //                 $row->is_bookmark = 0;

    //             }

    //             $total_votes = Vote::where('blog_id', $row->id)->count();

    //             $yes_votes = Vote::where('blog_id', $row->id)->where('vote', 1)->count();

    //             $no_votes = Vote::where('blog_id', $row->id)->where('vote', 0)->count();

    //             $row->view_count = BlogViewCount::where('blog_id', $row->id)->count();

    //             if ($yes_votes != 0) {

    //                 $yes_percent = ($yes_votes / $total_votes) * 100;

    //             } else {

    //                 $yes_percent = 0;

    //             }

    //             if ($no_votes != 0) {

    //                 $no_percent = ($no_votes / $total_votes) * 100;

    //             } else {

    //                 $no_percent = 0;

    //             }

    //             // $row->type = "post";

    //             $row->yes_percent = round($yes_percent);

    //             $row->no_percent = round($no_percent);

    //             $author = Author::where('id', $row->author_id)->first();

    //             if ($author) {

    //                 $row->author_name = $author->name;

    //                 if ($author->image != null || $author->image != '') {

    //                     $row->image = url('upload/author/original/' . $author->image);

    //                 } else {

    //                     $row->image = url('upload/author/default.png');

    //                 }

    //             } else {

    //                 $row->author_name = "";

    //                 $row->image = url('upload/author/default.png');

    //             }

    //             $category = Category::where('id', $row->category_id)->first();

    //             if ($category) {



    //                 $catTranslate = CategoryTranslation::where('category_id', $category->id)->where('language_code', $this->language)->first();

    //                 if ($catTranslate) {

    //                     $category->name = $catTranslate->name;

    //                 }



    //                 $row->category_name = $category->name;

    //                 $row->color = $category->color;

    //             } else {

    //                 $row->category_name = "";

    //                 $row->color = "";

    //             }

    //             $row->time = $row->time . " min";

    //             $row->create_date = date("d M Y // h:i a", strtotime($row->schedule_date));

    //         }

    //         // return $this->sendResponse($blog, __('message_alerts.blog_list'));

    //         $result = [];

    //         $adCount = count($ads_list);

    //         $adIndex = 0;



    //         $BlogCount = 0;

    //         if (count($ads_list)) {

    //             foreach ($blog as $key => $item) {

    //                 if ($BlogCount == $ads_list[$adIndex]['frequency']) {

    //                     $ads_list[$adIndex]['type'] = "ads";

    //                     $result[] = $ads_list[$adIndex];

    //                     $adIndex = ($adIndex + 1) % $adCount;

    //                     $BlogCount = 0;

    //                 }

    //                 $result[] = $item;

    //                 $BlogCount++;

    //             }

    //         } else {

    //             foreach ($blog as $key => $item) {

    //                 $result[] = $item;

    //             }

    //         }





    //         $dataCheck = $this->arrayPaginator($result, $request);

    //         return $this->sendResponse($dataCheck, __('message_alerts.blog_list'));

    //     } catch (\Exception $e) {

    //         return $this->sendError($e->getMessage(), 401);

    //     }

    // }

    //old code
    public function allBloglist(Request $request)
    {
        try {
            $header = $request->header('userData');
            if ($header != null) {
                $user = User::where('id', $header)->first();
                if (!isset($user->id)) {
                    return $this->sendError('Your account has been deleted by the Admin', 401);
                }
            }
            $pagination_no = 10;

            if ($request->has('per_page') && !empty($request->per_page)) {
                $pagination_no = $request->per_page;
            }

            $excludeCategoryId = $request->input('category_id');
            $user_id = $request->input('user_id');
            $device_id = $request->input('device_id');
            $readids = json_decode($request->input('blog_ids'));

            $blog_image = array();
            $final_blog = array();

            // $blogQuery = Blog::where('status', 1)
            //     ->whereNotIn('id', $readids)
            //     ->where('schedule_date', "<=", date("Y-m-d H:i:s"))
            //     ->whereDoesntHave('blog_category', function ($query) use ($excludeCategoryId) {
            //         $query->whereHas('category', function ($query) use ($excludeCategoryId) {
            //             $query->where('id', $excludeCategoryId); // Exclude blogs with the provided category ID
            //         });
            //     })
            //     ->with([
            //         'blog_category' => function ($query) {
            //             $query->whereHas('category', function ($query) {
            //                 $query->where('status', 1);
            //             });
            //         }
            //     ])
            //     ->orderBy('schedule_date', 'DESC');
            $blogQuery = Blog::where('status', 1)
                // 		->whereNotIn('id', $readids)
                ->where('schedule_date', "<=", date("Y-m-d H:i:s"))
                ->whereDoesntHave('blog_category', function ($query) use ($excludeCategoryId) {
                    $query->whereHas('category', function ($query) use ($excludeCategoryId) {
                        $query->where('id', $excludeCategoryId); // Exclude blogs with the provided category ID
                    });
                })
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

                ->with([
                    'blog_category' => function ($query) {
                        $query->whereHas('category', function ($query) {
                            $query->where('status', 1);
                        });
                    }
                ])
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
                })
                ->orderBy('schedule_date', 'DESC')
                ->orderBy('updated_at', 'DESC')
                ->orderBy('id', 'DESC');


            if (!empty($readids)) {
                $blogQuery->whereNotIn('id', $readids);
            }

            $blog = $blogQuery->get();


            $ads_list = Ads::where('status', 1)
                ->where('end_date', ">=", date('Y-m-d'))
                ->withCount('click')
                ->withCount('view')
                ->with('media')
                ->orderBy('order', 'ASC')
                ->get();

            if (count($ads_list)) {
                foreach ($ads_list as $ads_list_data) {
                    foreach ($ads_list_data->media as $media_list) {
                        if ($media_list->type == 'image') {
                            $media_list->file = url('storage/ads/' . $media_list->file);
                        }
                    }
                }
            }

            foreach ($blog as $row) {
                $flag = false;
                $blogTranslate = BlogTranslation::where('blog_id', $row->id)->where('language_code', $this->language)->first();
                if ($blogTranslate) {
                    $flag = true;
                    $row->title = $blogTranslate->title;
                    $row->tags = $blogTranslate->tags;
                    $row->description = $blogTranslate->description;
                    $row->seo_title = $blogTranslate->seo_title;
                    $row->seo_keyword = $blogTranslate->seo_keyword;
                    $row->seo_tag = $blogTranslate->seo_tag;
                    $row->seo_description = $blogTranslate->seo_description;
                }

                $row->trimed_description = strip_tags($row->description);
                $row->trimed_description = str_replace("&nbsp;", '', $row->trimed_description);
                $row->trimed_description = str_replace("&#39;", "'", $row->trimed_description);
                $row->created_at = date("d M Y h:i a", strtotime($row->created_at));

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

                $total_votes = Vote::where('blog_id', $row->id)->count();
                $yes_votes = Vote::where('blog_id', $row->id)->where('vote', 1)->count();
                $no_votes = Vote::where('blog_id', $row->id)->where('vote', 0)->count();
                $row->view_count = BlogViewCount::where('blog_id', $row->id)->count();

                $yes_percent = $yes_votes != 0 ? ($yes_votes / $total_votes) * 100 : 0;
                $no_percent = $no_votes != 0 ? ($no_votes / $total_votes) * 100 : 0;

                $row->yes_percent = round($yes_percent);
                $row->no_percent = round($no_percent);

                $author = Author::where('id', $row->author_id)->first();
                if ($author) {
                    $row->author_name = $author->name;
                    $row->image = $author->image ? url('upload/author/original/' . $author->image) : url('upload/author/default.png');
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
                $row->create_date = date("d M Y // h:i a", strtotime($row->schedule_date));
            }

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
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }
    }


    //new code

    //     public function allBloglist(Request $request)
    // {
    //     try {
    //         $header = $request->header('userData');
    //         $pagination_no = 10;

    //         if ($request->has('per_page') && !empty($request->per_page)) {
    //             $pagination_no = $request->per_page;
    //         }

    //         $excludeCategoryId = $request->input('category_id');

    //         $blog_image = array();
    //         $final_blog = array();

    //         $blogQuery = Blog::where('status', 1)
    //             ->where('schedule_date', "<=", date("Y-m-d H:i:s"))
    //             ->whereDoesntHave('blog_category', function ($query) use ($excludeCategoryId) {
    //                 $query->whereHas('category', function ($query) use ($excludeCategoryId) {
    //                     $query->where('id', $excludeCategoryId); // Exclude blogs with the provided category ID
    //                 });
    //             })
    //             ->whereNotExists(function ($query) use ($header) {
    //                 $query->select(DB::raw(1))
    //                       ->from('view_blog')
    //                       ->where('view_blog.blog_id', '=', $blogQuery->id)
    //                       ->where('view_blog.user_id', '=', $header);
    //             })
    //             ->with([
    //                 'blog_category' => function ($query) {
    //                     $query->whereHas('category', function ($query) {
    //                         $query->where('status', 1);
    //                     });
    //                 }
    //             ])
    //             ->orderBy('schedule_date', 'DESC');

    //         $blog = $blogQuery->get();

    //         $ads_list = Ads::where('status', 1)
    //             ->where('end_date', ">=", date('Y-m-d'))
    //             ->withCount('click')
    //             ->withCount('view')
    //             ->with('media')
    //             ->orderBy('order', 'ASC')
    //             ->get();

    //         if (count($ads_list)) {
    //             foreach ($ads_list as $ads_list_data) {
    //                 foreach ($ads_list_data->media as $media_list) {
    //                     if ($media_list->type == 'image') {
    //                         $media_list->file = url('storage/ads/' . $media_list->file);
    //                     }
    //                 }
    //             }
    //         }

    //         foreach ($blog as $row) {
    //             $flag = false;
    //             $blogTranslate = BlogTranslation::where('blog_id', $row->id)->where('language_code', $this->language)->first();
    //             if ($blogTranslate) {
    //                 $flag = true;
    //                 $row->title = $blogTranslate->title;
    //                 $row->tags = $blogTranslate->tags;
    //                 $row->description = $blogTranslate->description;
    //                 $row->seo_title = $blogTranslate->seo_title;
    //                 $row->seo_keyword = $blogTranslate->seo_keyword;
    //                 $row->seo_tag = $blogTranslate->seo_tag;
    //                 $row->seo_description = $blogTranslate->seo_description;
    //             }

    //             $row->trimed_description = strip_tags($row->description);
    //             $row->trimed_description = str_replace("&nbsp;", '', $row->trimed_description);
    //             $row->trimed_description = str_replace("&#39;", "'", $row->trimed_description);
    //             $row->created_at = date("d M Y h:i a", strtotime($row->created_at));

    //             if ($row->thumb_image != '') {
    //                 $row->thumb_image = url('upload/blog/thumb/360/' . $row->thumb_image);
    //             } else {
    //                 $row->thumb_image = url('upload/blog/thumb/default.png');
    //             }

    //             $check_image = BlogImages::where('blog_id', $row->id)->pluck('image');
    //             $blog_image = array();

    //             if (count($check_image)) {
    //                 foreach ($check_image as $value) {
    //                     $value = url('upload/blog/banner/800/' . $value);
    //                     array_push($blog_image, $value);
    //                 }
    //                 $row->banner_image = $blog_image;
    //             } else {
    //                 $blog_image[0] = url('upload/author/default.png');
    //                 $row->banner_image = $blog_image;
    //             }

    //             if ($header != null) {
    //                 $vote = Vote::where('user_id', $header)->where('blog_id', $row->id)->first();
    //                 if ($vote) {
    //                     $row->is_vote = 1;
    //                 } else {
    //                     $row->is_vote = 0;
    //                 }

    //                 $bookmarked = BookMarkPost::where('user_id', $header)->where('blog_id', $row->id)->first();
    //                 if ($bookmarked) {
    //                     $row->is_bookmark = 1;
    //                 } else {
    //                     $row->is_bookmark = 0;
    //                 }
    //             } else {
    //                 $row->is_vote = 0;
    //                 $row->is_bookmark = 0;
    //             }

    //             $total_votes = Vote::where('blog_id', $row->id)->count();
    //             $yes_votes = Vote::where('blog_id', $row->id)->where('vote', 1)->count();
    //             $no_votes = Vote::where('blog_id', $row->id)->where('vote', 0)->count();
    //             $row->view_count = BlogViewCount::where('blog_id', $row->id)->count();

    //             $yes_percent = $yes_votes != 0 ? ($yes_votes / $total_votes) * 100 : 0;
    //             $no_percent = $no_votes != 0 ? ($no_votes / $total_votes) * 100 : 0;

    //             $row->yes_percent = round($yes_percent);
    //             $row->no_percent = round($no_percent);

    //             $author = Author::where('id', $row->author_id)->first();
    //             if ($author) {
    //                 $row->author_name = $author->name;
    //                 $row->image = $author->image ? url('upload/author/original/' . $author->image) : url('upload/author/default.png');
    //             } else {
    //                 $row->author_name = "";
    //                 $row->image = url('upload/author/default.png');
    //             }

    //             $category = Category::where('id', $row->category_id)->first();
    //             if ($category) {
    //                 $catTranslate = CategoryTranslation::where('category_id', $category->id)->where('language_code', $this->language)->first();
    //                 if ($catTranslate) {
    //                     $category->name = $catTranslate->name;
    //                 }
    //                 $row->category_name = $category->name;
    //                 $row->color = $category->color;
    //             } else {
    //                 $row->category_name = "";
    //                 $row->color = "";
    //             }

    //             $row->time = $row->time . " min";
    //             $row->create_date = date("d M Y // h:i a", strtotime($row->schedule_date));
    //         }

    //         $result = [];
    //         $adCount = count($ads_list);
    //         $adIndex = 0;
    //         $BlogCount = 0;

    //         if (count($ads_list)) {
    //             foreach ($blog as $key => $item) {
    //                 if ($BlogCount == $ads_list[$adIndex]['frequency']) {
    //                     $ads_list[$adIndex]['type'] = "ads";
    //                     $result[] = $ads_list[$adIndex];
    //                     $adIndex = ($adIndex + 1) % $adCount;
    //                     $BlogCount = 0;
    //                 }
    //                 $result[] = $item;
    //                 $BlogCount++;
    //             }
    //         } else {
    //             foreach ($blog as $key => $item) {
    //                 $result[] = $item;
    //             }
    //         }

    //         $dataCheck = $this->arrayPaginator($result, $request);
    //         return $this->sendResponse($dataCheck, __('message_alerts.blog_list'));

    //     } catch (\Exception $e) {
    //         return $this->sendError($e->getMessage(), 401);
    //     }
    // }




    /**

     * Show Blog view.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function detail($id, Request $request)
    {
        $header = $request->header('userData');
        if ($header != null) {
            $user = User::where('id', $header)->first();
            if (!isset($user->id)) {
                return $this->sendError('Your account has been deleted by the Admin', 401);
            }
        }

        try {

            $blog = Blog::getActiveBlogDetail($id);

            if ($blog) {

                $blogTranslate = BlogTranslation::where('blog_id', $blog->id)->where('language_code', $this->language)->first();

                if ($blogTranslate) {

                    $blog->title = $blogTranslate->title;

                    $blog->tags = $blogTranslate->tags;

                    $blog->description = $blogTranslate->description;

                    $blog->seo_title = $blogTranslate->seo_title;

                    $blog->seo_keyword = $blogTranslate->seo_keyword;

                    $blog->seo_tag = $blogTranslate->seo_tag;

                    $blog->seo_description = $blogTranslate->seo_description;
                    if (str_word_count($blog->description) > 62) {
                        $blog->description = substr($blog->description, 0, 420);
                        $blog->description = $blog->description . ".....";
                    }
                    $blog->trimed_description = strip_tags($blog->description);
                    $blog->trimed_description = str_replace("&nbsp;", '', $blog->trimed_description);
                    $blog->trimed_description = str_replace("&#39;", "'", $blog->trimed_description);
                    $blog->created_at = date("d M Y h:i a", strtotime($blog->created_at));
                    if ($blog->thumb_image != '') {
                        $blog->thumb_image = url('upload/blog/thumb/360/' . $blog->thumb_image);
                    } else {
                        $blog->thumb_image = url('upload/blog/thumb/default.png');
                    }
                    $check_image = BlogImages::where('blog_id', $blog->id)->pluck('image');
                    $blog_image = array();
                    if (count($check_image)) {
                        foreach ($check_image as $value) {
                            $value = url('upload/blog/banner/800/' . $value);
                            array_push($blog_image, $value);
                        }
                        $blog->banner_image = $blog_image;
                    } else {
                        $blog_image[0] = url('upload/author/default.png');
                        $blog->banner_image = $blog_image;
                    }
                    if ($header != null) {
                        $vote = Vote::where('user_id', $header)->where('blog_id', $blog->id)->first();
                        if ($vote) {
                            $blog->is_vote = 1;
                        } else {
                            $blog->is_vote = 0;
                        }
                        $bookmarked = BookMarkPost::where('user_id', $header)->where('blog_id', $blog->id)->first();
                        if ($bookmarked) {
                            $blog->is_bookmark = 1;
                        } else {
                            $blog->is_bookmark = 0;
                        }
                    } else {
                        $blog->is_vote = 0;
                        $blog->is_bookmark = 0;
                    }
                    $total_votes = Vote::where('blog_id', $blog->id)->count();
                    $yes_votes = Vote::where('blog_id', $blog->id)->where('vote', 1)->count();
                    $no_votes = Vote::where('blog_id', $blog->id)->where('vote', 0)->count();
                    $blog->view_count = BlogViewCount::where('blog_id', $blog->id)->count();
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
                    $blog->yes_percent = round($yes_percent);
                    $blog->no_percent = round($no_percent);
                    $blog->author_name = $blog->username;

                    if ($blog->image == null) {
                        $blog->image =  $blog->image;
                    } else {
                        $blog->author_name = "";
                        $blog->image = url('upload/author/default.png');
                    }
                    $category = Category::where('id', $blog->category_id)->first();
                    if ($category) {
                        $catTranslate = CategoryTranslation::where('category_id', $category->id)->where('language_code', $this->language)->first();
                        if ($catTranslate) {
                            $category->name = $catTranslate->name;
                        }
                        $blog->category_name = $category->name;
                        $blog->color = $category->color;
                    } else {
                        $blog->category_name = "";
                        $blog->color = "";
                    }
                    $blog->time = $blog->time . " min";
                    $blog->create_date = date("d M Y // h:i a", strtotime($blog->schedule_date));

                    return $this->sendResponse($blog, __('message_alerts.blog_list'));
                }

                return $this->sendError(__('message_alerts.blog_not_found'), 401);
            }
        } catch (\Exception $e) {

            return $this->sendError($e->getMessage(), 401);
        }
    }

    /**

     * Show Blog Search Result.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function searchBlog(Request $request)
    {
        try {
            $post = $request->all();
            $validate = [
                'title' => 'required|min:3',
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                $data['error'] = $validator->errors();
                return response(\Helpers::sendFailureAjaxResponse($data['error']));
            } else {
                $header = $request->header('userData');
                if ($header != null) {
                    $user = User::where('id', $header)->first();
                    if (!isset($user->id)) {
                        return $this->sendError('Your account has been deleted by the Admin', 401);
                    }
                }
                $blog_image = array();
                $final_blog = array();
                // $blog = Blog::where('title', 'like', '%' . trim($post['title']) . '%')
                //     ->orWhere('blog_code', 'like', '%' . trim($post['blog_code']) . '%')
                //     ->where('status', 1)->where('schedule_date', "<=", date("Y-m-d H:i:s"))
                //     ->with('blog_category')->get();
                // $blog = Blog::where(function ($query) use ($post) {
                //     $query->where('blog_code', 'like', '%' . trim($post['blog_code']) . '%')
                //         ->orWhere(function ($innerQuery) use ($post) {
                //             $innerQuery->whereDoesntHave('blog_category', function ($categoryQuery) {
                //                 $categoryQuery->where('category_id', 12);
                //             })->where('title', 'like', '%' . trim($post['title']) . '%');
                //         });
                // })
                //     ->where('status', 1)
                //     ->where('schedule_date', '<=', date("Y-m-d H:i:s"))
                //     ->with('blog_category')
                //     ->get();
                $searchTerm = trim($post['title'] ?? '');

                $blog = Blog::query()
                    ->leftJoin('users', 'blog.created_by', '=', 'users.id')
                    ->leftJoin('blog_category', 'blog.id', '=', 'blog_category.blog_id')
                    ->leftJoin('category', 'blog_category.category_id', '=', 'category.id')
                    ->where(function ($query) use ($post, $searchTerm) {
                        if (isset($post['blog_code'])) {
                            $query->where('blog.blog_code', 'like', '%' . trim($post['blog_code']) . '%');
                        }

                        if (!empty($searchTerm)) {
                            $query->orWhere(function ($subQuery) use ($searchTerm) {
                                $subQuery->where('blog.title', 'like', '%' . $searchTerm . '%')
                                    ->orWhere('blog.description', 'like', '%' . $searchTerm . '%')
                                    ->orWhere('users.name', 'like', '%' . $searchTerm . '%')
                                    ->orWhere('category.name', 'like', '%' . $searchTerm . '%');
                            });
                        }
                    })
                    ->where('blog.status', 1)
                    ->where('blog.schedule_date', '<=', now())

                    ->where(function ($query) {
                        $query->whereHas('blog_category.category', function ($q) {
                            $q->where('name', 'Personal News');
                        })
                            ->where('blog.schedule_date', '>=', now()->subDays(8))
                            ->orWhere(function ($q) {
                                $q->whereDoesntHave('blog_category.category', function ($qq) {
                                    $qq->where('name', 'Personal News');
                                })
                                    ->where('blog.schedule_date', '>=', now()->subDays(3));
                            });
                    })

                    ->whereHas('blog_category')
                    ->with(['blog_category', 'author'])

                    ->select('blog.*', 'users.name as username', 'users.photo as image')

                    // ✅ THIS MAKES BLOG UNIQUE
                    ->groupBy('blog.id')

                    ->get();
                if (count($blog) == 0) {
                    $blog = Blog::where('tags', 'like', '%' . trim($post['title']) . '%')->where('status', 1)->where('schedule_date', "<=", date("Y-m-d H:i:s"))->with('blog_category')->get();
                }
                if (count($blog)) {
                    foreach ($blog as $row) {
                        $flag = false;
                        $blogTranslate = BlogTranslation::where('blog_id', $row->id)->where('language_code', $this->language)->first();
                        if ($blogTranslate) {
                            $flag = true;
                            $row->title = $blogTranslate->title;
                            $row->tags = $blogTranslate->tags;
                            $row->description = $blogTranslate->description;
                            $row->seo_title = $blogTranslate->seo_title;
                            $row->seo_keyword = $blogTranslate->seo_keyword;
                            $row->seo_tag = $blogTranslate->seo_tag;
                            $row->seo_description = $blogTranslate->seo_description;
                        }
                        if (str_word_count($row->description) > 62) {
                            $row->description = substr($row->description, 0, 420);
                            $row->description = $row->description . ".....";
                        }
                        $row->trimed_description = strip_tags($row->description);
                        $row->trimed_description = str_replace("&nbsp;", '', $row->trimed_description);
                        $row->trimed_description = str_replace("&#39;", "'", $row->trimed_description);
                        $row->created_at = date("d M Y h:i a", strtotime($row->created_at));
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
                        $total_votes = Vote::where('blog_id', $row->id)->count();
                        $yes_votes = Vote::where('blog_id', $row->id)->where('vote', 1)->count();
                        $no_votes = Vote::where('blog_id', $row->id)->where('vote', 0)->count();
                        $row->view_count = BlogViewCount::where('blog_id', $row->id)->count();
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
                        $row->author_name = $row->username;

                        if ($row->image == null) {
                            $row->image =  $row->image;
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
                        $row->create_date = date("d M Y // h:i a", strtotime($row->schedule_date));
                        if ($flag) {
                            array_push($final_blog, $row);
                        }
                    }
                    $log = array(
                        'user_id' => 0,
                        'search_keyword' => $post['title'],
                        'search_count' => count($blog),
                        'created_at' => date('Y-m-d h:i:s')
                    );
                    // dd($log);
                    $existingLog = SearchLog::where('search_keyword', $post['title'])->first();

                    if ($existingLog) {
                        // Update existing record
                        $existingLog->search_count = $existingLog->search_count + 1; // adds new count();
                        $existingLog->save();
                    } else {
                        // Insert new record
                        SearchLog::create([
                            'user_id' => 0,
                            'search_keyword' => $post['title'],
                            'search_count' => 1,
                            'created_at' => now(),
                        ]);
                    }
                    return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_found'), $final_blog));
                } else {
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.record_not_found')));
                }
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }
    }

    /**

     * Show list of settings.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function settingList(Request $request)
    {

        try {

            $app_name = SiteContent::where('key', 'app_name')->first();

            $app_subtitle = SiteContent::where('key', 'app_subtitle')->first();

            $app_image = SiteContent::where('key', 'bg_image')->first();

            if ($app_image) {

                if ($app_image->value != '') {

                    $app_image->value = url('upload/bg/' . $app_image->value);
                } else {

                    $app_image->value = url('upload/bg/default.jpg');
                }
            }

            $live_news_logo = SiteContent::where('key', 'live_news_logo')->first();

            if (isset($live_news_logo) && $live_news_logo != '') {

                if ($live_news_logo->value != '') {

                    $live_news_logo->value = url('upload/live-news-logo/' . $live_news_logo->value);
                } else {

                    $live_news_logo->value = url('upload/no-image.png');
                }
            }

            $e_paper_logo = SiteContent::where('key', 'e_paper_logo')->first();

            if (isset($e_paper_logo) && $e_paper_logo != '') {

                if ($e_paper_logo->value != '') {

                    $e_paper_logo->value = url('upload/e-paper/' . $e_paper_logo->value);
                } else {

                    $e_paper_logo->value = url('upload/no-image.png');
                }
            }

            $live_news_status = SiteContent::where('key', 'live_news_status')->first();

            $e_paper_status = SiteContent::where('key', 'e_paper_status')->first();

            $ads_frequency = SiteContent::where('key', 'ads_frequency')->first();

            $admob_interstitial_id_ios = SiteContent::where('key', 'admob_interstitial_id_ios')->first();

            $admob_banner_id_ios = SiteContent::where('key', 'admob_banner_id_ios')->first();

            $admob_interstitial_id_android = SiteContent::where('key', 'admob_interstitial_id_android')->first();

            $admob_banner_id_android = SiteContent::where('key', 'admob_banner_id_android')->first();

            $enable_ads = SiteContent::where('key', 'enable_ads')->first();

            $fb_ads_placement_id_android = SiteContent::where('key', 'fb_ads_placement_id_android')->first();

            $fb_ads_placement_id_ios = SiteContent::where('key', 'fb_ads_placement_id_ios')->first();

            $fb_ads_app_token = SiteContent::where('key', 'fb_ads_app_token')->first();

            $enable_fb_ads = SiteContent::where('key', 'enable_fb_ads')->first();

            $after_news_ads = SiteContent::where('key', 'after_news_ads')->first();

            $news_view_time = SiteContent::where('key', 'time_for_news_view')->first();

            $personal_category_auto_remove = SiteContent::where('key', 'personal_category_auto_remove')->first();
            $story_view_visibility = SiteContent::where('key', 'story_view_visibility')->first();

            $social_links = Social::getAllActiveSocial();
            foreach ($social_links as $link) {
                if (!empty($link->image)) {
                    $link->image = url('upload/social/' . $link->image);
                }
            }

            $settings = array(
                'social_links' => $social_links,
                'app_name' => $app_name->value,
                'app_image' => $app_image->value,
                'app_subtitle' => $app_subtitle->value,
                'live_news_logo' => $live_news_logo->value,
                'e_paper_logo' => $e_paper_logo->value,
                'live_news_status' => $live_news_status->value,
                'e_paper_status' => $e_paper_status->value,
                'ads_frequency' => $ads_frequency->value,
                'admob_interstitial_id_ios' => $admob_interstitial_id_ios->value,
                'admob_banner_id_ios' => $admob_banner_id_ios->value,
                'admob_interstitial_id_android' => $admob_interstitial_id_android->value,
                'admob_banner_id_android' => $admob_banner_id_android->value,
                'enable_ads' => $enable_ads->value,
                'fb_ads_placement_id_android' => $fb_ads_placement_id_android->value,
                'fb_ads_placement_id_ios' => $fb_ads_placement_id_ios->value,
                'fb_ads_app_token' => $fb_ads_app_token->value,

                'enable_fb_ads' => $enable_fb_ads->value,
                'news_view_time' => $news_view_time->value,
                'ads_show_after_news' => $after_news_ads->value,

                'personal_category_auto_remove' => $personal_category_auto_remove->value,
                'story_view_visibility' => $story_view_visibility ? $story_view_visibility->value : '1'
            );

            //$settings = array('app_name'=>$app_name->value,'app_image'=>$app_image->value,'app_subtitle'=>$app_subtitle->value);

            return $this->sendResponse($settings, __('message_alerts.setting_list'));
        } catch (\Exception $e) {

            return $this->sendError($e->getMessage(), 401);
        }
    }





    /**

     * bookmarkPost

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */



    public function bookmarkPost(Request $request)
    {

        try {

            $post = $request->all();

            $validate = [

                'user_id' => 'required',

                'blog_id' => 'required',

            ];

            $validator = Validator::make($post, $validate);

            if ($validator->fails()) {

                $data['error'] = $validator->errors();



                return response(\Helpers::sendFailureAjaxResponse($data['error']));
            } else {

                $alreadyBookmarked = BookMarkPost::where('user_id', $post['user_id'])->where('blog_id', $post['blog_id'])->count();

                if ($alreadyBookmarked) {

                    return $this->sendResponse([], 'Already Bookmarked');
                } else {

                    $data['blog'] = BookMarkPost::insertGetID(array('user_id' => $post['user_id'], 'blog_id' => $post['blog_id']));

                    $bookmarked = BookMarkPost::where('user_id', $post['user_id'])->where('blog_id', $post['blog_id'])->first();

                    if ($bookmarked) {

                        $data['is_bookmark'] = 1;
                    } else {

                        $data['is_bookmark'] = 0;
                    }

                    if ($data['blog']) {

                        return $this->sendResponse($data, __('message_alerts.bookmarked_success'));
                    } else {

                        return $this->sendError($data['blog'], 500);
                    }
                }
            }
        } catch (\Exception $ex) {

            return $this->sendError($ex->getMessage(), 401);
        }
    }





    /**

     * Delete bookmarkPost

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */





    public function deleteBookmarkPost(Request $request)
    {

        try {

            $post = $request->all();

            $validate = [

                'user_id' => 'required',

                'blog_id' => 'required',

            ];

            $validator = Validator::make($post, $validate);

            if ($validator->fails()) {

                $data['error'] = $validator->errors();

                return response(\Helpers::sendFailureAjaxResponse($data['error']));
            } else {

                BookMarkPost::where('user_id', $post['user_id'])->where('blog_id', $post['blog_id'])->delete();

                return $this->sendResponse([], __('message_alerts.bookmark_removed'));
            }
        } catch (\Exception $ex) {

            return $this->sendError($ex->getMessage(), 401);
        }
    }

    /**

     * All bookmarked Post

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */



    public function AllBookmarkPost(Request $request)
    {

        try {

            $post = $request->all();

            $blogs = array();

            $final_blog = array();



            $header = $request->header('userData');

            if (isset($post['category_id']) && $post['category_id'] != '' && $post['category_id'] != 0) {

                $blogs = Blog::where('category_id', $post['category_id'])->where('schedule_date', "<=", date("Y-m-d H:i:s"))->with('blog_category')->orderBy('schedule_date', 'DESC')->get();

                foreach ($blogs as $row) {

                    $flag = false;



                    $blogTranslate = BlogTranslation::where('blog_id', $row->id)->where('language_code', $this->language)->first();

                    if ($blogTranslate) {



                        $flag = true;

                        $row->title = $blogTranslate->title;

                        $row->tags = $blogTranslate->tags;

                        $row->description = $blogTranslate->description;

                        $row->seo_title = $blogTranslate->seo_title;

                        $row->seo_keyword = $blogTranslate->seo_keyword;

                        $row->seo_tag = $blogTranslate->seo_tag;

                        $row->seo_description = $blogTranslate->seo_description;
                    }





                    if (str_word_count($row->description) > 62) {

                        $row->description = substr($row->description, 0, 420);

                        $row->description = $row->description . ".....";
                    }

                    $row->trimed_description = strip_tags($row->description);

                    $row->trimed_description = str_replace("&nbsp;", '', $row->trimed_description);

                    $row->trimed_description = str_replace("&#39;", "'", $row->trimed_description);

                    $row->created_at = date("d M Y h:i a", strtotime($row->created_at));

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

                    $total_votes = Vote::where('blog_id', $row->id)->count();

                    $yes_votes = Vote::where('blog_id', $row->id)->where('vote', 1)->count();

                    $no_votes = Vote::where('blog_id', $row->id)->where('vote', 0)->count();

                    $row->view_count = BlogViewCount::where('blog_id', $row->id)->count();

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

                    $row->create_date = date("d M Y // h:i a", strtotime($row->schedule_date));



                    if ($flag) {

                        array_push($final_blog, $row);
                    }
                }

                return $this->sendResponse($final_blog, __('message_alerts.record_found'));
            } else {

                $data = BookMarkPost::where('user_id', $post['user_id'])->OrderBy('created_at', 'DESC')->get();

                foreach ($data as $row) {

                    $blogdata = Blog::where('id', $row->blog_id)->with('blog_category')->first();

                    if ($blogdata) {



                        $flag = false;

                        $blogTranslate = BlogTranslation::where('blog_id', $blogdata->id)->where('language_code', $this->language)->first();



                        if ($blogTranslate) {

                            $flag = true;

                            $blogdata->title = $blogTranslate->title;

                            $blogdata->tags = $blogTranslate->tags;

                            $blogdata->description = $blogTranslate->description;

                            $blogdata->seo_title = $blogTranslate->seo_title;

                            $blogdata->seo_keyword = $blogTranslate->seo_keyword;

                            $blogdata->seo_tag = $blogTranslate->seo_tag;

                            $blogdata->seo_description = $blogTranslate->seo_description;
                        }



                        if (str_word_count($blogdata->description) > 62) {

                            $blogdata->description = substr($blogdata->description, 0, 420);

                            $blogdata->description = $blogdata->description . ".....";
                        }

                        $blogdata->trimed_description = strip_tags($blogdata->description);

                        $blogdata->trimed_description = str_replace("&nbsp;", '', $blogdata->trimed_description);

                        $blogdata->trimed_description = str_replace("&#39;", "'", $blogdata->trimed_description);

                        $blogdata->created_at = date("d M Y h:i a", strtotime($blogdata->created_at));

                        if ($blogdata->thumb_image != '') {

                            $blogdata->thumb_image = url('upload/blog/thumb/360/' . $blogdata->thumb_image);
                        } else {

                            $blogdata->thumb_image = url('upload/blog/thumb/default.png');
                        }

                        $check_image = BlogImages::where('blog_id', $blogdata->id)->pluck('image');

                        $blog_image = array();

                        if (count($check_image)) {

                            foreach ($check_image as $value) {

                                $value = url('upload/blog/banner/800/' . $value);

                                array_push($blog_image, $value);
                            }

                            $blogdata->banner_image = $blog_image;
                        } else {

                            $blog_image[0] = url('upload/author/default.png');

                            $blogdata->banner_image = $blog_image;
                        }

                        if ($header != null) {

                            $vote = Vote::where('user_id', $header)->where('blog_id', $blogdata->id)->first();

                            if ($vote) {

                                $blogdata->is_vote = 1;
                            } else {

                                $blogdata->is_vote = 0;
                            }

                            $bookmarked = BookMarkPost::where('user_id', $header)->where('blog_id', $blogdata->id)->first();

                            if ($bookmarked) {

                                $blogdata->is_bookmark = 1;
                            } else {

                                $blogdata->is_bookmark = 0;
                            }
                        } else {

                            $blogdata->is_vote = 0;

                            $blogdata->is_bookmark = 0;
                        }

                        $total_votes = Vote::where('blog_id', $blogdata->id)->count();

                        $yes_votes = Vote::where('blog_id', $blogdata->id)->where('vote', 1)->count();

                        $no_votes = Vote::where('blog_id', $blogdata->id)->where('vote', 0)->count();

                        $blogdata->view_count = BlogViewCount::where('blog_id', $blogdata->id)->count();

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

                        $blogdata->yes_percent = round($yes_percent);

                        $blogdata->no_percent = round($no_percent);

                        $author = Author::where('id', $blogdata->author_id)->first();

                        if ($author) {

                            $blogdata->author_name = $author->name;

                            if ($author->image != null || $author->image != '') {

                                $blogdata->image = url('upload/author/original/' . $author->image);
                            } else {

                                $blogdata->image = url('upload/author/default.png');
                            }
                        } else {

                            $blogdata->author_name = "";

                            $blogdata->image = url('upload/author/default.png');
                        }

                        $category = Category::where('id', $blogdata->category_id)->first();

                        if ($category) {

                            $catTranslate = CategoryTranslation::where('category_id', $category->id)->where('language_code', $this->language)->first();

                            if ($catTranslate) {

                                $category->name = $catTranslate->name;
                            }



                            $blogdata->category_name = $category->name;

                            $blogdata->color = $category->color;
                        } else {

                            $blogdata->category_name = "";

                            $blogdata->color = "";
                        }

                        $blogdata->time = $blogdata->time . " min";

                        $blogdata->create_date = date("d M Y // h:i a", strtotime($blogdata->schedule_date));

                        //array_push($blogs,$blogdata);

                        if ($flag) {

                            array_push($final_blog, $blogdata);
                        }
                    }
                }

                return $this->sendResponse($final_blog, __('message_alerts.record_found'));
            }
        } catch (\Exception $ex) {

            return $this->sendError($ex->getMessage(), 401);
        }
    }





    /**

     * increaseBlogViewCount

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */



    public function increaseBlogViewCount(Request $request)
    {

        try {

            $post = $request->all();
            $validate = [

                'blog_id' => 'required',

                'action' => 'required',

            ];

            $validator = Validator::make($post, $validate);

            if ($validator->fails()) {

                $data['error'] = $validator->errors();

                return response(\Helpers::sendFailureAjaxResponse($data['error']));
            } else {



                $alreadyBookmarked = BlogViewCount::where('blog_id', $post['blog_id']);
                if (isset($post['user_id']) && !empty($post['user_id']) && isset($post['device_id']) && !empty($post['device_id'])) {
                    $alreadyBookmarked = $alreadyBookmarked->where('user_id', $post['user_id'])->where('device_id', $post['device_id']);
                } else if (isset($post['user_id']) && !empty($post['user_id'])) {
                    $alreadyBookmarked = $alreadyBookmarked->where('user_id', $post['user_id'])->where('device_id', null);
                } else if (isset($post['device_id']) && !empty($post['device_id'])) {
                    $alreadyBookmarked = $alreadyBookmarked->where('device_id', $post['device_id'])->where('user_id', null);
                }
                $alreadyBookmarked = $alreadyBookmarked->count();

                if ($alreadyBookmarked) {

                    return $this->sendResponse([], __('message_alerts.already_viewed'));
                } else {

                    $blog = BlogViewCount::insertGetID(array('user_id' => $post['user_id'] ?? null, 'blog_id' => $post['blog_id'], 'action' => $post['action'], 'device_id' => $post['device_id'] ?? null));

                    if ($blog) {

                        return $this->sendResponse($blog, __('message_alerts.successfully_viewed'));
                    } else {

                        return $this->sendError($blog, 500);
                    }
                }
            }
        } catch (\Exception $ex) {

            return $this->sendError($ex->getMessage(), 401);
        }
    }



    public function blogResetview(Request $request)
    {

        try {

            $post = $request->all();

            $alreadyBookmarked = null;

            if (!empty($post['user_id']) && !empty($post['device_id'])) {
                $alreadyBookmarked = BlogViewCount::where('user_id', $post['user_id'])
                    ->where('device_id', $post['device_id']);
            } elseif (!empty($post['user_id'])) {
                $alreadyBookmarked = BlogViewCount::where('user_id', $post['user_id']);
            } elseif (!empty($post['device_id'])) {
                $alreadyBookmarked = BlogViewCount::where('device_id', $post['device_id']);
            }

            if ($alreadyBookmarked) {
                $deletedCount = $alreadyBookmarked->delete();

                if ($deletedCount > 0) {
                    return $this->sendResponse([], __('message_alerts.successfully_deleted'));
                } else {
                    return $this->sendResponse([], __('message_alerts.already_deleted'));
                }
            }
        } catch (\Exception $ex) {

            return $this->sendError($ex->getMessage(), 401);
        }
    }

    // public function blogView(Request $request)
    // {
    //     try {
    //         $post = $request->all();
    //         $validate = [
    //             'blog_id' => 'required',
    //         ];
    //         $validator = Validator::make($post, $validate);
    //         if ($validator->fails()) {
    //             $data['error'] = $validator->errors();
    //             return response(\Helpers::sendFailureAjaxResponse($data['error']));
    //         } else {
    //             $blog = StoreViewed::insertGetID(array('user_id' => $post['user_id'], 'blog_id' => $post['blog_id'], 'device_id' => $post['device_id']));
    //             if ($blog) {
    //                 return $this->sendResponse($blog, __('message_alerts.successfully_viewed'));
    //             } else {
    //                 return $this->sendError($blog, 500);
    //             }
    //         }
    //     } catch (\Exception $ex) {
    //         return $this->sendError($ex->getMessage(), 401);
    //     }
    // }

    public function blogView(Request $request)
    {
        try {
            $post = $request->all();
            $validate = [
                'blog_id' => 'required',
                'user_id' => 'required',
                'device_id' => 'required',
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }
            $blog = StoreViewed::create([
                'user_id' => $post['user_id'],
                'blog_id' => $post['blog_id'],
                'device_id' => $post['device_id'],
            ]);
            if ($blog) {
                return response()->json(['message' => __('message_alerts.successfully_viewed')], 200);
            } else {
                return response()->json(['error' => 'Failed to create entry'], 500);
            }
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 401);
        }
    }

    //     public function blogView(Request $request)
    // {
    //     try {
    //         $post = $request->all();
    //         $validate = [
    //             // 'user_id' => 'required',
    //             'blog_id' => 'required',
    //             // 'device_id' => 'required',
    //         ];
    //         $validator = Validator::make($post, $validate);
    //         if ($validator->fails()) {
    //             $data['error'] = $validator->errors();
    //             return response(\Helpers::sendFailureAjaxResponse($data['error']));
    //         } else {
    //             $exists = StoreViewed::where('user_id', $post['user_id'])
    //                                  ->where('blog_id', $post['blog_id'])
    //                                  ->where('device_id', $post['device_id'])
    //                                  ->exists();

    //             if ($exists) {
    //                 return $this->sendError(__('message_alerts.already_viewed'), 409); // Conflict status code
    //             } else {
    //                 $blog = StoreViewed::insertGetID(array('user_id' => $post['user_id'], 'blog_id' => $post['blog_id'], 'device_id' => $post['device_id']));
    //                 if ($blog) {
    //                     return $this->sendResponse($blog, __('message_alerts.successfully_viewed'));
    //                 } else {
    //                     return $this->sendError(__('message_alerts.insertion_failed'), 500);
    //                 }
    //             }
    //         }
    //     } catch (\Exception $ex) {
    //         return $this->sendError($ex->getMessage(), 401);
    //     }
    // }


    /**

     * Vote for the blog

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */



    public function addBlogVote(Request $request)
    {

        try {

            $post = $request->all();

            $validate = [

                'user_id' => 'required',

                'blog_id' => 'required',

            ];

            $validator = Validator::make($post, $validate);

            if ($validator->fails()) {

                $data['error'] = $validator->errors();

                return response(\Helpers::sendFailureAjaxResponse($data['error']));
            } else {

                $alreadyVoted = Vote::where('user_id', $post['user_id'])->where('blog_id', $post['blog_id'])->count();

                if ($alreadyVoted) {

                    return $this->sendResponse([], 'Already Voted');
                } else {

                    $post['created_at'] = date("Y-m-d h:i:s");

                    $vote = Vote::addVote($post);

                    if ($vote) {

                        return $this->sendResponse($vote, __('message_alerts.successfully_voted'));
                    } else {

                        return $this->sendError($vote, 500);
                    }
                }
            }
        } catch (\Exception $ex) {

            return $this->sendError($ex->getMessage(), 401);
        }
    }



    /**

     * For Swipe next blog

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */



    public function blogSwipe(Request $request)
    {

        try {

            $header = $request->header('userData');

            $post = $request->all();

            $validate = [

                'blog_id' => 'required',

            ];

            $validator = Validator::make($post, $validate);

            if ($validator->fails()) {

                $data['error'] = $validator->errors();

                return response(\Helpers::sendFailureAjaxResponse($data['error']));
            } else {

                $blog_image = array();

                $final_blog = array();



                $blogs = array();

                $header = $request->header('userData');

                $singleBlog = Blog::where('id', $post['blog_id'])->where('schedule_date', "<=", date("Y-m-d H:i:s"))->with('blog_category')->first();

                if ($singleBlog) {

                    $flag = false;

                    $blogTranslate = BlogTranslation::where('blog_id', $singleBlog->id)->where('language_code', $this->language)->first();

                    if ($blogTranslate) {

                        $flag = true;

                        $singleBlog->title = $blogTranslate->title;

                        $singleBlog->tags = $blogTranslate->tags;

                        $singleBlog->description = $blogTranslate->description;

                        $singleBlog->seo_title = $blogTranslate->seo_title;

                        $singleBlog->seo_keyword = $blogTranslate->seo_keyword;

                        $singleBlog->seo_tag = $blogTranslate->seo_tag;

                        $singleBlog->seo_description = $blogTranslate->seo_description;
                    }



                    if (str_word_count($singleBlog->description) > 62) {

                        $singleBlog->description = substr($singleBlog->description, 0, 420);

                        $singleBlog->description = $singleBlog->description . ".....";
                    }

                    $singleBlog->trimed_description = strip_tags($singleBlog->description);

                    $singleBlog->trimed_description = str_replace("&nbsp;", '', $singleBlog->trimed_description);

                    $singleBlog->trimed_description = str_replace("&#39;", "'", $singleBlog->trimed_description);

                    $singleBlog->created_at = date("d M Y h:i a", strtotime($singleBlog->created_at));

                    if ($singleBlog->thumb_image != '') {

                        $singleBlog->thumb_image = url('upload/blog/thumb/360/' . $singleBlog->thumb_image);
                    } else {

                        $singleBlog->thumb_image = url('upload/blog/thumb/default.png');
                    }

                    $check_image = BlogImages::where('blog_id', $singleBlog->id)->pluck('image');

                    $blog_image = array();

                    if (count($check_image)) {

                        foreach ($check_image as $value) {

                            $value = url('upload/blog/banner/800/' . $value);

                            array_push($blog_image, $value);
                        }

                        $singleBlog->banner_image = $blog_image;
                    } else {

                        $blog_image[0] = url('upload/author/default.png');

                        $singleBlog->banner_image = $blog_image;
                    }

                    if ($header != null) {

                        $vote = Vote::where('user_id', $header)->where('blog_id', $singleBlog->id)->first();

                        if ($vote) {

                            $singleBlog->is_vote = 1;
                        } else {

                            $singleBlog->is_vote = 0;
                        }

                        $bookmarked = BookMarkPost::where('user_id', $header)->where('blog_id', $singleBlog->id)->first();

                        if ($bookmarked) {

                            $singleBlog->is_bookmark = 1;
                        } else {

                            $singleBlog->is_bookmark = 0;
                        }
                    } else {

                        $singleBlog->is_vote = 0;

                        $singleBlog->is_bookmark = 0;
                    }

                    $total_votes = Vote::where('blog_id', $singleBlog->id)->count();

                    $yes_votes = Vote::where('blog_id', $singleBlog->id)->where('vote', 1)->count();

                    $no_votes = Vote::where('blog_id', $singleBlog->id)->where('vote', 0)->count();

                    $singleBlog->view_count = BlogViewCount::where('blog_id', $row->id)->count();

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

                    $singleBlog->yes_percent = round($yes_percent);

                    $singleBlog->no_percent = round($no_percent);

                    $author = Author::where('id', $singleBlog->author_id)->first();

                    if ($author) {

                        $singleBlog->author_name = $author->name;

                        if ($author->image != null || $author->image != '') {

                            $singleBlog->image = url('upload/author/original/' . $singleBlog->image);
                        } else {

                            $singleBlog->image = url('upload/author/default.png');
                        }
                    } else {

                        $singleBlog->author_name = "";

                        $singleBlog->image = url('upload/author/default.png');
                    }

                    $category = Category::where('id', $singleBlog->category_id)->first();

                    if ($category) {

                        $catTranslate = CategoryTranslation::where('category_id', $category->id)->where('language_code', $this->language)->first();

                        if ($catTranslate) {

                            $category->name = $catTranslate->name;
                        }

                        $singleBlog->category_name = $category->name;

                        $singleBlog->color = $category->color;
                    } else {

                        $singleBlog->category_name = "";

                        $singleBlog->color = "";
                    }

                    $singleBlog->time = $singleBlog->time . " min";

                    $singleBlog->create_date = date("d M Y // h:i a", strtotime($singleBlog->schedule_date));

                    // $blogs[0] = $singleBlog;



                    if ($flag) {

                        array_push($final_blog, $singleBlog);
                    }
                }

                $getBlog = Blog::where('status', 1)->where('id', '!=', $post['blog_id'])->where('schedule_date', "<=", date("Y-m-d H:i:s"))->orderBy('schedule_date', 'DESC')->get();

                foreach ($getBlog as $row) {

                    $newFlag = false;

                    $blogTranslate = BlogTranslation::where('blog_id', $row->id)->where('language_code', $this->language)->first();

                    if ($blogTranslate) {

                        $newFlag = true;

                        $row->title = $blogTranslate->title;

                        $row->tags = $blogTranslate->tags;

                        $row->description = $blogTranslate->description;

                        $row->seo_title = $blogTranslate->seo_title;

                        $row->seo_keyword = $blogTranslate->seo_keyword;

                        $row->seo_tag = $blogTranslate->seo_tag;

                        $row->seo_description = $blogTranslate->seo_description;
                    }



                    if (str_word_count($row->description) > 62) {

                        $row->description = substr($row->description, 0, 420);

                        $row->description = $row->description . ".....";
                    }

                    $row->trimed_description = strip_tags($row->description);

                    $row->trimed_description = str_replace("&nbsp;", '', $row->trimed_description);

                    $row->trimed_description = str_replace("&#39;", "'", $row->trimed_description);

                    $row->created_at = date("d M Y h:i a", strtotime($row->created_at));

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

                        $votes = Vote::where('user_id', $header)->where('blog_id', $row->id)->first();

                        if ($votes) {

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

                    $total_votes = Vote::where('blog_id', $row->id)->count();

                    $yes_votes = Vote::where('blog_id', $row->id)->where('vote', 1)->count();

                    $no_votes = Vote::where('blog_id', $row->id)->where('vote', 0)->count();

                    $row->view_count = BlogViewCount::where('blog_id', $row->id)->count();

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

                    $row->create_date = date("d M Y // h:i a", strtotime($row->schedule_date));

                    // array_push($blogs,$row);

                    if ($newFlag) {

                        array_push($final_blog, $row);
                    }
                }

                return $this->sendResponse($final_blog, __('message_alerts.record_found'));
            }
        } catch (\Exception $ex) {

            return $this->sendError($ex->getMessage(), 401);
        }
    }



    /**

     * Show Blog votes.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function getBlogVote(Request $request)
    {

        try {

            $post = $request->all();

            $validate = [

                'blog_id' => 'required|min:1',

            ];

            $validator = Validator::make($post, $validate);

            if ($validator->fails()) {

                $data['error'] = $validator->errors();

                return response(\Helpers::sendFailureAjaxResponse($data['error']));
            } else {

                $header = $request->header('userData');

                $blog_image = array();

                $blog = Blog::where('id', $post['blog_id'])->where('schedule_date', "<=", date("Y-m-d H:i:s"))->with('blog_category')->first();

                if ($blog) {



                    $flag = false;



                    $blogTranslate = BlogTranslation::where('blog_id', $blog->id)->where('language_code', $this->language)->first();



                    if ($blogTranslate) {



                        $flag = true;





                        $blog->title = $blogTranslate->title;

                        $blog->tags = $blogTranslate->tags;

                        $blog->description = $blogTranslate->description;

                        $blog->seo_title = $blogTranslate->seo_title;

                        $blog->seo_keyword = $blogTranslate->seo_keyword;

                        $blog->seo_tag = $blogTranslate->seo_tag;

                        $blog->seo_description = $blogTranslate->seo_description;
                    }



                    if (str_word_count($blog->description) > 62) {

                        $blog->description = substr($blog->description, 0, 420);

                        $blog->description = $blog->description . ".....";
                    }



                    $blog->trimed_description = strip_tags($blog->description);

                    $blog->trimed_description = str_replace("&nbsp;", '', $blog->trimed_description);

                    $blog->trimed_description = str_replace("&#39;", "'", $blog->trimed_description);

                    $blog->created_at = date("d M Y h:i a", strtotime($blog->created_at));

                    if ($blog->thumb_image != '') {

                        $blog->thumb_image = url('upload/blog/thumb/360/' . $blog->thumb_image);
                    } else {

                        $blog->thumb_image = url('upload/blog/thumb/default.png');
                    }

                    $check_image = BlogImages::where('blog_id', $blog->id)->pluck('image');

                    $blog_image = array();

                    if (count($check_image)) {

                        foreach ($check_image as $value) {

                            $value = url('upload/blog/banner/800/' . $value);

                            array_push($blog_image, $value);
                        }

                        $blog->banner_image = $blog_image;
                    } else {

                        $blog_image[0] = url('upload/author/default.png');

                        $blog->banner_image = $blog_image;
                    }

                    if ($header != null) {

                        $vote = Vote::where('user_id', $header)->where('blog_id', $blog->id)->first();

                        if ($vote) {

                            $blog->is_vote = 1;
                        } else {

                            $blog->is_vote = 0;
                        }

                        $bookmarked = BookMarkPost::where('user_id', $header)->where('blog_id', $blog->id)->first();

                        if ($bookmarked) {

                            $blog->is_bookmark = 1;
                        } else {

                            $blog->is_bookmark = 0;
                        }
                    } else {

                        $blog->is_vote = 0;

                        $blog->is_bookmark = 0;
                    }

                    $total_votes = Vote::where('blog_id', $blog->id)->count();

                    $yes_votes = Vote::where('blog_id', $blog->id)->where('vote', 1)->count();

                    $no_votes = Vote::where('blog_id', $blog->id)->where('vote', 0)->count();

                    $blog->view_count = BlogViewCount::where('blog_id', $blog->id)->count();

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

                    $blog->yes_percent = round($yes_percent);

                    $blog->no_percent = round($no_percent);

                    $author = Author::where('id', $blog->author_id)->first();

                    if ($author) {

                        $blog->author_name = $author->name;

                        if ($author->image != null || $author->image != '') {

                            $blog->image = url('upload/author/original/' . $author->image);
                        } else {

                            $blog->image = url('upload/author/default.png');
                        }
                    } else {

                        $blog->author_name = "";

                        $blog->image = url('upload/author/default.png');
                    }

                    $category = Category::where('id', $blog->category_id)->first();

                    if ($category) {





                        $catTranslate = CategoryTranslation::where('category_id', $category->id)->where('language_code', $this->language)->first();

                        if ($catTranslate) {

                            $category->name = $catTranslate->name;
                        }



                        $blog->category_name = $category->name;

                        $blog->color = $category->color;
                    } else {

                        $blog->category_name = "";

                        $blog->color = "";
                    }

                    $blog->time = $blog->time . " min";

                    $blog->create_date = date("d M Y // h:i a", strtotime($blog->schedule_date));



                    if ($flag) {

                        return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_found'), $blog));
                    }

                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.blog_not_found')));
                } else {

                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.record_not_found')));
                }
            }
        } catch (\Exception $e) {

            return $this->sendError($e->getMessage(), 401);
        }
    }

    /**

     * Show Next and Previous Blog.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function nextPreviousBlog(Request $request)
    {

        try {

            $post = $request->all();

            $validate = [

                'blog_id' => 'required|min:1',

            ];

            $validator = Validator::make($post, $validate);

            if ($validator->fails()) {

                $data['error'] = $validator->errors();

                return response(\Helpers::sendFailureAjaxResponse($data['error']));
            } else {

                $header = $request->header('userData');

                $blog_image = array();

                $blog_image = array();

                if ($post['type'] == 'next') {

                    $blog = Blog::where('id', '>', $post['blog_id'])->where('schedule_date', "<=", date("Y-m-d H:i:s"))->where('deleted_at', null)->with('blog_category')->limit(1)->get();
                } else {

                    $blog = Blog::where('id', '<', $post['blog_id'])->where('schedule_date', "<=", date("Y-m-d H:i:s"))->where('deleted_at', null)->orderBy('schedule_date', 'desc')->with('blog_category')->limit(1)->get();
                }

                if ($blog) {

                    foreach ($blog as $row) {

                        $flag = false;

                        $blogTranslate = BlogTranslation::where('blog_id', $row->id)->where('language_code', $this->language)->first();



                        if ($blogTranslate) {



                            $flag = true;



                            $row->title = $blogTranslate->title;

                            $row->tags = $blogTranslate->tags;

                            $row->description = $blogTranslate->description;

                            $row->seo_title = $blogTranslate->seo_title;

                            $row->seo_keyword = $blogTranslate->seo_keyword;

                            $row->seo_tag = $blogTranslate->seo_tag;

                            $row->seo_description = $blogTranslate->seo_description;
                        }



                        if (str_word_count($row->description) > 62) {

                            $row->description = substr($row->description, 0, 420);

                            $row->description = $row->description . ".....";
                        }



                        $row->trimed_description = strip_tags($row->description);

                        $row->trimed_description = str_replace("&nbsp;", '', $row->trimed_description);

                        $row->trimed_description = str_replace("&#39;", "'", $row->trimed_description);

                        $row->created_at = date("d M Y h:i a", strtotime($row->created_at));

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

                        $total_votes = Vote::where('blog_id', $row->id)->count();

                        $yes_votes = Vote::where('blog_id', $row->id)->where('vote', 1)->count();

                        $no_votes = Vote::where('blog_id', $row->id)->where('vote', 0)->count();

                        $row->view_count = BlogViewCount::where('blog_id', $row->id)->count();

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

                        $row->create_date = date("d M Y // h:i a", strtotime($row->schedule_date));



                        if ($flag) {

                            array_push($final_blog, $row);
                        }
                    }

                    return response(\Helpers::sendSuccessAjaxResponse(ucfirst($post['type']) . ' ' . __('message_alerts.record_found'), $final_blog));
                } else {

                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.record_not_found')));
                }
            }
        } catch (\Exception $e) {

            return $this->sendError($e->getMessage(), 401);
        }
    }



    /**

     * Show Blog votes.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function getSingleData(Request $request)
    {

        try {

            $post = $request->all();

            $validate = [

                'user_id' => 'required',

            ];

            $validator = Validator::make($post, $validate);

            if ($validator->fails()) {

                $data['error'] = $validator->errors();

                return response(\Helpers::sendFailureAjaxResponse($data['error']));
            } else {

                $blogs = array();

                $header = $request->header('userData');
                if ($header != null) {
                    $user = User::where('id', $header)->first();
                    if (!isset($user->id)) {
                        return $this->sendError('Your account has been deleted by the Admin', 401);
                    }
                }

                if (isset($post['category_id']) && $post['category_id'] != '' && $post['category_id'] != 0) {

                    $blog = Blog::where('category_id', $post['category_id'])->where('schedule_date', "<=", date("Y-m-d H:i:s"))->with('blog_category')->first();

                    if ($blog) {

                        $flag = false;

                        $blogTranslate = BlogTranslation::where('blog_id', $blog->id)->where('language_code', $this->language)->first();

                        if ($blogTranslate) {

                            $flag = true;

                            $blog->title = $blogTranslate->title;

                            $blog->tags = $blogTranslate->tags;

                            $blog->description = $blogTranslate->description;

                            $blog->seo_title = $blogTranslate->seo_title;

                            $blog->seo_keyword = $blogTranslate->seo_keyword;

                            $blog->seo_tag = $blogTranslate->seo_tag;

                            $blog->seo_description = $blogTranslate->seo_description;
                        }



                        if (str_word_count($blog->description) > 62) {

                            $blog->description = substr($blog->description, 0, 420);

                            $blog->description = $blog->description . ".....";
                        }

                        $blog->trimed_description = strip_tags($blog->description);

                        $blog->trimed_description = str_replace("&nbsp;", '', $blog->trimed_description);

                        $blog->trimed_description = str_replace("&#39;", "'", $blog->trimed_description);

                        $blog->created_at = date("d M Y h:i a", strtotime($blog->created_at));

                        if ($blog->thumb_image != '') {

                            $blog->thumb_image = url('upload/blog/thumb/360/' . $blog->thumb_image);
                        } else {

                            $blog->thumb_image = url('upload/blog/thumb/default.png');
                        }

                        $check_image = BlogImages::where('blog_id', $blog->id)->pluck('image');

                        $blog_image = array();

                        if (count($check_image)) {

                            foreach ($check_image as $value) {

                                $value = url('upload/blog/banner/800/' . $value);

                                array_push($blog_image, $value);
                            }

                            $blog->banner_image = $blog_image;
                        } else {

                            $blog_image[0] = url('upload/author/default.png');

                            $blog->banner_image = $blog_image;
                        }

                        if ($header != null) {

                            $vote = Vote::where('user_id', $header)->where('blog_id', $blog->id)->first();

                            if ($vote) {

                                $blog->is_vote = 1;
                            } else {

                                $blog->is_vote = 0;
                            }

                            $bookmarked = BookMarkPost::where('user_id', $header)->where('blog_id', $blog->id)->first();

                            if ($bookmarked) {

                                $blog->is_bookmark = 1;
                            } else {

                                $blog->is_bookmark = 0;
                            }
                        } else {

                            $blog->is_vote = 0;

                            $blog->is_bookmark = 0;
                        }

                        $total_votes = Vote::where('blog_id', $blog->id)->count();

                        $yes_votes = Vote::where('blog_id', $blog->id)->where('vote', 1)->count();

                        $no_votes = Vote::where('blog_id', $blog->id)->where('vote', 0)->count();

                        $blog->view_count = BlogViewCount::where('blog_id', $blog->id)->count();

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

                        $blog->yes_percent = round($yes_percent);

                        $blog->no_percent = round($no_percent);

                        $author = Author::where('id', $blog->author_id)->first();

                        if ($author) {

                            $blog->author_name = $author->name;

                            if ($author->image != null || $author->image != '') {

                                $blog->image = url('upload/author/original/' . $author->image);
                            } else {

                                $blog->image = url('upload/author/default.png');
                            }
                        } else {

                            $blog->author_name = "";

                            $blog->image = url('upload/author/default.png');
                        }

                        $category = Category::where('id', $blog->category_id)->first();

                        if ($category) {

                            $catTranslate = CategoryTranslation::where('category_id', $category->id)->where('language_code', $this->language)->first();

                            if ($catTranslate) {

                                $category->name = $catTranslate->name;
                            }

                            $blog->category_name = $category->name;

                            $blog->color = $category->color;
                        } else {

                            $blog->category_name = "";

                            $blog->color = "";
                        }

                        $blog->time = $blog->time . " min";

                        $blog->create_date = date("d M Y // h:i a", strtotime($blog->schedule_date));
                    }
                } else {



                    $blog = Blog::where('status', 1)->where('schedule_date', "<=", date("Y-m-d H:i:s"))->orderBy('schedule_date', 'DESC')->with('blog_category')->first();

                    if ($blog) {



                        $flag = false;





                        $blogTranslate = BlogTranslation::where('blog_id', $blog->id)->where('language_code', $this->language)->first();

                        if ($blogTranslate) {

                            $flag = true;



                            $blog->title = $blogTranslate->title;

                            $blog->tags = $blogTranslate->tags;

                            $blog->description = $blogTranslate->description;

                            $blog->seo_title = $blogTranslate->seo_title;

                            $blog->seo_keyword = $blogTranslate->seo_keyword;

                            $blog->seo_tag = $blogTranslate->seo_tag;

                            $blog->seo_description = $blogTranslate->seo_description;
                        }



                        if (str_word_count($blog->description) > 62) {

                            $blog->description = substr($blog->description, 0, 420);

                            $blog->description = $blog->description . ".....";
                        }

                        $blog->trimed_description = strip_tags($blog->description);

                        $blog->trimed_description = str_replace("&nbsp;", '', $blog->trimed_description);

                        $blog->trimed_description = str_replace("&#39;", "'", $blog->trimed_description);

                        $blog->created_at = date("d M Y h:i a", strtotime($blog->created_at));

                        if ($blog->thumb_image != '') {

                            $blog->thumb_image = url('upload/blog/thumb/360/' . $blog->thumb_image);
                        } else {

                            $blog->thumb_image = url('upload/blog/thumb/default.png');
                        }

                        $check_image = BlogImages::where('blog_id', $blog->id)->pluck('image');

                        $blog_image = array();

                        if (count($check_image)) {

                            foreach ($check_image as $value) {

                                $value = url('upload/blog/banner/800/' . $value);

                                array_push($blog_image, $value);
                            }

                            $blog->banner_image = $blog_image;
                        } else {

                            $blog_image[0] = url('upload/author/default.png');

                            $blog->banner_image = $blog_image;
                        }

                        if ($header != null) {

                            $vote = Vote::where('user_id', $header)->where('blog_id', $blog->id)->first();

                            if ($vote) {

                                $blog->is_vote = 1;
                            } else {

                                $blog->is_vote = 0;
                            }

                            $bookmarked = BookMarkPost::where('user_id', $header)->where('blog_id', $blog->id)->first();

                            if ($bookmarked) {

                                $blog->is_bookmark = 1;
                            } else {

                                $blog->is_bookmark = 0;
                            }
                        } else {

                            $blog->is_vote = 0;

                            $blog->is_bookmark = 0;
                        }

                        $total_votes = Vote::where('blog_id', $blog->id)->count();

                        $yes_votes = Vote::where('blog_id', $blog->id)->where('vote', 1)->count();

                        $no_votes = Vote::where('blog_id', $blog->id)->where('vote', 0)->count();

                        $blog->view_count = BlogViewCount::where('blog_id', $blog->id)->count();

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

                        $blog->yes_percent = round($yes_percent);

                        $blog->no_percent = round($no_percent);

                        $author = Author::where('id', $blog->author_id)->first();

                        if ($author) {

                            $blog->author_name = $author->name;

                            if ($author->image != null || $author->image != '') {

                                $blog->image = url('upload/author/original/' . $author->image);
                            } else {

                                $blog->image = url('upload/author/default.png');
                            }
                        } else {

                            $blog->author_name = "";

                            $blog->image = url('upload/author/default.png');
                        }

                        $category = Category::where('id', $blog->category_id)->first();

                        if ($category) {



                            $catTranslate = CategoryTranslation::where('category_id', $category->id)->where('language_code', $this->language)->first();

                            if ($catTranslate) {

                                $category->name = $catTranslate->name;
                            }



                            $blog->category_name = $category->name;

                            $blog->color = $category->color;
                        } else {

                            $blog->category_name = "";

                            $blog->color = "";
                        }

                        $blog->time = $blog->time . " min";

                        $blog->create_date = date("d M Y // h:i a", strtotime($blog->schedule_date));
                    }
                }



                if ($flag) {

                    return $this->sendResponse($blog, __('message_alerts.record_found'));
                } else {

                    return $this->sendError(__('message_alerts.blog_not_found'), 401);
                }
            }
        } catch (\Exception $ex) {

            return $this->sendError($ex->getMessage(), 401);
        }
    }

    public function action(Request $request)
    {

        $validate = [

            'userID' => 'required|integer|exists:users,id',

            'blogID' => 'required|integer|exists:blog,id',

            'action' => 'required|boolean', //0=>for view,1=> for click

        ];

        $validatemessage = [

            'userID.required' => 'user ID required',

            'userID.integer' => 'user ID should be integer',

            'userID.exists' => 'Invalid User',

            'blogID.exists' => 'Blog Invalid',

            'blogID.required' => 'blog ID required',

            'action.required' => 'action  required',

            'action.boolean' => 'action should be boolean value',

            'blogID.integer' => 'blog id should be integer',

        ];

        $validator = Validator::make($request->all(), $validate, $validatemessage);

        if ($validator->fails()) {

            $data['error'] = $validator->errors();



            return response(\Helpers::sendFailureAjaxResponse($data['error']));
        } else {

            // if ($request->action == 0){

            //   $data =  Ads_action::where(['userID' => $request->userID, 'AdsID'=> $request->AdsID, 'action' => $request->action])->first();

            //   if ($data){

            //       return $this->sendError(__('Already Viewed'), 401);

            //   }else{

            //       $Ads_action = new Ads_action();

            //       $Ads_action->userID = $request->userID;

            //       $Ads_action->AdsID = $request->AdsID;

            //       $Ads_action->action = $request->action;

            //       $Ads_action->save();



            //   }

            // }else{

            //     $Ads_action = new Ads_action();

            //     $Ads_action->userID = $request->userID;

            //     $Ads_action->AdsID = $request->AdsID;

            //     $Ads_action->action = $request->action;

            //     $Ads_action->save();

            // }





            $blog = Blog::where('id', $request->blogID)->with('blog_category')->first();

            if ($blog) {



                if ($request->action == 0) {

                    return $this->sendError(__('Invalid action'), 401);

                    // $blog->view = $blog->view+1;

                } else {

                    $blog->view_more_click = $blog->view_more_click + 1;
                }

                $blog->save();

                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.success'), $blog));
            } else {

                return $this->sendError(__('Error'), 401);
            }
        }
    }



    public function arrayPaginator($array, $request)
    {

        $post = $request->all();

        $per_page_number = 500;

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
