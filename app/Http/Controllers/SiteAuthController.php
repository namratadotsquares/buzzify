<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CmsPages;
use Illuminate\Support\Facades\Validator;
use App\Models\SiteContent;
use App\Models\Category;
use App\Models\Social;
use App\Models\Blog;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
Use DB;

class SiteAuthController extends Controller
{
    /**
     * Show specified view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loginView()
    {

        if (auth()->user() && auth()->user()->type == 'user') {
           return redirect('/');
        }

        $category = Category::getCategoryOnFilter(5,'is_featured');    
        $side_category = Category::getCategoryOnFilter(3);
        $social = Social::get();
        $recent_blog = Blog::frontBlog('recent',2);
        $side_recent_blog = Blog::frontBlog('recent',3);
        $site_content = CmsPages::get();
        $not_featured_category = Category::where('is_featured',0)->where('status',1)->orderBy('id','DESC')->get();
        return view('site/login/index', [
            'category' => $category,
            'social' => $social,
            'site_content' => $site_content,
            'side_category' => $side_category,
            'side_recent_blog' => $side_recent_blog,
            'recent_blog' => $recent_blog,
            'not_featured_category' => $not_featured_category,
        ]);
    }

    /**
     * Show specified view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function signupView()
    {
        if (auth()->user() && auth()->user()->type == 'user') {
           return redirect('/');
        }

        $category = Category::getCategoryOnFilter(5,'is_featured');    
        $side_category = Category::getCategoryOnFilter(3);
        $social = Social::get();
        $recent_blog = Blog::frontBlog('recent',2);
        $side_recent_blog = Blog::frontBlog('recent',3);
        $site_content = CmsPages::get();
        $not_featured_category = Category::where('is_featured',0)->where('status',1)->orderBy('id','DESC')->get();
        return view('site/signup/index', [
            'category' => $category,
            'social' => $social,
            'site_content' => $site_content,
            'side_category' => $side_category,
            'side_recent_blog' => $side_recent_blog,
            'recent_blog' => $recent_blog,
            'not_featured_category' => $not_featured_category,
        ]);
    }

    /**
     * Show specified view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function forgetView()
    {
        if (auth()->user() && auth()->user()->type == 'user') {
           return redirect('/');
        }
        $category = Category::getCategoryOnFilter(5,'is_featured');    
        $side_category = Category::getCategoryOnFilter(3);
        $social = Social::get();
        $recent_blog = Blog::frontBlog('recent',2);
        $side_recent_blog = Blog::frontBlog('recent',3);
        $site_content = CmsPages::get();
        $not_featured_category = Category::where('is_featured',0)->where('status',1)->orderBy('id','DESC')->get();
        return view('site/forget_password/index', [
            'category' => $category,
            'social' => $social,
            'site_content' => $site_content,
            'side_category' => $side_category,
            'side_recent_blog' => $side_recent_blog,
            'recent_blog' => $recent_blog,
            'not_featured_category' => $not_featured_category,
        ]);
    }

    /**
     * Authenticate login user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $user = User::where('email',$request->email)->first(); 
        if($user){
            if($user->type=='user'){

                if (!$user->active) {
                    return Response::json(['status' => false, 'message' => __('message_alerts.unauthorized'),'data'=>[]], '200');
                }

                if (!\Auth::attempt([
                    'email' => $request->email, 
                    'password' => $request->password
                ])) {
                    return Response::json(['status' => false, 'message' => __('message_alerts.use_valid_email_password'),'data'=>[]], '200');
                }else{
                    return Response::json(['status' => true, 'message' => __('message_alerts.login_success'),'data'=>[]], '200');
                }
            }else{
                return Response::json(['status' => false, 'message' => __('message_alerts.use_valid_email_password'),'data'=>[]], '200');
            }
        }else{
            return Response::json(['status' => false, 'message' => __('message_alerts.use_valid_email_password'),'data'=>[]], '200');
        }        
    }

    /**
     * Authenticate login user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function signup(Request $request)
    {   
        $emailExist = User::where('email',$request->email)->first();
        if ($emailExist) {
            return array('success'=>false,'data'=>null,'message'=>__('message_alerts.email_exist'));
        }
        $post['name'] = $request->name;
        $post['email'] = $request->email;
        $post['phone'] = $request->phone;
        $post['active'] = 1;
        $post['type'] = "user";
        $post['lang_code'] = Setting('preferred_site_language');
        $post['created_at'] = date('Y-m-d h:i:s');
        $post['password'] = bcrypt($request->password);
        $id = User::addUser($post);
        $user= User::find($id['id']);
        $user = User::where('email',$request->email)->first(); 
        if($user){
            if($user->type=='user'){
                if (!\Auth::attempt([
                    'email' => $request->email,
                    'password' => $request->password
                ])) {
                    return Response::json(['status' => false, 'message' => __('message_alerts.use_valid_email_password'),'data'=>[]], '200');
                }else{
                    return Response::json(['status' => true, 'message' => __('message_alerts.signup_success'),'data'=>[]], '200');
                }
            }else{
                return Response::json(['status' => false, 'message' =>  __('message_alerts.use_valid_email_password'),'data'=>[]], '200');
            }
        }else{
            return Response::json(['status' => false, 'message' => __('message_alerts.use_valid_email_password'),'data'=>[]], '200');
        }
    }

   
    public function forget_password(Request $request)
    {
        $post = $request->all();
        if(!empty($post)){
            $user = User::where('email',$request->email)->first(); 
            if($user){
                $otp = rand(1000,9999);
                $data = array();
                $data['email'] = $user->email;
                $data['otp'] = $otp;
                $c = \Helpers::sendEmail('emails.forgot-password',$data,$user->email,'', setting('site_name'). ' forgot password '.$data['otp'], setting('site_name'). ' App',setting('from_email'),'');
                return Response::json(['status' => true, 'message' => __('message_alerts.otp_sent_on_email'),'data'=>$data], '200');
            }else{
                return Response::json(['status' => false, 'message' => __('message_alerts.email_invalid'),'data'=>[]], '200');
            }
        }else{
            return Response::json(['status' => false, 'message' => __('message_alerts.email_invalid'),'data'=>[]], '200');
        }       
    }

    public function reset_password(Request $request)
    {
        $post = $request->all();
        if(!empty($post)){
            $inject = array();
            if(isset($post['password']) && $post['password'] != ''){
                $inject['password'] = bcrypt($post['password']);
            }
            $user = User::where('email',$post['email'])->first();
            User::where('id',$user->id)->update($inject);
            return Response::json(['status' => true, 'message' => __('message_alerts.password_reset_success'),'data'=>[]], '200');
        }else{
            return Response::json(['status' => false, 'message' => __('message_alerts.something_went_wrong'),'data'=>[]], '200');
        }       
    }

    /**
     * Logout user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        \Auth::logout();
        return redirect('user-login');
    }
}
