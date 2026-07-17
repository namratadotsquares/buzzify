<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Walletproduct;
use App\Models\Wallet;
use App\Models\SiteContent;
use App\Models\Story;
use App\Models\Category;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogImages;
use App\Models\BlogTranslation;
use App\Models\ProductRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\Languages;
use App\Models\EpaperTranslation;
use Illuminate\Support\Facades\Gate;
use Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class WalletController extends Controller
{

    // function __construct()
    // {
    //     // Wallet products
    //     $this->middleware('permission:wallet-product-list', ['only' => ['index']]);
    //     $this->middleware('permission:wallet-product-create|wallet-product-edit', ['only' => ['addUpdate', 'uploadLogo', 'uploadPdf']]);
    //     $this->middleware('permission:wallet-product-delete', ['only' => ['deleteProduct']]);
    //     $this->middleware('permission:wallet-product-status', ['only' => ['changeStatus']]);

    //     // Redeem / product requests
    //     $this->middleware('permission:redeem-request-list', ['only' => ['list', 'letest_list']]);
    //     $this->middleware('permission:redeem-request-edit|redeem-request-status', ['only' => ['reqUpdate']]);

    //     // User stories moderation
    //     $this->middleware('permission:user-story-list', ['only' => ['story', 'storyletest', 'view']]);
    //     $this->middleware('permission:user-story-status', ['only' => ['changestoryStatus']]);
    //     $this->middleware('permission:user-story-delete', ['only' => ['deleteEpaper']]);
    // }

    /**
     * Show Category view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function letest_list(Request $request, $layout = 'side-menu', $theme = 'light')
    {
        $news = ProductRequest::getlatestproduct($request->all());
        $languages = Languages::get();

        return view('super-admin/ProductRequest.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'languages' => $languages,
            'News' => $news,
            'breadcrumb' => '<a href="' . url('/') . '" class="breadcrumb">Dashboard</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="' . url('/category/side-menu/light') . '" class="breadcrumb--active">Request List</a>'

        ]);
    }
    public function list(Request $request, $layout = 'side-menu', $theme = 'light')
    {

        $news = ProductRequest::getAllproduct($request->all());

        $languages = Languages::get();

        return view('super-admin/ProductRequest.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'languages' => $languages,
            'News' => $news,
            'breadcrumb' => '<a href="' . url('/') . '" class="breadcrumb">Dashboard</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="' . url('/category/side-menu/light') . '" class="breadcrumb--active">Request List</a>'

        ]);
    }

    public function index(Request $request, $layout = 'side-menu', $theme = 'light')
    {
        $news = Walletproduct::getAllproduct($request->all());
        $languages = Languages::get();
        return view('super-admin/product.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'languages' => $languages,
            'News' => $news,
            'breadcrumb' => '<a href="' . url('/') . '" class="breadcrumb">Dashboard</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="' . url('/category/side-menu/light') . '" class="breadcrumb--active">Product List</a>'
        ]);
    }

    public function story(Request $request, $layout = 'side-menu', $theme = 'light')
    {
        $news = Story::getAllproduct($request->all());

        $languages = Languages::get();
        return view('super-admin/story.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'languages' => $languages,
            'News' => $news,
            'breadcrumb' => '<a href="' . url('/') . '" class="breadcrumb">Dashboard</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="' . url('/category/side-menu/light') . '" class="breadcrumb--active">Product List</a>'
        ]);
    }

    public function storyletest(Request $request, $layout = 'side-menu', $theme = 'light')
    {
        $news = Story::getletestproduct($request->all());
        $languages = Languages::get();

        return view('super-admin/story.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'languages' => $languages,
            'News' => $news,
            'breadcrumb' => '<a href="' . url('/') . '" class="breadcrumb">Dashboard</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="' . url('/category/side-menu/light') . '" class="breadcrumb--active">Product List</a>'

        ]);
    }

    /**
     * Show Category view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reqUpdate(Request $request)
    {
        $post = $request->all();
        if ($post['status'] == 2) {
            $post['product_code'] = null;
            $post['point'] = null;
        }

        if (!empty($post)) {
            $post['updated_at'] = date('Y-m-d H:i:s');
            $id = ProductRequest::editProductRequest($post);
            $msg = __('message_alerts.record_updated');
            return array('success' => true, 'data' => $id, 'message' => $msg);
        } else {
            return array('success' => false, 'data' => null, 'message' => __('message_alerts.something_went_wrong'));
        }
    }

    public function addUpdate(Request $request)
    {
        $post = $request->all();
        if (!empty($post)) {
            if (isset($post['thumb_image']) && $post['thumb_image'] != '') {
                $post['img'] = $post['thumb_image'];
                unset($post['thumb_image']);
            }

            if (!isset($post['id'])) {
                if (Gate::check('wallet-product-create')) {
                    $post['created_at'] = date('Y-m-d h:i:s');
                    $id = Walletproduct::addEpaper($post);
                    $msg = __('message_alerts.record_inserted');
                } else {
                    return response(\Helpers::sendFailureAjaxResponse('User does not have a right permission.'));
                }
            } else {
                if (Gate::check('wallet-product-edit')) {
                    $post['updated_at'] = date('Y-m-d h:i:s');
                    $id = Walletproduct::updateEpaper($post);
                    $msg = __('message_alerts.record_updated');
                } else {
                    return response(\Helpers::sendFailureAjaxResponse('User does not have a right permission.'));
                }
            }
            return array('success' => true, 'data' => $id, 'message' => $msg);
        } else {
            return array('success' => false, 'data' => null, 'message' => __('message_alerts.something_went_wrong'));
        }
    }


    /**
     * upload category thumb image
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    public function uploadLogo(Request $request)
    {
        try {
            if ($request->ajax()) {
                $post = $request->all();
                $name = '';
                if ($post['image'] != '') {
                    $file = $request->file('image');
                    $name = time() . rand() . '.' . $file->getClientOriginalExtension();
                    $destination = public_path('/upload/e-paper/original/') . $name;
                    $c = \Helpers::compress_image($file, $destination, 30);
                }

                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'), $name));
            } else {
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }

    /**
     * Method to delete category
     * @param array $request post data, id
     */
    public function deleteEpaper(Request $request, $id)
    {
        Story::where('id', $id)->delete();
        $blog = Blog::where('story_status', $id)->get();

        foreach ($blog as $blogs) {
            $blogImage = BlogImages::where('blog_id', $blogs->id)->get();
            foreach ($blogImage as $blogImages) {
                $imageUrl = 'upload/blog/banner/360/' . $blogImages->image;
                $fullImagePath = public_path($imageUrl);

                if (file_exists($fullImagePath)) {
                    unlink($fullImagePath);
                }
                $deleteImage = BlogImages::find($blogImages->id);
                $deleteImage->delete();
            }
            $deleteblog = Blog::find($blogs->id);
            $deleteblog->delete();
        }
        return back()->with('success', 'Stories deleted successfully.');
    }

    public function deleteProduct(Request $request, $id)
    {
       Walletproduct::where('id', $id)->delete();
        return back()->with('success', __('message_alerts.record_deleted'));
    }
    /**
     * Method to change status of category
     * @param array $request post data ,id ,status
     */
    public function changeStatus(Request $request, $id, $status)
    {
        $post['status'] = $status;
        $post['id'] = $id;
        Walletproduct::updateEpaper($post);
        return back()->with('success', __('message_alerts.status_changed_success'));
    }
    public function changestoryStatus(Request $request, $id, $status, $user_id)
    {
        $wallet_value = SiteContent::find(61);
        $wallet_expiry = SiteContent::find(62);
        $wallet_expiry_range = SiteContent::find(63);

        if (isset($request->reward_points) && !empty($request->reward_points)) {
            $postwallet_extra['user_id'] = $user_id;
            $postwallet_extra['story_id'] = $id;
            $postwallet_extra['point'] = $request->reward_points;
        }


        $post['status'] = $status;
        $post['id'] = $id;
        if ($status == 1) {
            $selectedMedia = (string) $request->input('selected_media', '');

            $postwallet['user_id'] = $user_id;
            $postwallet['story_id'] = $id;
            $postwallet['point'] = $wallet_value->value;

            if ($wallet_expiry->value == 1) {
                $currentDate = date('Y-m-d');
                $futureDate = date('Y-m-d', strtotime($currentDate . ' +' . $wallet_expiry_range->value . ' days'));
                $postwallet['range_date'] = $futureDate;
                $postwallet_extra['range_date'] = $futureDate;
            }

            /* Save Story as Blog start */
            $story = Story::where('id', $post['id'])->where('user_id', $user_id)->first();
            if ($story && $selectedMedia !== '') {
                $files = [];
                if (isset($story->files) && is_array($story->files)) {
                    $files = $story->files;
                } elseif ($story->file) {
                    $files = [$story->file];
                }

                $files = array_values(array_unique(array_filter($files, function ($v) {
                    return is_string($v) && $v !== '';
                })));

                $selectedIndex = array_search($selectedMedia, $files, true);
                if ($selectedIndex !== false) {
                    unset($files[$selectedIndex]);
                    array_unshift($files, $selectedMedia);
                    $files = array_values($files);

                    $story->setAttribute('file', count($files) === 1 ? $files[0] : json_encode($files));
                    $story->save();
                }
            }

            $file = $story->file;
            
            // Check if $file is a JSON array and decode it to get the first file
            if (is_string($file) && Str::startsWith(trim($file), '[')) {
                $decodedFiles = json_decode($file, true);
                if (is_array($decodedFiles) && count($decodedFiles) > 0) {
                    $file = $decodedFiles[0];
                }
            }

            $fileType = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                $storyti = strip_tags($story->story);
                $storySlug = substr(($storyti), 0, 50);
                $storytitle = substr(($storyti), 0, 65);
                $slug = \Helpers::createSlug($storySlug, 'blog', 0, false);
                $blog = new Blog();
                $blog['slug'] = $slug;
                $blog['title'] = $storytitle;
                $blog['description'] = $story->story;
                // $blog['url'] = $story->file;
                $blog['banner_image'] = null;
                $blog['category_id'] = 12;
                $blog['created_at'] = date('Y-m-d H:i:s');
                //$blog['post_id'] = null;
                $blog['story_status'] = (int) $id;
                $blog['status'] = 2; // Save as Draft so it appears in buzz but as draft status
                $blog['created_by'] = $user_id;
                $blog['schedule_date'] = null;
                $blId = $blog->save();

                /* Copy the banner save in 360 folder */
                $url = $story->file;
                $imageName = basename($url);
                $sourcePath = public_path("upload/story/{$imageName}");
                $destinationPath = public_path("upload/blog/banner/360/{$imageName}");
                File::copy($sourcePath, $destinationPath);

                /* Copy the banner save in original folder */
                $origUrl = $story->file;
                $origImageName = basename($origUrl);
                $origsourcePath = public_path("upload/story/{$origImageName}");
                $origdestinationPath = public_path("upload/blog/banner/original/{$origImageName}");
                File::copy($origsourcePath, $origdestinationPath);

                /* Copy the banner save in 800 folder */
                $frontImageUrl = $story->file;
                $frontShowImage = basename($frontImageUrl);
                $frontsourcePath = public_path("upload/story/{$frontShowImage}");
                $frontdestinationPath = public_path("upload/blog/banner/800/{$frontShowImage}");
                File::copy($frontsourcePath, $frontdestinationPath);

                if ($blog->id) {
                    $blId = $blog->id;
                    $blogCat = new BlogCategory();
                    $blogCat['category_id'] = 12;
                    $blogCat['blog_id'] = $blId;
                    $blogCat->save();

                    $blogTrans = new BlogTranslation();
                    $blogTrans['language_code'] = 'en';
                    $blogTrans['blog_id'] = $blId;
                    $blogTrans['title'] = $storytitle;
                    $blogTrans['description'] = $story->story;
                    $blogTrans->save();

                    $blogImgs = new BlogImages();
                    $blogImgs['image'] = $imageName;
                    $blogImgs['blog_id'] = $blId;
                    $blogImgs->save();
                } else {
                }
            }

            /* Save Story as Blog end */
            $totalStoriesByUser = Story::where('user_id', $user_id)->where('status', 1)->where('reward_id', 0)->count();
            $isRewardEligible = ($totalStoriesByUser == 2) ? true : false;
            if ($isRewardEligible) {
                $wallet_id = Wallet::addEpaper($postwallet);
                Story::where('user_id', $user_id)->where('status', 1)->where('reward_id', 0)
                    ->update(['reward_id' => $wallet_id]);
                Story::where('id', $id)->where('user_id', $user_id)->where('reward_id', 0)
                    ->update(['reward_id' => $wallet_id]);
            }
            if (isset($request->reward_points) && !empty($request->reward_points)) {
                $wallet_id = Wallet::addEpaper($postwallet_extra);
            }
        }
        Story::updateEpaper($post);
        if ($status == 1) {
            
            // Send push notification and in-app notification to the user
            // USER REQUESTED NO NOTIFICATION ON ACCEPT/DRAFT STAGE
            /*
            $user = \App\Models\User::find($user_id);
            if ($user) {
                $tokens = [];
                if (!empty($user->fcm_token)) {
                    $tokens[] = $user->fcm_token;
                } elseif (!empty($user->device_token)) {
                    $tokens[] = $user->device_token;
                }
                
                $notiTitle = "Story Published";
                $notiDesc = "Your story has been approved and published in the buzz section.";
                
                if (!empty($tokens)) {
                    $image = '';
                    if (file_exists(public_path() . "/upload/logo/" . setting('site_logo'))) {
                        $image = url('upload/logo') . '/' . setting('site_logo');
                    } else {
                        $image = url('upload/no-image.png');
                    }
                    \Helpers::sendNotification($tokens, $notiDesc, $notiTitle, setting('firebase_msg_key'), $image, $blId ?? null);
                }
                
                \App\Models\Notification::create([
                    'user_id' => $user_id,
                    'title' => $notiTitle,
                    'decs' => $notiDesc,
                ]);

                \App\Models\CustomNotification::create([
                    'user_id' => $user_id,
                    'title' => $notiTitle,
                    'desc' => $notiDesc,
                    'post_id' => $blId ?? null,
                    'type' => 'User',
                ]);
            }
            */

            return response()->json([
                'status' => 'success',
                'message' => __('message_alerts.status_changed_success'),
                'redirect_url' => route('list_stories', [$request->layout, $request->theme]) // replace with your actual route name
            ]);
        } else {
            return back()->with('success', __('message_alerts.status_changed_success'));
        }
    }

    public function uploadPdf(Request $request)
    {
        try {
            if ($request->ajax()) {
                $post = $request->all();
                $name = '';
                if ($post['upload_file'] != '') {
                    $file = $request->file('upload_file');
                    $name = time() . rand() . '.' . $file->getClientOriginalExtension();
                    $destination = public_path('/upload/e-paper/pdf/') . $name;
                    $file->move(public_path('/upload/e-paper/pdf/'), $name);
                }
                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'), $name));
            } else {
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }
    public function view(Request $request, $id, $layout = 'side-menu', $theme = 'light')
    {
        //  dd($id);

        $story = Story::where('id', $id)->first();
        $wallet_value = SiteContent::find(61);
        $wallet_stories = SiteContent::find(65);
        $wallet_stories_count = isset($wallet_stories->value) ? $wallet_stories->value : 500;
        $stories_count_check = $wallet_stories_count - 1;
        $amount = isset($wallet_value->value) ? $wallet_value->value : 500;
        $analytics['Detail'] = $story;
        $userId = $story->user_id;

        $totalStoriesByUser = Story::where('user_id', $userId)->where('status', 1)->where('reward_id', 0)->count();

        // Check if this is the 3rd, 6th, 9th, etc. story
        $isRewardEligible = ($totalStoriesByUser == $stories_count_check) ? true : false;
        $theme = $request->segment(3);
        $wallet_stories_count = $this->addOrdinalSuffix($wallet_stories_count);
        return view('super-admin.story.analytics', compact('analytics', 'layout', 'theme', 'isRewardEligible', 'amount', 'wallet_stories_count'));
    }
    public function addOrdinalSuffix($number)
    {
        if (!in_array(($number % 100), [11, 12, 13])) {
            switch ($number % 10) {
                case 1:
                    return $number . 'st';
                case 2:
                    return $number . 'nd';
                case 3:
                    return $number . 'rd';
            }
        }
        return $number . 'th';
    }
}
