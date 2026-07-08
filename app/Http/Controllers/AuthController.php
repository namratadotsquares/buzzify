<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Response;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;
use Spatie\Permission\Models\Role;
use App\Permission;

class AuthController extends Controller
{
    /**
     * Show specified view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loginView()
    {
        return view('login/main', [
            'theme' => 'light',
            'page_name' => 'auth-login',
            'layout' => 'login'
        ]);
    }

    /**
     * Authenticate login user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email',$request->email)->first();
        if($user){

            if (!$user->active) {
                return Response::json(['status' => false, 'message' => __('Your account has been suspended.'),'data'=>[]], '200');
            }

            if (!\Auth::attempt([
                'email' => $request->email,
                'password' => $request->password
            ])) {
                // throw new \Exception(__('message_alerts.use_valid_email_password'));

                return Response::json(['status' => false, 'message' => __('message_alerts.use_valid_email_password'),'data'=>[]], '200');
            }else{
                if(User::isAdminSubadmin(Auth::user()->id,Auth::user()->role_id)){
                    Auth::loginUsingId(Auth::user()->id);
                     return Response::json(['status' => true, 'message' => '','data'=>[]], '200');
                }
                else{
                    return Response::json(['status' => false, 'message' => __('message_alerts.use_valid_email_password'),'data'=>[]], '200');
                }
            }

        }else{
            return Response::json(['status' => false, 'message' => __('message_alerts.credential_not_found'),'data'=>[]], '200');
        }
    }

    // /**
    //  * Authenticate login user.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\Response
    //  */
    // public function login(LoginRequest $request)
    // {
    //     $user = User::where('email',$request->email)->first();
    //     if($user){

    //         if($user->type=='admin'){
    //             if (!\Auth::attempt([
    //                 'email' => $request->email,
    //                 'password' => $request->password
    //             ])) {
    //                 // throw new \Exception(__('message_alerts.use_valid_email_password'));

    //                 return Response::json(['status' => false, 'message' => __('message_alerts.use_valid_email_password'),'data'=>[]], '200');

    //             }else{
    //                 return Response::json(['status' => true, 'message' => '','data'=>[]], '200');
    //             }
    //         }else if($user->type=='subadmin'){

    //             if (!$user->active) {
    //                 return Response::json(['status' => false, 'message' => __('Your account has been suspended.'),'data'=>[]], '200');
    //             }

    //             if (!\Auth::attempt([
    //                 'email' => $request->email,
    //                 'password' => $request->password
    //             ])) {
    //                 // throw new \Exception(__('message_alerts.use_valid_email_password'));

    //                 return Response::json(['status' => false, 'message' => __('message_alerts.use_valid_email_password'),'data'=>[]], '200');
    //             }else{
    //                 return Response::json(['status' => true, 'message' => '','data'=>[]], '200');
    //             }
    //         }else{
    //             return Response::json(['status' => false, 'message' => __('message_alerts.use_admin_credentials'),'data'=>[]], '200');
    //         }
    //     }else{
    //         return Response::json(['status' => false, 'message' => __('message_alerts.credential_not_found'),'data'=>[]], '200');
    //     }
    // }

    /**
     * Logout user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        \Auth::logout();
        return redirect('admin-login');
    }


       /**
     * Logout user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function admin_logout()
    {
        \Auth::logout();
        return redirect('admin-login');
    }

}
