<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Author;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Wallet;
use App\Models\Walletproduct;
use App\Models\ProductRequest;
use App\Models\Feedback;
use App\Models\Story;
use App\Models\CmsPages;
use App\Models\BookMarkPost;
use Illuminate\Support\Facades\Validator;
use App\Models\SiteContent;
use App\Models\Social;
use Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class EditProfileController extends Controller
{
    /**
     * Show Dashboard view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::user()==null){
            return redirect('/user-login');
        }else{
            $user = User::where('id',Auth::user()->id)->first();
            
            $walletAmount = Wallet::where('user_id',$user->id)->sum('point'); 
            $point_used = ProductRequest::where('user_id',$user->id)->where('status',1)->sum('point');   
            $point = $walletAmount - $point_used;  
            $totalWalletAmount = $point." .0 /-";

            $category = Category::getCategoryOnFilter(5,'is_featured');    
            $side_category = Category::getCategoryOnFilter(3);
            $social = Social::get();
            $recent_blog = Blog::frontBlog('recent',2);
            $side_recent_blog = Blog::frontBlog('recent',3);
            $site_content = CmsPages::getCmsPages();
            $not_featured_category = Category::getNotFeaturedCategory();
            return view('site/edit_profile.index', [
                'category' => $category,
                'social' => $social,
                'site_content' => $site_content,
                'side_category' => $side_category,
                'side_recent_blog' => $side_recent_blog,
                'recent_blog' => $recent_blog,
                'user' => $user,
                'point'=>$totalWalletAmount,
                'not_featured_category' => $not_featured_category,
            ]);
        }       
    }

    public function edit_profile(Request $request)
    {
        $post = $request->all();
        unset($post['_token']);
        if($post){
            $emailexist = User::where('email',$post['email'])->where('id','!=',$post['id'])->get();
            if(count($emailexist)){
                return response(\Helpers::sendFailureAjaxResponse('Email already taken'));
            }
            if(isset($post['password']) && $post['password']!=null){
                $post['password'] = bcrypt($post['password']);
            }else{
                unset($post['password']);
            }
            $id = User::updateUser($post);
            return Response::json(['status' => true, 'message' => __('message_alerts.profile_updated_success'),'data'=>[]], '200');
        }else{
            return Response::json(['status' => false, 'message' => __('message_alerts.something_went_wrong'),'data'=>[]], '200');
        }        
    }
      
    public function saved_stories()
    {
        if(Auth::user()==null){
            return redirect('/user-login');
        }else{
            
            $data = BookMarkPost::getSavedStories();
            $category = Category::getCategoryOnFilter(5,'is_featured');    
            $side_category = Category::getCategoryOnFilter(3);
            $social = Social::get();
            $recent_blog = Blog::frontBlog('recent',2);
            $side_recent_blog = Blog::frontBlog('recent',3);
            $site_content = CmsPages::getCmsPages();
            $not_featured_category = Category::getNotFeaturedCategory();
            
           //  dd($data);
            
            return view('site/saved_stories.index', [
                'category' => $category,
                'social' => $social,
                'site_content' => $site_content,
                'side_category' => $side_category,
                'side_recent_blog' => $side_recent_blog,
                'recent_blog' => $recent_blog,
                'data' => $data,
                'not_featured_category' => $not_featured_category,
            ]);
        }       
    }

    public function manage_request(){
        if(Auth::user()==null){
            return redirect('/user-login');
        }else{
            $data = BookMarkPost::getSavedStories();
            $category = Category::getCategoryOnFilter(5,'is_featured');    
            $side_category = Category::getCategoryOnFilter(3);
            $social = Social::get();
            $recent_blog = Blog::frontBlog('recent',2);
            $side_recent_blog = Blog::frontBlog('recent',3);
            $site_content = CmsPages::getCmsPages();
            $not_featured_category = Category::getNotFeaturedCategory();

            $managereq = ProductRequest::getuserrequest(Auth::user()->id);  
               // dd($managereq);
            return view('site/manage_request.index', [
                'category' => $category,
                'social' => $social,
                'site_content' => $site_content,
                'side_category' => $side_category,
                'side_recent_blog' => $side_recent_blog,
                'recent_blog' => $recent_blog,
                'data' => $data,
                'not_featured_category' => $not_featured_category,
                'managereq'=>$managereq
            ]);
        }   
    }
               
    public function redeem_request(){
        if(Auth::user()==null){
            return redirect('/user-login');
        }else{
            $data = BookMarkPost::getSavedStories();
            $category = Category::getCategoryOnFilter(5,'is_featured');    
            $side_category = Category::getCategoryOnFilter(3);
            $social = Social::get();
            $recent_blog = Blog::frontBlog('recent',2);
            $side_recent_blog = Blog::frontBlog('recent',3);
            $site_content = CmsPages::getCmsPages();
            $not_featured_category = Category::getNotFeaturedCategory();

            $redeemReq = Walletproduct::orderBy('id','DESC')->get();
            // dd($redeemReq);
            return view('site/manage_request.redeem', [
                'category' => $category,
                'social' => $social,
                'site_content' => $site_content,
                'side_category' => $side_category,
                'side_recent_blog' => $side_recent_blog,
                'recent_blog' => $recent_blog,
                'data' => $data,
                'not_featured_category' => $not_featured_category,
                'redeemReq'=>$redeemReq
            ]);
        }   
    }

    public function redemm_points(Request $request){

       // dd($request->product_id);
        if(Auth::user()==null){
            return redirect('/user-login');
        }else{
            $data = BookMarkPost::getSavedStories();
            $category = Category::getCategoryOnFilter(5,'is_featured');    
            $side_category = Category::getCategoryOnFilter(3);
            $social = Social::get();
            $recent_blog = Blog::frontBlog('recent',2);
            $side_recent_blog = Blog::frontBlog('recent',3);
            $site_content = CmsPages::getCmsPages();
            $not_featured_category = Category::getNotFeaturedCategory();
           
            try {
                $exist  = ProductRequest::where('user_id',Auth::user()->id)->where('product_id',$request->product_id)->where('status',1)->count();
                    if($exist != 0){
                        $exist_pro  = Walletproduct::find($request->product_id);
                        if($exist_pro->redeem ==0 ){                            
                            return back()->withErrors(['error' => 'Request cannot send! product is one time already purchased']);
                        }                    
                    }
                    $req = ProductRequest::where('user_id',Auth::user()->id)->where('status',0)->count();
                    if($req != 0){ 
                       return back()->withErrors(['error' => 'Request can not send! previous request is not processed by our team.']);
                    }
    
                    $point_user = Wallet::where('user_id',Auth::user()->id)->sum('point');
                    $point_used = ProductRequest::where('user_id',Auth::user()->id)->where('status',1)->sum('point');                
                    $point = $point_user -$point_used;    
    
                    $Walletproduct = Walletproduct::find($request->product_id);
                    if( $point < $Walletproduct->point ){                                    
                       return back()->withErrors(['error' => 'insufficient amount!']);
                    }
                    // Check if the request is successful and handle accordingly
                    $userRequest = ProductRequest::create([
                        'product_id' => $request->product_id,
                        'user_id' => Auth::user()->id,
                    ]);
                    return back()->with('success', 'Redeem request submitted successfully!');
            } catch (\Exception $ex) {
                echo $ex;exit;
                return back()->withErrors(['error' => 'An error occurred while processing the redeem request.']);

            }

            return view('site/manage_request.index', [
                'category' => $category,
                'social' => $social,
                'site_content' => $site_content,
                'side_category' => $side_category,
                'side_recent_blog' => $side_recent_blog,
                'recent_blog' => $recent_blog,
                'data' => $data,
                'not_featured_category' => $not_featured_category
            ]);
        }   
    }
                
    public function my_stories()
    {
        if(Auth::user()==null){
            return redirect('/user-login');
        }else{
            $data = BookMarkPost::getSavedStories();
            $category = Category::getCategoryOnFilter(5,'is_featured');    
            $side_category = Category::getCategoryOnFilter(3);
            $social = Social::get();
            $recent_blog = Blog::frontBlog('recent',2);
            $side_recent_blog = Blog::frontBlog('recent',3);
            $site_content = CmsPages::getCmsPages();
            $not_featured_category = Category::getNotFeaturedCategory();

            $stories = Story::where('user_id', Auth::user()->id)->orderBy('id','DESC')->get();
            // dd($stories);
            return view('site/my_stories.index', [
                'category' => $category,
                'social' => $social,
                'site_content' => $site_content,
                'side_category' => $side_category,
                'side_recent_blog' => $side_recent_blog,
                'recent_blog' => $recent_blog,
                'data' => $data,
                'not_featured_category' => $not_featured_category,
                'stories'=>$stories
            ]);
        }       
    }
      
    public function view_my_story($id)
    {
        if(Auth::user()==null){
            return redirect('/user-login');
        }else{
            $data = BookMarkPost::getSavedStories();
            $category = Category::getCategoryOnFilter(5,'is_featured');    
            $side_category = Category::getCategoryOnFilter(3);
            $social = Social::get();
            $recent_blog = Blog::frontBlog('recent',2);
            $side_recent_blog = Blog::frontBlog('recent',3);
            $site_content = CmsPages::getCmsPages();
            $not_featured_category = Category::getNotFeaturedCategory();

            $stories = Story::where('user_id',Auth::user()->id)->where('id',$id)->first();

            // dd($stories);
            return view('site/my_stories.view', [
                'category' => $category,
                'social' => $social,
                'site_content' => $site_content,
                'side_category' => $side_category,
                'side_recent_blog' => $side_recent_blog,
                'recent_blog' => $recent_blog,
                'data' => $data,
                'not_featured_category' => $not_featured_category,
                'stories'=>$stories
            ]);
        }       
    }

    public function add_my_stories()
    {
            $category = Category::getCategoryOnFilter(5,'is_featured');    
            $side_category = Category::getCategoryOnFilter(3);
            $social = Social::get();
            $recent_blog = Blog::frontBlog('recent',2);
            $side_recent_blog = Blog::frontBlog('recent',3);
            $site_content = CmsPages::getCmsPages();
            $not_featured_category = Category::getNotFeaturedCategory();

            return view('site/my_stories.add', [
                'category' => $category,
                'social' => $social,
                'site_content' => $site_content,
                'side_category' => $side_category,
                'side_recent_blog' => $side_recent_blog,
                'recent_blog' => $recent_blog,
                //'data' => $data,
                'not_featured_category' => $not_featured_category,
            ]);              
    }

    public function save_my_stories(Request $request)
    {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email',
                'phone' => 'required',
                'story' => 'required',
                'file' => 'required|file|mimes:jpeg,jpg,png,pdf,mp4,flv,avi,wmv,heif,heic|',
            ]);
            
            $userId = Auth::user()->id;
            $file=$request->file('file');
            $filePath = $file->storeAs('upload/story', $file->getClientOriginalName(), 'public');
           
            $data = Story::create([
                'user_id' => $userId,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'story' => $request->story,
                'status'=>0,
                'file' => $filePath,
            ]);

            return back()->with('success', 'Your story submitted successfully!');
            
            $category = Category::getCategoryOnFilter(5,'is_featured');    
            $side_category = Category::getCategoryOnFilter(3);
            $social = Social::get();
            $recent_blog = Blog::frontBlog('recent',2);
            $side_recent_blog = Blog::frontBlog('recent',3);
            $site_content = CmsPages::getCmsPages();
            $not_featured_category = Category::getNotFeaturedCategory();

            return view('site/my_stories.add', [
                'category' => $category,
                'social' => $social,
                'site_content' => $site_content,
                'side_category' => $side_category,
                'side_recent_blog' => $side_recent_blog,
                'recent_blog' => $recent_blog,
                //'data' => $data,
                'not_featured_category' => $not_featured_category,
            ]);              
    }

    public function my_feedback()
    {        
        // $data = BookMarkPost::getSavedStories();
        $category = Category::getCategoryOnFilter(5,'is_featured');    
        $side_category = Category::getCategoryOnFilter(3);
        $social = Social::get();
        $recent_blog = Blog::frontBlog('recent',2);
        $side_recent_blog = Blog::frontBlog('recent',3);
        $site_content = CmsPages::getCmsPages();
        $not_featured_category = Category::getNotFeaturedCategory();
                     
        return view('site/my_feedback.index', [
            'category' => $category,
            'social' => $social,
            'site_content' => $site_content,
            'side_category' => $side_category,
            'side_recent_blog' => $side_recent_blog,
            'recent_blog' => $recent_blog,
            //'data' => $data,
            'not_featured_category' => $not_featured_category,
        ]);              
    }

    public function save_feedback(Request $request)
    {
            $request->validate([
                'name' => [
                    'required'
                ],
                'email' => [
                    'required'
                ],
                'phone' =>[
                    'required'
                ],
                'feed_back' =>[
                    'required'
                ]
            ]);
            
            if(Auth::user() && Auth::user()->type == 'user'){
                $userId = Auth::user()->id;
            }else{
                $userId = null;
            }
            $data = Feedback::create([
                'user_id' => $userId,
                'name'=>$request->name,
                'email'=>$request->email,
                'phone'=>$request->phone,
                'feed_back'=>$request->feed_back
            ]);
            return back()->with('success', 'Your feedback submitted successfully!');

            //dd($data);
            $category = Category::getCategoryOnFilter(5,'is_featured');    
            $side_category = Category::getCategoryOnFilter(3);
            $social = Social::get();
            $recent_blog = Blog::frontBlog('recent',2);
            $side_recent_blog = Blog::frontBlog('recent',3);
            $site_content = CmsPages::getCmsPages();
            $not_featured_category = Category::getNotFeaturedCategory();

            return view('site/my_feedback.index', [
                'category' => $category,
                'social' => $social,
                'site_content' => $site_content,
                'side_category' => $side_category,
                'side_recent_blog' => $side_recent_blog,
                'recent_blog' => $recent_blog,
                //'data' => $data,
                'not_featured_category' => $not_featured_category,
            ]);              
    }
     
    public function deleteStory(Request $request,$id)
    {
        BookMarkPost::where('id', $id)->delete();      
        return back()->with('success',__('message_alerts.blog_deleted_success'));
    }

    public function bookmark(Request $request)
    {
        $post = $request->all();
        if($post){
            $alreadyBookmarked = BookMarkPost::where('user_id',Auth::user()->id)->where('blog_id',$post['blog_id'])->count();
            if ($alreadyBookmarked) {
                BookMarkPost::where('user_id',Auth::user()->id)->where('blog_id',$post['blog_id'])->delete();
                return Response::json(['status' => true, 'message' => __('message_alerts.bookmark_removed'),'data'=>['data'=>'already']],'200');
            }else{
                $data['blog'] = BookMarkPost::insertGetID(array('user_id'=>Auth::user()->id,'blog_id'=>$post['blog_id'])); 
                $bookmarked = BookMarkPost::where('user_id',Auth::user()->id)->where('blog_id',$post['blog_id'])->first();
                if($bookmarked){
                    $data['is_bookmark'] = 1;
                }else{
                    $data['is_bookmark'] = 0;
                } 
                return Response::json(['status' => true, 'message' => __('message_alerts.bookmarked_success'),'data'=>['data'=>'first']], '200');
            }
        }else{
            return Response::json(['status' => false, 'message' =>__('message_alerts.something_went_wrong'),'data'=>[]], '200');
        }        
    }

    public function uploadProfileImage(Request $request){
        try {
            if($request->ajax()){
                $post = $request->all();
                $name = '';
                if($post['photo']!=''){
                    $file=$request->file('photo');
                    $name = time() . rand() .'.'.$file->getClientOriginalExtension();
                    $destination =  public_path('/upload/user/').$name;
                    $c = \Helpers::compress_image($file,$destination,30);
                    $post['photo'] = url('/upload/user').'/'.$name;
                    User::updateUser($post);
                }
                $url = '';
                if($name != ''){
                    $url = url('/upload/user/').'/'.$name;
                }
                $data = array('url'=>$url, 'name' => $name);
                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.profile_picture_updated_success'),$data));
            }else{
              return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }

    public function deleteAccount(Request $request)
    {
        if(Auth::user()){
            User::where('id',Auth::user()->id)->delete();
            \Auth::logout();
            return redirect('user-login');
        }else{
            return back();
        }
    }

}
 