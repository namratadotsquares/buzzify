<?php



namespace App\Http\Controllers;



use Illuminate\Http\Request;

use App\Models\SiteContent;

use App\Models\Social;

use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;

use Spatie\Permission\Models\Role;

use Spatie\Permission\Models\Permission;

use anlutro\LaravelSettings\Facade as Setting;

use Illuminate\Support\Facades\Session;

use Illuminate\Support\Facades\Cookie;

use App\Models\Languages;

use UploadImage as Image;



class SiteContentController extends Controller
{

    function __construct()
    {

        $this->middleware('permission:setting-list|setting-update', ['only' => ['index', 'updateSetting']]);

        $this->middleware('permission:setting-update', ['only' => ['updateSetting']]);

    }







    /**

     * Show Category view.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function index(Request $request, $layout = 'side-menu', $theme = 'light', $page=0)
    {

        $data = SiteContent::get();
        
        // return dd($data);

        if ($page == 'site-setting') {

            $title = trans("admin.all_site_setting");

        } else if ($page == 'global') {

            $title = trans("admin.global_setting");

        } else if ($page == 'local') {

            $title = trans("admin.local_setting");

        } else if ($page == 'notification') {

            $title = trans("admin.notification_setting");

        } else if ($page == 'social') {

            $title = trans("admin.social");

        } else if ($page == 'permission') {

            $title = trans("admin.permission");

        } else if ($page == 'font-setting') {

            $title = trans("admin.font_setting");

        } else if ($page == 'live-News&E-News') {

            $title = 'Live News & E-News Settings';

        } else if ($page == 'admob') {

            $title = 'Admob Settings';

        } else if ($page == 'fb_ad_settings') {

            $title = 'Facebook Ad Settings';

        } else if ($page == 'wallet_setting') {

            $title = 'Wallet Settings';

        } else if ($page == 'location_setting') {

            $title = 'Location Settings';

        } else if ($page == 'app_settings') {

            $title = 'App Settings';

        }else if ($page == 'news_deletion') {

            $title = 'News Deletion';

        }
        else {

            $title = trans("admin.no_setting");

        }

        $voice_accent = config('constant.voice_accent');



        $zones = timezone_identifiers_list();

        if ($page == 'social') {

            $category = Social::getAllSocial($request->all());

            return view('super-admin/settings.social', [

                'theme' => $theme,

                'page_name' => 'index',

                'side_menu' => array(),

                'layout' => $layout,

                'category' => $category,

                'voice_accent' => $voice_accent,

                'title' => $title,

                'breadcrumb' => '<a href="' . url('/') . '" class="breadcrumb">' . trans("admin.dashboard") . '</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="' . url('/category/side-menu/light') . '" class="breadcrumb--active">Social List</a>'

            ]);

        } else if ($page == 'permission') {

            $roles = Role::all();

            $permissions = Permission::all();

            return view('super-admin/settings.index', [

                'theme' => $theme,

                'page_name' => 'index',

                'layout' => $layout,

                'page' => $page,

                'permissions' => $permissions,

                'voice_accent' => $voice_accent,

                'roles' => $roles,

                'title' => $title,

                'breadcrumb' => '<a href="' . url('/') . '" class="breadcrumb">' . trans("admin.dashboard") . '</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="' . url('/setting/') . '/' . $page . '" class="breadcrumb--active">' . $title . '</a>'

            ]);

        } else {

            $languages = Languages::get();

            $font_family = config('constant.font_family');



            return view('super-admin/settings.index', [

                'theme' => $theme,

                'page_name' => 'index',

                'layout' => $layout,

                'page' => $page,

                'voice_accent' => $voice_accent,

                'title' => $title,

                'data' => $data,

                'languages' => $languages,

                'font_family' => $font_family,

                'zones' => $zones,

                'breadcrumb' => '<a href="' . url('/') . '" class="breadcrumb">' . trans("admin.dashboard") . '</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="' . url('/setting/') . '/' . $page . '" class="breadcrumb--active">' . $title . '</a>'

            ]);

        }

    }



    public function uploadLogoImage(Request $request)
    {

        try {

            if ($request->ajax()) {

                $post = $request->all();

                $name = '';

                if ($post['image'] != '') {

                    $file = $request->file('image');

                    $name = time() . rand() . '.' . $file->getClientOriginalExtension();

                    //$destination =  public_path('/upload/logo/').$name;

                    $basePath = public_path('/upload/logo/');

                    $file->move($basePath, $name);



                    //$c = \Helpers::compress_image($file,$destination,30);

                }

                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'), $name));

            } else {

                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));

            }

        } catch (\Exception $ex) {

            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));

        }

    }



    public function uploadLogoFavicon(Request $request)
    {

        try {

            if ($request->ajax()) {

                $post = $request->all();

                $name = '';

                if ($post['image'] != '') {

                    $file = $request->file('image');

                    $name = time() . rand() . '.' . $file->getClientOriginalExtension();

                    //$destination =  public_path('/upload/logo/').$name;

                    $basePath = public_path('/upload/favicon/');

                    $file->move($basePath, $name);



                    //$c = \Helpers::compress_image($file,$destination,30);

                }

                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'), $name));

            } else {

                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));

            }

        } catch (\Exception $ex) {

            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));

        }

    }



    public function uploadBGImage(Request $request)
    {

        try {

            if ($request->ajax()) {

                $post = $request->all();

                $name = '';

                if ($post['image'] != '') {

                    $file = $request->file('image');

                    $name = time() . rand() . '.' . $file->getClientOriginalExtension();

                    $destination = public_path('/upload/bg/') . $name;

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



    public function uploadLiveNewsLogo(Request $request)
    {

        try {

            if ($request->ajax()) {

                $post = $request->all();

                $name = '';

                if ($post['image'] != '') {

                    $file = $request->file('image');

                    $name = time() . rand() . '.' . $file->getClientOriginalExtension();

                    $destination = public_path('/upload/live-news-logo/') . $name;

                    $basePath = public_path('/upload/live-news-logo/');

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



    public function uploadEpaperLogo(Request $request)
    {

        try {

            if ($request->ajax()) {

                $post = $request->all();

                $name = '';

                if ($post['image'] != '') {

                    $file = $request->file('image');

                    $name = time() . rand() . '.' . $file->getClientOriginalExtension();

                    $destination = public_path('/upload/e-paper/') . $name;

                    $basePath = public_path('/upload/e-paper/');

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



    /**Update setting

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function updateSetting(Request $request)
    {

        $post = $request->all();

        if (!empty($post)) {

            if (isset($post['page_name'])) {

                $page = $post['page_name'];

                unset($post['page_name']);

            }

            if (!env('APP_DEMO', false)) {

                if (!isset($post['bg_image'])) {

                    unset($post['bg_image']);

                }

                if (!isset($post['app_logo'])) {

                    unset($post['app_logo']);

                }



                if (!isset($post['live_news_logo'])) {

                    unset($post['live_news_logo']);

                }

                if (!isset($post['e_paper_logo'])) {

                    unset($post['e_paper_logo']);

                }



                if (isset($post['live_news_status'])) {

                    $post['live_news_status'] = 1;

                } else {

                    $post['live_news_status'] = 0;

                }



                if (isset($post['e_paper_status'])) {

                    $post['e_paper_status'] = 1;

                } else {

                    $post['e_paper_status'] = 0;

                }





                if (!isset($post['splash_image'])) {

                    unset($post['splash_image']);

                }

                if (isset($page) && $page == 'notification') {

                    if (isset($post['enable_notifications'])) {

                        if ($post['enable_notifications'] == 'on') {

                            $post['enable_notifications'] = 1;

                        } else {

                            $post['enable_notifications'] = $post['enable_notifications'];

                        }

                    } else {

                        $post['enable_notifications'] = 0;

                    }

                }

                if (isset($page) && $page == 'admob') {

                    if (isset($post['enable_ads'])) {

                        if ($post['enable_ads'] == 'on') {

                            $post['enable_ads'] = 1;

                        } else {

                            $post['enable_ads'] = $post['enable_ads'];

                        }

                    } else {

                        $post['enable_ads'] = 0;

                    }

                }



                if (isset($post['fb_share'])) {

                    $post['fb_share'] = 1;

                } else {

                    $post['fb_share'] = 0;

                }



                if (isset($post['twitter_share'])) {

                    $post['twitter_share'] = 1;

                } else {

                    $post['twitter_share'] = 0;

                }



                if (isset($post['instagram_share'])) {

                    $post['instagram_share'] = 1;

                } else {

                    $post['instagram_share'] = 0;

                }



                if (!isset($post['site_logo'])) {

                    unset($post['site_logo']);

                }



                if (!isset($post['site_seo_image'])) {

                    unset($post['site_seo_image']);

                }



                unset($post['_token']);

                foreach ($post as $key => $value) {

                    $exist = SiteContent::where('key', $key)->first();

                    if ($exist) {

                        $id = SiteContent::where('id', $exist->id)->update(array('value' => $value));

                    } else {

                        SiteContent::insert(array('key' => $key, 'value' => $value));

                    }



                    if ($key == 'preferred_site_language') {

                        \Artisan::call('env:set locale ' . $value);

                    }



                }



                $settingsc = SiteContent::all();

                foreach ($settingsc as $row) {

                    Setting::set($row->key, $row->value);

                }

                Setting::save();



                if (isset($post['preferred_site_language'])) {

                    Session::put('locale', $post['preferred_site_language']);

                    // setcookie('lang_code',$post['preferred_site_language'],time()+60*60*24*365);



                }



                return back()->with('success', __('message_alerts.record_updated'));

            } else {

                return back()->with('failure', __('message_alerts.demo_app_warning'));

            }

        } else {

            return back()->with('failure', __('message_alerts.unable_change_try_again'));

        }

    }



    /**Update setting

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function settingPermission(Request $request)
    {

        $post = $request->all();

        if (!empty($post)) {

            for ($i = 0; $i < count($post['permission_id']); $i++) {

                $rolePermissions = DB::table("role_has_permissions")->where('role_id', $post['role_id'])->where('permission_id', $post['permission_id'][$i])->first();

                $role = Role::create(['name' => 'admin']);

                $role->givePermissionTo('dashboard');



            }

            return back()->with('success', __('message_alerts.record_updated'));

        } else {

            return back()->with('failure', __('message_alerts.unable_change_try_again'));

        }

    }



    /**

     * Show Category view.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function addUpdateSocial(Request $request)
    {

        $post = $request->all();

        if (!empty($post)) {

            if (isset($post['thumb_image']) && $post['thumb_image'] != '') {

                $post['image'] = $post['thumb_image'];

                unset($post['thumb_image']);

            }

            if (!isset($post['id'])) {

                $post['created_at'] = date('Y-m-d h:i:s');

                $id = Social::addSocial($post);

                $msg = __('message_alerts.record_inserted');

            } else {

                $post['updated_at'] = date('Y-m-d h:i:s');

                $id = Social::updateSocial($post);

                $msg = __('message_alerts.record_updated');

            }

            return array('success' => true, 'data' => $id, 'message' => $msg);

        } else {

            return array('success' => false, 'data' => null, 'message' => __('message_alerts.something_went_wrong'));

        }

    }



    public function uploadSocialImage(Request $request)
    {

        try {

            if ($request->ajax()) {

                $post = $request->all();

                $name = '';

                if ($post['image'] != '') {

                    $file = $request->file('image');

                    $name = time() . rand() . '.' . $file->getClientOriginalExtension();

                    $destination = public_path('/upload/social/') . $name;

                    $basePath = public_path('/upload/social/');

                    if (!file_exists($basePath)) {

                        mkdir($basePath, 0777, true);

                    }

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

    public function deleteSocial(Request $request, $id)
    {

        Social::where('id', $id)->delete();

        return back()->with('success', __('message_alerts.record_deleted'));

    }



    /**

     * Method to change status of category

     * @param array $request post data ,id ,status

    */

    public function changeSocialStatus(Request $request, $id, $status)
    {

        $post['status'] = $status;

        $post['id'] = $id;

        Social::updateSocial($post);

        return back()->with('success', __('message_alerts.status_changed_success'));

    }

}

