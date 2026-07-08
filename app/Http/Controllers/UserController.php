<?php



namespace App\Http\Controllers;



use App\Models\UserFeed;

use Illuminate\Http\Request;

use App\Models\User;

use Notification;
use Spatie\Permission\Models\Role;

use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;

use Auth;

use Illuminate\Support\Facades\Artisan;

use App\Models\Permission;



class UserController extends Controller
{



    function __construct()
    {

        $this->middleware('permission:user-list|user-delete|user-status|user-personalization', ['only' => ['index', 'deleteUser', 'deleteMultipleUser', 'changeUserStatus', 'personalizationUser']]);

        $this->middleware('permission:user-delete', ['only' => ['deleteUser']]);

        $this->middleware('permission:user-status', ['only' => ['changeUserStatus']]);

        $this->middleware('permission:user-personalization', ['only' => ['personalizationUser']]);

    }



    /**

     * Show User view.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function index(Request $request, $layout = 'side-menu', $theme = 'light')
    {

        $User = User::getAllUsers($request->all());

        $page = isset ($_GET['page']) ? $_GET['page'] : 1;

        $per_page = isset ($_GET['per_page']) ? $_GET['per_page'] : 10; // here 20 is which is default per page value

        $i = ($page == 1) ? 1 : (($page - 1) * $per_page + 1);

        return view('super-admin/user.index', [

            'theme' => $theme,

            'page_name' => 'index',

            'side_menu' => array(),

            'layout' => $layout,

            'user' => $User,

            'i' => $i,

            'breadcrumb' => '<a href="' . url('/') . '" class="breadcrumb">' . trans("admin.dashboard") . '</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="' . url('/users/side-menu/light') . '" class="breadcrumb--active">Users List</a>'



        ]);

    }



    /**

     * Show User view.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function profile($layout = 'side-menu', $theme = 'light')
    {

        $user = Auth::user();

        return view('profile.edit', [

            'theme' => $theme,

            'page_name' => 'edit',

            'side_menu' => array(),

            'layout' => $layout,

            'user' => $user,

            'breadcrumb' => '<a href="' . url('/') . '" class="breadcrumb">' . trans("admin.dashboard") . '</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="' . url('/profile/side-menu/light') . '" class="breadcrumb--active">' . trans("admin.admin_profile") . '</a>'

        ]);

    }



    /**

     * Method to delete category

     * @param array $request post data, id

    */

    public function deleteUser(Request $request, $id)
    {

        User::where('id', $id)->delete();

        return back()->with('success', __('message_alerts.record_deleted'));

    }

    /**
     * Delete multiple users
     */
    public function deleteMultipleUser(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', __('message_alerts.select_record'));
        }

        User::whereIn('id', $ids)->delete();

        return back()->with('success', __('message_alerts.record_deleted'));
    }



    public function personalizationUser(Request $request, $id, $layout = 'side-menu', $theme = 'light')
    {

        $users['personalization'] = UserFeed::where('user_id', $id)->with('categoryData')->get()->toArray();

        //        dd($users['personalization']);

        $side_menu = array();

        return view('super-admin.user.data', compact('users', 'layout', 'side_menu', 'theme'));

    }



    /**

     * Method to change status of category

     * @param array $request post data ,id ,status

    */

    public function changeUserStatus(Request $request, $id, $status)
    {

        $post['active'] = $status;

        $post['id'] = $id;

        User::updateUser($post);

        return back()->with('success', __('message_alerts.status_changed_success'));

    }



    public function uploadProfileImage(Request $request)
    {

        try {

            if ($request->ajax()) {

                $post = $request->all();

                $name = '';

                if ($post['image'] != '') {

                    $file = $request->file('image');

                    $name = time() . rand() . '.' . $file->getClientOriginalExtension();

                    $destination = public_path('/upload/user/') . $name;

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

    public function editProfile(Request $request)
    {

        $post = $request->all();

        if (!empty ($post)) {

            if (!env('APP_DEMO', false)) {

                if ($post['photo'] == null) {

                    unset($post['photo']);

                }

                unset($post['photo_img']);

                unset($post['_token']);

                if ($post['password'] == null) {

                    unset($post['password']);

                } else {

                    $post['password'] = bcrypt($post['password']);

                }

                $id = User::where('id', $post['id'])->update($post);

                return back()->with('success', __('message_alerts.record_updated'));

            } else {

                return back()->with('failure', __('message_alerts.demo_app_warning'));

            }

        } else {

            return back()->with('failure', __('message_alerts.unable_change_try_again'));

        }

    }



    /**give Permission To all Admin

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function givePermissionToAdmin()
    {



        // \Artisan::call('migrate');

        // \Artisan::call('db:seed --class=PermissionTableSeeder');

        $permissions = [

            'role-list',

            'role-create',

            'role-edit',

            'role-delete',

            'feed-item-list',

            'feed-item-save-post',

            'blog-list',

            'blog-create',

            'blog-edit',

            'blog-delete',

            'ads-list',

            'ads-create',
            
            'news-ads-list',
            
            'news-ads-create',

            'rss-feed-list',

            'rss-feed-create',

            'rss-feed-edit',

            'rss-feed-delete',

            'cms-pages-list',

            'cms-pages-edit',

            'user-list',

            'user-delete',

            'user-edit',

            'sub-admin-list',

            'sub-admin-create',

            'sub-admin-edit',

            'sub-admin-delete',

            'search-log-list',

            'setting-list',

            'setting-edit',

            'category-list',

            'category-create',

            'category-edit',

            'category-delete',

            'author-list',

            'author-create',

            'author-edit',

            'author-delete',

            'news-api-post-list',

            'news-api-post-save',

            'live-news-list',

            'live-news-create',

            'live-news-edit',

            'live-news-delete',

            'epaper-list',

            'epaper-create',

            'epaper-edit',

            'epaper-delete',

            'show-notification-form',

            'send-notification',

            'quote-list',

            'quote-create',

            'quote-edit',

            'quote-delete',

        ];

 return dd($permissions);

        for ($i = 0; $i < count($permissions); $i++) {

            $exist = Permission::where('name', $permissions[$i])->first();

            if (!$exist) {

                Permission::insertGetId(['name' => $permissions[$i], 'guard_name' => 'web']);

            }

        }



        $per = Permission::pluck('id')->toArray();

        $role = Role::find(3);

        $role->name = 'admin';

        $role->save();

        $role->syncPermissions($per);

        echo 'permission granted !!!';
        die;

    }
}

