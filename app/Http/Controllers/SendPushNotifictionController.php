<?php



namespace App\Http\Controllers;



use Illuminate\Http\Request;

use Validator;

use App\Models\User;

use App\Models\DeviceToken;

use App\Models\CustomNotification;
use App\Models\Notification;

class SendPushNotifictionController extends Controller
{





    function __construct()
    {

        $this->middleware('permission:show-notification-form|send-notification', ['only' => ['index', 'sendNotification']]);

        $this->middleware('permission:show-notification-form', ['only' => ['index']]);

        $this->middleware('permission:send-notification', ['only' => ['sendNotification']]);

    }







    /**

     * Show send push notification form.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */



    public function index($layout = 'side-menu', $theme = 'light')
    {



        return view('super-admin/push-notification.index', [

            'theme' => $theme,

            'page_name' => 'index',

            'side_menu' => array(),

            'layout' => $layout,

            'breadcrumb' => '<a href="' . url('/') . '" class="breadcrumb">' . trans('admin.dashboard') . '</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="' . url('/rss-feed-src/side-menu/light') . '" class="breadcrumb--active">' . trans('admin.send_notification') . '</a>'



        ]);

    }





    /**

  *  send push notification.

  *

  * @param  \Illuminate\Http\Request  $request

  * @return \Illuminate\Http\Response

  */



    public function sendNotification(Request $request)
    {

        try {
            $post = $request->all();
            $validate = [
                'send_to' => 'required',
                'title' => 'required',
            ];
      
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                $data['error'] = $validator->errors();
                return redirect()->back()->with('failure', __('message_alerts.required_field_missing'));
            } else {
                  
                if (setting('enable_notifications')) {
                    if (file_exists(public_path() . "/upload/logo/" . setting('site_logo'))) {
                        $image = url('upload/logo') . '/' . setting('site_logo');
                    } else {
                        $image = url('upload/no-image.png');
                    }
                    
                   if ($post['send_to'] == 'all') {
                        $users = User::where('active', 1)->where('is_notiifcation',1)->get();
                        $tokens = [];

                        foreach ($users as $user) {
                            if (!empty($user->fcm_token)) {
                                $tokens[] = $user->fcm_token;
                            }
                        }

                        // Now send all tokens in one call — will be chunked internally
                        if (!empty($tokens)) {

                         \Helpers::sendNotification($tokens, $post['description'], $post['title'], setting('firebase_msg_key'), $image);
                    
                        CustomNotification::create([
                            'title'=>$post['title'],
                            'desc'=>$post['description'],
                            'type'=>'All',
                        ]);
                    }

                        // $non_logged_in = DeviceToken::pluck('device_token')->toArray();
                        // if (!empty($non_logged_in)) {
                        //     \Helpers::sendNotification($non_logged_in, $post['description'], $post['title'], setting('firebase_msg_key'), $image);
                        // }

                    } else {

                        $emails = is_array($post['email']) ? $post['email'] : explode(',', $post['email']);
                        $tokens = [];
                        $userIds = [];

                        foreach ($emails as $email) {
                            $user = User::where('email', trim($email))->where('is_notiifcation',1)->first();
                            if ($user && !empty($user->fcm_token)) {
                                $tokens[] = $user->fcm_token;
                                $userIds[] = $user->id;
                            }
                        }

      

                        if (!empty($tokens)) {
                            \Helpers::sendNotification($tokens, $post['description'], $post['title'], setting('firebase_msg_key'), $image);
                        }

                        // Save notification for each specific user in `notification` table
                        foreach ($userIds as $userId) {
                            Notification::create([
                                'user_id' => $userId,
                                'title' => $post['title'],
                                'decs' => $post['description'],
                            ]);
                        }
                    }
                }
                return redirect()->back()->with('success', __('message_alerts.notification_sent'));
            }
        } catch (\Exception $ex) {

            return redirect()->back()->with('failure', $ex->getMessage());

        }

    }





    public function sendNotificationOld(Request $request)
    {

        try {

            $post = $request->all();

            $validate = [

                'send_to' => 'required',

                'title' => 'required',

            ];

            $validator = Validator::make($post, $validate);

            if ($validator->fails()) {

                $data['error'] = $validator->errors();

                return redirect()->back()->with('failure', __('message_alerts.required_field_missing'));

            } else {

                if (setting('enable_notifications')) {

                    if (file_exists(public_path() . "/upload/logo/" . setting('site_logo'))) {

                        $image = url('upload/logo') . '/' . setting('site_logo');

                    } else {

                        $image = url('upload/no-image.png');

                    }

                    if ($post['send_to'] == 'all') {

                        $user = User::where('active', 1)->get();



                        foreach ($user as $detail) {

                            if ($detail->device_token != null) {

                                \Helpers::sendNotification($detail->device_token, $post['description'], $post['title'], setting('firebase_msg_key'), $image);

                            }

                        }

                        $non_logged_in = DeviceToken::get();

                        if (count($non_logged_in)) {

                            foreach ($non_logged_in as $non_logged_in_data) {

                                if ($non_logged_in_data->device_token != null) {

                                    \Helpers::sendNotification($non_logged_in_data->device_token, $post['description'], $post['title'], setting('firebase_msg_key'), $image);

                                }

                            }

                        }

                    } else {

                        $post['email'] = explode(',', $post['email']);

                        for ($c = 0; $c < count($post['email']); $c++) {

                            $user = User::where('email', $post['email'])->first();

                            if ($user && $user->device_token != null) {

                                \Helpers::sendNotification($user->device_token, $post['description'], $post['title'], setting('firebase_msg_key'), $image);

                            }





                        }



                    }

                }

                return redirect()->back()->with('success', __('message_alerts.notification_sent'));

            }



        } catch (\Exception $ex) {

            return redirect()->back()->with('failure', $ex->getMessage());

        }

    }



    /**

     * Show the form for creating a new resource.

     *

     * @return \Illuminate\Http\Response

     */

    public function postAutocomplete(Request $request)
    {

        $post = $request->all();

        $data = User::select("email")

            ->where("email", "LIKE", "%{$request->term}%")

            ->get();



        /*$data = User::select("email")

                ->where("email","LIKE","%{$request['query']}%")

                ->get();*/

        return response()->json($data);

    }

}

