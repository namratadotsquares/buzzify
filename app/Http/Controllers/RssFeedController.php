<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RssFeed;
use App\Models\Category;
use App\Models\Blog;
use App\Models\Feedback;
use App\Models\BlogImages;
use App\Models\BlogTranslation;
use App\Models\BlogCategory;
use App\Models\BlogActionLog;
use Illuminate\Support\Facades\Validator;
use Auth;
use UploadImage as Image;
use Illuminate\Support\Facades\Gate;

class RssFeedController extends Controller
{

    function __construct(){
        $this->middleware('permission:feed-item-list|feed-item-save-post|rss-feed-list|rss-feed-delete|rss-feed-status', ['only' => ['feedItem','saveFeed','index']]);
        $this->middleware('permission:feed-item-list', ['only' => ['feedItem']]);
        $this->middleware('permission:feed-item-save-post', ['only' => ['saveFeed']]);
        $this->middleware('permission:rss-feed-list', ['only' => ['index']]);
        $this->middleware('permission:rss-feed-delete', ['only' => ['deleteRssFeed']]);
        $this->middleware('permission:rss-feed-status', ['only' => ['changeRssFeedStatus']]);
    }


    /**
     * Show Category view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$layout = 'side-menu', $theme = 'light')
    {
        $feed = RssFeed::getAllRssFeed($request->all());
        $category = Category::where('status',1)->get();
        return view('super-admin/rss_feed.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'feed' => $feed,
            'category'=>$category,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans('admin.dashboard').'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/rss-feed-src/side-menu/light').'" class="breadcrumb--active">'.trans('admin.rss_feed').'</a>'

        ]);
    }
#----------------feedback----------------
public function feedback(Request $request,$layout = 'side-menu', $theme = 'light')
    {
        
        $sources = Feedback::latest()->get();

        
        return view('super-admin/feedback.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'feed'=>$sources,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans("admin.dashboard").'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/feed-item/side-menu/light').'" class="breadcrumb--active">'.trans("admin.feedback").'</a>'
        ]);
    }

    /**
     * Show feed items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function feedItem(Request $request,$layout = 'side-menu', $theme = 'light')
    {
        $final_array = array();
        if(isset($_GET['category_id']) && $_GET['category_id']!='' && isset($_GET['source']) && $_GET['source']==''){
            $search_cat = RssFeed::where('category_id',$_GET['category_id'])->get();
    //      echo json_encode($search_cat);exit;
            foreach($search_cat as $detail){
                if($detail->rss_url!='' && $detail->rss_url!=null){
                    // $rss_feed = simplexml_load_file($detail->rss_url);
                    //$namespaces = $rss_feed->getNamespaces(true);
                    $rss_feed= @simplexml_load_file($detail->rss_url);
                    if ($rss_feed) {
                        $namespaces = "http://search.yahoo.com/mrss/";
                        if (is_array($rss_feed->channel->item) || is_object($rss_feed->channel->item))
                        { 
                            foreach($rss_feed->channel->item as $feed_item){
                                $feed_item->category_id = $detail->category_id;
                                $feed_item->source = $detail->rss_url;
                                $explode = explode("=",$feed_item->guid); 
                                if(isset($explode[1])){
                                    $feed_item->post_id = $explode[1];
                                    $blog = Blog::where('post_id',$feed_item->post_id)->first();
                                    if($blog){
                                        $feed_item->is_saved = 1;
                                    }else{
                                        $feed_item->is_saved = 0;
                                    }
                                }else{
                                    $blog = Blog::where('title',$feed_item->title)->first();
                                    if($blog){
                                        $feed_item->is_saved = 1;
                                    }else{
                                        $feed_item->is_saved = 0;
                                    }
                                }
    
                                if ($feed_item->image !='') {
                                    $feed_item->url = $feed_item->image;
                                }else if(!is_null($namespaces) && !is_null($feed_item->children($namespaces)) && !is_null($feed_item->children($namespaces)->content[0])){
                                    $image = $feed_item->children($namespaces)->content[0]->attributes();
                                    $feed_item->url = $image['url'];
                                }else if(!is_null($namespaces) && !is_null($feed_item->children($namespaces)) && !is_null($feed_item->children($namespaces)->thumbnail[0])){
                                    $image = $feed_item->children($namespaces)->thumbnail[0]->attributes();
                                    $feed_item->url = $image['url'];
                                }else{
                                    $feed_item->url = url('upload/author/default.png');
                                }
    
                                array_push($final_array,$feed_item);
                            }
                        }
                    }
                }               
            }
        }else if(isset($_GET['source']) && $_GET['source']!='' && isset($_GET['category_id']) && $_GET['category_id']==''){
            $search_cat = RssFeed::where('id',$_GET['source'])->first();
            if($search_cat){
                if($search_cat->rss_url!='' && $search_cat->rss_url!=null){
                    $rss_feed= @simplexml_load_file($search_cat->rss_url);
                    if ($rss_feed) {
                        $namespaces = "http://search.yahoo.com/mrss/";
                        if (is_array($rss_feed->channel->item) || is_object($rss_feed->channel->item))
                        { 
                            foreach($rss_feed->channel->item as $feed_item){
                                $feed_item->category_id = $search_cat->category_id;
                                $feed_item->source = $search_cat->rss_url;
                                $explode = explode("=",$feed_item->guid); 
                                if(isset($explode[1])){
                                    $feed_item->post_id = $explode[1];
                                    $blog = Blog::where('post_id',$feed_item->post_id)->first();
                                    if($blog){
                                        $feed_item->is_saved = 1;
                                    }else{
                                        $feed_item->is_saved = 0;
                                    }
                                }else{
                                    $blog = Blog::where('title',$feed_item->title)->first();
                                    if($blog){
                                        $feed_item->is_saved = 1;
                                    }else{
                                        $feed_item->is_saved = 0;
                                    }
                                }

                                if ($feed_item->image !='') {
                                    $feed_item->url = $feed_item->image;
                                }else if(!is_null($namespaces) && !is_null($feed_item->children($namespaces)) && !is_null($feed_item->children($namespaces)->content[0])){
                                    $image = $feed_item->children($namespaces)->content[0]->attributes();
                                    $feed_item->url = $image['url'];
                                }else if(!is_null($namespaces) && !is_null($feed_item->children($namespaces)) && !is_null($feed_item->children($namespaces)->thumbnail[0])){
                                    $image = $feed_item->children($namespaces)->thumbnail[0]->attributes();
                                    $feed_item->url = $image['url'];
                                }else{
                                    $feed_item->url = url('upload/author/default.png');
                                }

                                array_push($final_array,$feed_item);
                            }
                        }
                    }
                } 
            }           
        }else if(isset($_GET['source']) && $_GET['source']!='' && isset($_GET['category_id']) && $_GET['category_id']!=''){
            $search_cat = RssFeed::where('id',$_GET['source'])->where('category_id',$_GET['category_id'])->first();
            if($search_cat){
                if($search_cat->rss_url!='' && $search_cat->rss_url!=null){
                    $rss_feed= @simplexml_load_file($search_cat->rss_url);
                    if ($rss_feed) {
                        $namespaces = "http://search.yahoo.com/mrss/";
                        if (is_array($rss_feed->channel->item) || is_object($rss_feed->channel->item))
                        {
                            foreach($rss_feed->channel->item as $feed_item){
                                $feed_item->category_id = $search_cat->category_id;
                                $feed_item->source = $search_cat->rss_url;
                                $explode = explode("=",$feed_item->guid); 
                                if(isset($explode[1])){
                                    $feed_item->post_id = $explode[1];
                                    $blog = Blog::where('post_id',$feed_item->post_id)->first();
                                    if($blog){
                                        $feed_item->is_saved = 1;
                                    }else{
                                        $feed_item->is_saved = 0;
                                    }
                                }else{
                                    $blog = Blog::where('title',$feed_item->title)->first();
                                    if($blog){
                                        $feed_item->is_saved = 1;
                                    }else{
                                        $feed_item->is_saved = 0;
                                    }
                                }
    
                                if ($feed_item->image !='') {
                                    $feed_item->url = $feed_item->image;
                                }else if(!is_null($namespaces) && !is_null($feed_item->children($namespaces)) && !is_null($feed_item->children($namespaces)->content[0])){
                                    $image = $feed_item->children($namespaces)->content[0]->attributes();
                                    $feed_item->url = $image['url'];
                                }else if(!is_null($namespaces) && !is_null($feed_item->children($namespaces)) && !is_null($feed_item->children($namespaces)->thumbnail[0])){
                                    $image = $feed_item->children($namespaces)->thumbnail[0]->attributes();
                                    $feed_item->url = $image['url'];
                                }else{
                                    $feed_item->url = url('upload/author/default.png');
                                }
    
                                array_push($final_array,$feed_item);
                            }
                        }
                     // Do some stuff . . .
                    }
                    
                }               
            }           
        }
        if(isset($_GET['category_id']) && $_GET['category_id']!=''){
            $sources = RssFeed::where('status',1)->where('category_id',$_GET['category_id'])->with('categoryData')->get();
        }else{
            $sources = RssFeed::where('status',1)->with('categoryData')->get();
        }
        $feed_data = RssFeed::getAllRssFeed($request->all());
        $category = Category::where('status',1)->get();
        return view('super-admin/feed_items.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'feed' => $final_array,
            'category'=>$category,
            'sources'=>$sources,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans("admin.dashboard").'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/feed-item/side-menu/light').'" class="breadcrumb--active">'.trans("admin.feed_items").'</a>'
        ]);
    }

    /**
     * Method to delete rss feed
     * @param array $request post data, id
    */
    public function saveFeed(Request $request,$post_id,$category_id)	
    {
 
    	$rss_feed = simplexml_load_file($_GET['source']);
    	
    	//dd($rss_feed);
        $namespaces = "http://search.yahoo.com/mrss/";
        //echo json_encode($rss_feed);exit;
		foreach($rss_feed->channel->item as $feed_item){
			$explode = explode("=",$feed_item->guid);  
			if(isset($explode[1])){
				$feed_item->post_id = $explode[1];
				if($feed_item->post_id==$post_id){
					$explode_title = explode(":",$feed_item->title);
					$slug = \Helpers::createSlug($feed_item->title[0],'blog',0,false);
	                $post['slug'] = $slug;
					$post['title'] = $feed_item->title[0];
			    	$post['description'] = $feed_item->description[0];
			    	$post['banner_image'] = $feed_item->url[0];
			    	$post['url'] = $feed_item->link[0];
			    	//$post['category_id'] = $category_id;
			    	$post['created_at'] = date('Y-m-d H:i:s');
					$post['post_id'] = $post_id;
					$post['status'] = 2;
                    $post['created_by'] = Auth::User()->id;
                    $post['schedule_date'] = date("Y-m-d H:i:s");
                    $blog_id = Blog::insertGetId($post);  
                    if($blog_id){
                        BlogActionLog::record('blog_created', (int) $blog_id, Auth::check() ? (int) Auth::id() : null, [
                            'source' => 'rss_feed',
                            'status' => (int) ($post['status'] ?? 0),
                            'post_id' => isset($post['post_id']) ? (int) $post['post_id'] : null,
                        ]);

                        $blogCategoryArr = array(
                            'blog_id'=>$blog_id,
                          'category_id' => $category_id,
                          'created_at'=>date("Y-m-d H:i:s"),
                        );
                        $blogcategory = BlogCategory::insertGetId($blogCategoryArr);
                    }
                    $injectTransLation = array(
                        'blog_id' =>$blog_id,
                        'language_code' =>setting('preferred_site_language'),
                        'title' =>$feed_item->title[0],
                        'description' =>$feed_item->description[0],
                        'created_at' =>date("Y-m-d H:i:s"),
                    );
                    BlogTranslation::insertGetId($injectTransLation);

                    if ($feed_item->image !='') {
                        $urlToImage = $feed_item->image;
                    }else if(!is_null($namespaces) && !is_null($feed_item->children($namespaces)) && !is_null($feed_item->children($namespaces)->content[0])){
                        $image = $feed_item->children($namespaces)->content[0]->attributes();
                        $urlToImage = $image['url'];
                    }else if(!is_null($namespaces) && !is_null($feed_item->children($namespaces)) && !is_null($feed_item->children($namespaces)->thumbnail[0])){
                        $image = $feed_item->children($namespaces)->thumbnail[0]->attributes();
                        $urlToImage = $image['url'];
                    }else{
                        $urlToImage = '';
                    }

                    if($urlToImage!=''){
                        $file = $urlToImage;
                        $ext = \Helpers::get_file_extension($file);
                        if(in_array($ext, ['tif','tiff','bmp','jpg','jpeg','gif','png'])){
                            $exten = \Helpers::get_file_extension($file);
                            $name = time() . rand() .'.'.$exten;
                            $destination =  public_path('/upload/blog/banner/original/').$name;
                            $basePath = public_path('/upload/blog/banner/');
                            $info = getimagesize($file);
                            if ($info['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($file);
                            elseif ($info['mime'] == 'image/gif') $image = imagecreatefromgif($file);
                            elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($file);
                            $c = imagejpeg($image, $destination, 30);
                            $img = Image::make($destination);

                            $img->resize(800, null, function ($constraint) {
                                $constraint->aspectRatio();
                            })->save($basePath.'800/'.$name);

                            $img->resize(360, null, function ($constraint) {
                                $constraint->aspectRatio();
                            })->save($basePath.'360/'.$name);
                        }else{
                            $category_data = Category::where('id',$category_id)->first();
                            $cat_files = url('upload/category/original/'.$category_data->image);
                            $exten = \Helpers::get_file_extension($cat_files);
                            $name = time() . rand() .'.'.$exten;
                            $destination =  public_path('/upload/blog/banner/original/').$name;
                            $basePath = public_path('/upload/blog/banner/');
                            $info = getimagesize($cat_files);
                            if ($info['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($cat_files);
                            elseif ($info['mime'] == 'image/gif') $image = imagecreatefromgif($cat_files);
                            elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($cat_files);
                            $c = imagejpeg($image, $destination, 30);
                            $img = Image::make($destination);

                            $img->resize(800, null, function ($constraint) {
                                $constraint->aspectRatio();
                            })->save($basePath.'800/'.$name);

                            $img->resize(360, null, function ($constraint) {
                                $constraint->aspectRatio();
                            })->save($basePath.'360/'.$name);
                        }
                        $image_id = BlogImages::insertGetId(array('blog_id'=>$blog_id,'image'=>$name,'created_at'=>date('Y-m-d H:i:s'))); 
                    }else{
                        $category_data = Category::where('id',$category_id)->first();
                        
                        $cat_files = url('upload/category/original/'.$category_data->image);
                        $exten = \Helpers::get_file_extension($cat_files);
                        $name = time() . rand() .'.'.$exten;
                        $destination =  public_path('/upload/blog/banner/original/').$name;
                        $basePath = public_path('/upload/blog/banner/');
                        $info = getimagesize($cat_files);
                        if ($info['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($cat_files);
                        elseif ($info['mime'] == 'image/gif') $image = imagecreatefromgif($cat_files);
                        elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($cat_files);
                        $c = imagejpeg($image, $destination, 30);
                        $img = Image::make($destination);

                        $img->resize(800, null, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($basePath.'800/'.$name);

                        $img->resize(360, null, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($basePath.'360/'.$name);
                        
                        $image_id = BlogImages::insertGetId(array('blog_id'=>$blog_id,'image'=>$name,'created_at'=>date('Y-m-d H:i:s'))); 
                    }		        
				}
			}else{
				if($feed_item->link[0]==$_GET['link']){
					$explode_title = explode(":",$feed_item->title);
					$slug = \Helpers::createSlug($feed_item->title[0],'blog',0,false);
	                $post['slug'] = $slug;
					$post['title'] = $feed_item->title[0];
			    	$post['description'] = $feed_item->description[0];
			    	$post['banner_image'] = $feed_item->url[0];
			    	$post['url'] = $feed_item->link[0];
			    	$post['category_id'] = $category_id;
			    	$post['created_at'] = date('Y-m-d H:i:s');
					$post['status'] = 2;
                    $post['created_by'] = Auth::User()->id;
                  	$post['schedule_date'] = date("Y-m-d H:i:s");
                    $blog_id = Blog::insertGetId($post);  
                  	if($blog_id){
                        BlogActionLog::record('blog_created', (int) $blog_id, Auth::check() ? (int) Auth::id() : null, [
                            'source' => 'rss_feed',
                            'status' => (int) ($post['status'] ?? 0),
                        ]);

                      $blogCategoryArr = array(
                      	'blog_id'=>$blog_id,
                        'category_id' => $category_id,
                        'created_at'=>date("Y-m-d H:i:s"),
                      );
                      $blogcategory = BlogCategory::insertGetId($blogCategoryArr);
                    }
                    $injectTransLation = array(
                        'blog_id' =>$blog_id,
                        'language_code' =>setting('preferred_site_language'),
                        'title' =>$feed_item->title[0],
                        'description' =>$feed_item->description[0],
                        'created_at' =>date("Y-m-d H:i:s"),
                    );
                    BlogTranslation::insertGetId($injectTransLation);

                    if ($feed_item->image !='') {
                        $urlToImage = $feed_item->image;
                    }else if(!is_null($namespaces) && !is_null($feed_item->children($namespaces)) && !is_null($feed_item->children($namespaces)->content[0])){
                        $image = $feed_item->children($namespaces)->content[0]->attributes();
                        $urlToImage = $image['url'];
                    }else if(!is_null($namespaces) && !is_null($feed_item->children($namespaces)) && !is_null($feed_item->children($namespaces)->thumbnail[0])){
                        $image = $feed_item->children($namespaces)->thumbnail[0]->attributes();
                        $urlToImage = $image['url'];
                    }else{
                        $urlToImage = '';
                    }

                    if($urlToImage!=''){
                        $file = $urlToImage;
                        $ext = \Helpers::get_file_extension($file);
                        if(in_array($ext, ['tif','tiff','bmp','jpg','jpeg','gif','png'])){
                            $exten = \Helpers::get_file_extension($file);
                            $name = time() . rand() .'.'.$exten;
                            $destination =  public_path('/upload/blog/banner/original/').$name;
                            $basePath = public_path('/upload/blog/banner/');
                            $info = getimagesize($file);
                            if ($info['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($file);
                            elseif ($info['mime'] == 'image/gif') $image = imagecreatefromgif($file);
                            elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($file);
                            $c = imagejpeg($image, $destination, 30);
                            $img = Image::make($destination);

                            $img->resize(800, null, function ($constraint) {
                                $constraint->aspectRatio();
                            })->save($basePath.'800/'.$name);

                            $img->resize(360, null, function ($constraint) {
                                $constraint->aspectRatio();
                            })->save($basePath.'360/'.$name);
                        }else{
                            $category_data = Category::where('id',$category_id)->first();
                            $cat_files = url('upload/category/original/'.$category_data->image);
                            $exten = \Helpers::get_file_extension($cat_files);
                            $name = time() . rand() .'.'.$exten;
                            $destination =  public_path('/upload/blog/banner/original/').$name;
                            $basePath = public_path('/upload/blog/banner/');
                            $info = getimagesize($cat_files);
                            if ($info['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($cat_files);
                            elseif ($info['mime'] == 'image/gif') $image = imagecreatefromgif($cat_files);
                            elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($cat_files);
                            $c = imagejpeg($image, $destination, 30);
                            $img = Image::make($destination);

                            $img->resize(800, null, function ($constraint) {
                                $constraint->aspectRatio();
                            })->save($basePath.'800/'.$name);

                            $img->resize(360, null, function ($constraint) {
                                $constraint->aspectRatio();
                            })->save($basePath.'360/'.$name);
                        }
                        $image_id = BlogImages::insertGetId(array('blog_id'=>$blog_id,'image'=>$name,'created_at'=>date('Y-m-d H:i:s'))); 
                    }else{
                        $category_data = Category::where('id',$category_id)->first();

                        $cat_files = url('upload/category/original/'.$category_data->image);
                        $exten = \Helpers::get_file_extension($cat_files);
                        $name = time() . rand() .'.'.$exten;
                        $destination =  public_path('/upload/blog/banner/original/').$name;
                        $basePath = public_path('/upload/blog/banner/');
                        $info = getimagesize($cat_files);
                        if ($info['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($cat_files);
                        elseif ($info['mime'] == 'image/gif') $image = imagecreatefromgif($cat_files);
                        elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($cat_files);
                        $c = imagejpeg($image, $destination, 30);
                        $img = Image::make($destination);

                        $img->resize(800, null, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($basePath.'800/'.$name);

                        $img->resize(360, null, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($basePath.'360/'.$name);

                        $image_id = BlogImages::insertGetId(array('blog_id'=>$blog_id,'image'=>$name,'created_at'=>date('Y-m-d H:i:s'))); 
                    }
		    	}
			}		   	
		}    	   
        return back()->with('success',__('message_alerts.post_saved_success'));
    }

    /**
     * Show Category view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addUpdateRssFeedSrc(Request $request)
    {
        $post = $request->all();
        if(!empty($post)){
            if(!isset($post['id'])){
                if (Gate::check('rss-feed-create')) {
                    $post['created_at'] = date('Y-m-d h:i:s');
                    $id = RssFeed::addRssFeed($post);
                    $msg = __('message_alerts.record_inserted');
                }else{
                    return response(\Helpers::sendFailureAjaxResponse('User does not have a right permission.'));
                }
            }else{
                if (Gate::check('rss-feed-edit')) {
                    $post['updated_at'] = date('Y-m-d h:i:s');
                    $id = RssFeed::updateRssFeed($post);
                    $msg = __('message_alerts.record_updated');
                }else{
                    return response(\Helpers::sendFailureAjaxResponse('User does not have a right permission.'));
                }
            }            
            return array('success'=>true,'data'=>$id,'message'=>$msg);
        }else{
            return array('success'=>false,'data'=>null,'message'=>__('message_alerts.something_went_wrong'));
        }
    }

    /**
     * Method to delete rss feed
     * @param array $request post data, id
    */
    public function deleteRssFeed(Request $request,$id)
    {
        RssFeed::where('id', $id)->delete();      
        return back()->with('success',__('message_alerts.rss_feed_deleted_success'));
    }
    /**
     * Method to change status of rss feed
     * @param array $request post data ,id ,status
    */
    public function changeRssFeedStatus(Request $request,$id,$status)
    {
        $data = RssFeed::where('id',$id)->first();
        $isCategoryExists = Category::where('id', $data->category_id)->first();   
        if ($isCategoryExists) {
            $post['status'] = $status;
            $post['id'] = $id;
            RssFeed::updateRssFeed($post);         
            return back()->with('success',__('message_alerts.status_changed_success'));  
        }else{
            return back()->with('failure', 'Please assgin category');  
        }     
        
	}
	
	public function getFeeds(Request $request){
        try {
            if($request->ajax()){
                $post = $request->all();
                if(isset($post['category_id']) && $post['category_id']!=''){
                    $data = RssFeed::where('category_id',$post['category_id'])->where('status',1)->get();
                }else{
                    $data = RssFeed::where('status',1)->get();
                }
                // $name = '';
                // if($post['image']!=''){
                //     $file=$request->file('image');
                //     $name = time() . rand() .'.'.$file->getClientOriginalExtension();
                //     $destination =  public_path('/upload/user/').$name;
                //     $c = \Helpers::compress_image($file,$destination,30);
                // }
                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'),$data));
            }else{
              return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }
}
