<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\Ads;
use App\Models\Ads_images;
use App\Models\NewsAds;
use App\Models\Languages;
use App\Models\Ads_action;

use Illuminate\Support\Facades\Storage;

class AdsController extends Controller
{
    // function __construct(){
    //   $this->middleware('permission:news-ads-list|news-ads-create|news-ads-edit|news-ads-status|news-ads-analytics', ['only' => ['index','add','update','changeAdStatus','deleteAd','analytics']]);
    //   $this->middleware('permission:news-ads-create', ['only' => ['create','add']]);
    //   $this->middleware('permission:news-ads-edit', ['only' => ['editAd','update']]);
    //   $this->middleware('permission:news-ads-status', ['only' => ['changeAdStatus']]);
    //   $this->middleware('permission:news-ads-delete', ['only' => ['deleteAd']]);
    //   $this->middleware('permission:news-ads-analytics', ['only' => ['analytics']]);
    // }


    public function index(Request $request, $layout = 'side-menu', $theme = 'light')
    {
    
        $ads = NewsAds::paginate(10);
        return view('super-admin/news-ads.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'ads' => $ads,
            'breadcrumb' => '<a href="' . url('/') . '" class="breadcrumb">' . trans("admin.dashboard") . '</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="' . url('ads/side-menu/light?post=all') . '" class="breadcrumb--active">Ads</a>'
        ]);
    }

    public function create($layout = 'side-menu', $theme = 'light')
    {

        $Languages = Languages::get();

        return view('super-admin/news-ads.create', [
            'theme' => $theme,
            'page_name' => 'create',
            'side_menu' => array(),
            'layout' => $layout,

            'languages'=>$Languages,
            'voice_accent'=>config('constant.voice_accent'),

            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans("admin.dashboard").'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('ads/side-menu/light?post=all').'" class="breadcrumb">Ads</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/create-add/side-menu/light').'/" class="breadcrumb--active">News Ads Create</a>'

        ]);
    }

    
    public function add(Request $request, $layout = 'side-menu', $theme = 'light')
    {
        $post = $request->all();
       
        $request->validate([
            'url' => 'required',
            'frequency' => 'required|integer|min:1|max:1000',
            'image' => 'required',
        ]);
    
        // Get the single image from the request
        $image = $request->file('image');
    
        // Store the image in the database or storage
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $image->storeAs('storage/newsads', $imageName);
        
        
     
        $postArr = array(
            'url' => $post['url'],
            'frequency' => (int) $post['frequency'],
            'image' => $imageName, // Store the single image name
            'created_at' => date('Y-m-d H:i:s'),
        );
        
        if ($id = NewsAds::insertGetId($postArr)) {
            return redirect('/news-ads/' . $layout . '/' . $theme)->with('success', 'News ad added successfully');
        } else {
            return redirect('/news-ads/' . $layout . '/' . $theme)->with('error', 'Something went wrong');
        }
    }


    public function editAd($layout = 'side-menu', $theme = 'light',$id=0)
    {
        $Ads = NewsAds::where('id',$id)->with('media')->first();
        return view('super-admin/news-ads.edit', [
            'theme' => $theme,
            'page_name' => 'create',
            'side_menu' => array(),
            'layout' => $layout,

            'voice_accent'=>config('constant.voice_accent'),


            'ads'=>$Ads,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">Dashboard</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('ads/side-menu/light?post=all').'" class="breadcrumb">Ads</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/edit-ad/side-menu/light').'/'.$id.'" class="breadcrumb--active">Edit Ads</a>'

        ]);
    }
//     public function update(Request $request,$layout = 'side-menu', $theme = 'light')
//     {
//         $post = $request->all();
//         $request->validate([
//             'url'=> 'required',
//             'image' => 'required',
//         ]);
//         unset($request['_token']);
//         $postArr = array(
//             'url'=> $post['url'],
//             'image' => $post['image'],
//         );
//       unset($request['_token']);
//         if($id = NewsAds::where('id', $post['id'])->update($postArr)) {
//             if(isset($post['images_name']) && count($post['images_name'])>0){
//                 // Ads_images::where('ad_id',$post['id'])->where('type','image')->delete();
//                 foreach($post['images_name'] as $images){
//                     $find_ad = Ads_images::where('file',$images)->where('type','image')->first();
//                     if(!$find_ad){
//                         $imgArr = array(
//                             'ad_id'=>$post['id'],
//                             'type'=>'image',
//                             'file'=>$images,
//                             'created_at'=>date('Y-m-d H:i:s')
//                         );
//                         Ads_images::insertGetId($imgArr);
//                     }
//                 }
//             }
//             return redirect('/news-ads/'.$layout.'/'.$theme)->with('success','Ad Update successfully');
//         } else {
//             return redirect('/news-ads/'.$layout.'/'.$theme)->with('error','somthing went wrong');
//         }
// }

    // public function updateold(Request $request, $layout = 'side-menu', $theme = 'light')
    // {
    //     // Validate the incoming request
    //     $request->validate([
    //         'url' => 'required',
    //         'image' => 'required',
    //     ]);
    
    //     // Prepare the data for updating
    //     $postArr = [
    //         'url' => $request->input('url'),
    //         'image' => $request->input('image'),
    //     ];
    
    //     // Update the record in the NewsAds table
    //     $updated = NewsAds::where('id', $request->input('id'))->update($postArr);
    
    //     // Check if the update was successful and redirect accordingly
    //     if ($updated) {
    //         return redirect('/news-ads/' . $layout . '/' . $theme)->with('success', 'Ad updated successfully');
    //     } else {
    //         return redirect('/news-ads/' . $layout . '/' . $theme)->with('error', 'Something went wrong');
    //     }
    // }
    public function update(Request $request,$layout = 'side-menu', $theme = 'light')
    
    {
    //     $post = $request->all();
    //     // dd($post);
    //     $request->validate([
    //         'url'=> 'required',
    //         // 'images' => 'required',
    //     ]);
    //     unset($request['_token']);
    //     $postArr = array(
    //         'url'=> $post['url'],
    //         'image' => $post['images_name'][0],
    //     );
    // //  dd($postArr);
    //     if($id = NewsAds::where('id', $post['id'])->update($postArr)) {
    //         if(isset($post['images_name']) && count($post['images_name'])>0){
    //             // Ads_images::where('ad_id',$post['id'])->where('type','image')->delete();
    //             foreach($post['images_name'] as $images){
    //                 $find_ad = Ads_images::where('file',$images)->where('type','image')->first();
    //                 if(!$find_ad){
    //                     $imgArr = array(
    //                         'ad_id'=>$post['id'],
    //                         'type'=>'image',
    //                         'file'=>$images,
    //                         'created_at'=>date('Y-m-d H:i:s')
    //                     );
    //                     Ads_images::insertGetId($imgArr);
    //                 }
    //             }
    //         }
    //         return redirect('/news-ads/'.$layout.'/'.$theme)->with('success','Ad Update successfully');
    //     } else {
    //         return redirect('/news-ads/'.$layout.'/'.$theme)->with('error','somthing went wrong');
    //     }
     $post = $request->all();
        $request->validate([
            'url' => 'required',
            'frequency' => 'required|integer|min:1|max:1000',
            // 'image' => 'required',
        ]);
    
        // Get the single image from the request
        $image = $request->file('image');
        if($request->hasFile('image')){
        // Store the image in the database or storage
         $imageName = time() . '.' . $image->getClientOriginalExtension();
         $image->storeAs('storage/newsads', $imageName);
         $postArr = array(
             'url' => $post['url'],
             'frequency' => (int) $post['frequency'],
             'image' => $imageName, 
         );
         }else{
             $postArr = array(
             'url' => $post['url'],
             'frequency' => (int) $post['frequency'],
         );
             
         }
        
        
        if ($id =NewsAds::where('id', $post['id'])->update($postArr)) {
            return redirect('/news-ads/'.$layout.'/'.$theme)->with('success','Ad Update successfully');
        } else {
            return redirect('/news-ads/'.$layout.'/'.$theme)->with('error','somthing went wrong');
        }
    }

    public function changeNewsAdStatus($id,$status,Request $request)
    {
        $post['status'] = $status;
        $post['id'] = $id;
        NewsAds::where('id',$id)->update(['status' =>$status]);
        return back()->with('success',__('message_alerts.status_changed_success'));
    }

    public function deleteNewsAd(Request $request,$id)
    {
        // return dd($id);
        NewsAds::where('id', $id)->delete();
        return back()->with('success',__('message_alerts.blog_deleted_success'));
    }

     public function upload(Request $request){
        if($request->hasFile('files')){
            $file_input = $request->file('files');
            $imagesArry = [];
            $url = explode(',',$request->url);
            foreach ($file_input as $key => $item) {
                $adObj = new Ads_images();
                // Get filename with the extension
                $filenameWithExt = $item->getClientOriginalName();
                // Get just filename
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                // Get just ext
                $extension = $item->getClientOriginalExtension();
                $size = $item->getSize();
                // Filename to store
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                // Upload Image
                $path = $item->storeAs('storage/ads', $fileNameToStore);

                // set DB values
                $adObj->adId = $request->adId;
                $adObj->original_name = $filenameWithExt;
                $adObj->stored_name = $fileNameToStore;
                $adObj->location = 'storage/ads/'.$fileNameToStore;
                $adObj->extension = $extension;
                $adObj->size = $size;
                $adObj->redirectUrl = $url[$key];
                $adObj->save();
            }
        }
        return 'Upload Successfully';
    }

    public function uploadImages(Request $request){
        try {
            if($request->ajax()){
                $post = $request->all();
                $data['images']=[];
                $data['images_url']=[];
                $files=$request->file('images');
                foreach($files as $file){
                    $name = time() . rand() .'.'.$file->getClientOriginalExtension();
                    array_push($data['images'], $name);
                    $destination =  public_path('/storage/ads/');
                    $basePath = public_path('/storage/ads/');
                    // $c = \Helpers::compress_image($file,$destination,30,$name,$basePath,true);
                    $c = $file->move($destination, $name); 
                    if ($c) {
                        $img_url = url('/storage/ads/'.$name);
                        array_push($data['images_url'], $img_url);
                    }
                }
                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'),$data));
            }else{
              return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error'.$ex)));
        }
    }

    public function reArrange(Request $request){
       $orderArray = explode(',',$request->ids);
       $urlArray = explode(',',$request->url);
       foreach ($orderArray as $key=> $item){
           Ads_images::where('adID',$request->adID)->where('id',$item)->update([
               'img_order' => $key+1,
               'redirectUrl' => $urlArray[$key],
           ]);
       }
        return true;
    }

    public function addImages($id){
        $ads = [];
        $ads['id'] = $id;
        $layout = 'side-menu'; $theme = 'light';
        return view('super-admin.ads.slider',['ads' => $ads,'theme' => $theme,
            'side_menu' => array(),
            'layout' => $layout,]);
    }


    public function analytics(Request $request,$id,$layout = 'side-menu', $theme = 'light'){
        $analytics['Ads'] = NewsAds::where('id',$id)->orderBy('id','DESC')->first()->toArray();
        if(isset($request->start_date) && isset($request->end_date)){
            $from = date($request->start_date);
        $to = date($request->end_date);
        $ads_views = Ads_action::whereBetween('created_at', [$from, $to])->where('action','0')->where('AdsID',$id)->with('users')->get()->toArray();
         $ads_clicks = Ads_action::whereBetween('created_at', [$from, $to])->where('action','1')->where('AdsID',$id)->with('users')->get()->toArray();
        }else{
            
        $ads_views = Ads_action::where('action','0')->where('AdsID',$id)->with('users')->get()->toArray();
        $ads_clicks = Ads_action::where('action','1')->where('AdsID',$id)->with('users')->get()->toArray();

        }
        return view('super-admin.ads.analytics',compact('analytics','layout','theme','id','ads_views','ads_clicks'));
    }
    
        /**
     * update category
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function sortAds(Request $request){
        $posts = NewsAds::all();
        foreach ($posts as $post) {
            foreach ($request->order as $order) {
                if ($order['id'] == $post->id) {
                    $c = NewsAds::where('id',$post->id)->update(['order' => $order['position']]);
                    
                }
            }
        }
        return response(__('message_alerts.record_updated'), 200);
    }

    public function updatePosition(Request $request){
        $posts = Ads_images::where('ad_id',$request->ad_id)->get();
        foreach ($posts as $post) {
            foreach ($request->order as $order) {
                if ($order['id'] == $post->id) {
                    $c = Ads_images::where('id',$post->id)->update(['position' => $order['position']]);
                }
            }
        }
        return response(__('message_alerts.record_updated'), 200);
    }

    public function changeOrder(Request $request,$layout = 'side-menu', $theme = 'light',$id=0)
    {
        $Ads = NewsAds::where('id',$id)->with('media')->first();
        $media_list = Ads_images::getAllAdsImages($request->all(),$id);
        return view('super-admin/ads.change-order', [
            'theme' => $theme,
            'page_name' => 'change order',
            'side_menu' => array(),
            'layout' => $layout,
            'voice_accent'=>config('constant.voice_accent'),
            'ads'=>$Ads,
            'media'=>$media_list,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">Dashboard</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('ads/side-menu/light?post=all').'" class="breadcrumb">Ads</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/edit-ad/side-menu/light').'/'.$id.'" class="breadcrumb--active">Change Order of Ads Images</a>'

        ]);
    }

    public function showPreview(Request $request,$layout = 'side-menu', $theme = 'light',$id=0)
    {
        $Ads = NewsAds::where('id',$id)->with('media')->first();
        $media_list = Ads_images::getAllAdsImages($request->all(),$id);
        return view('super-admin/ads.change-order', [
            'theme' => $theme,
            'page_name' => 'preview',
            'side_menu' => array(),
            'layout' => $layout,
            'voice_accent'=>config('constant.voice_accent'),
            'ads'=>$Ads,
            'media'=>$media_list,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">Dashboard</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('ads/side-menu/light?post=all').'" class="breadcrumb">Ads</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/edit-ad/side-menu/light').'/'.$id.'" class="breadcrumb--active">Change Order of Ads Images</a>'

        ]);
    }

    public function addUpdateRedirectedUrl(Request $request){
        try {
            if($request->ajax()){
                $post = $request->all();
                $postArr = array(
                    'redirected_url'=>$post['redirected_url'],
                );
                Ads_images::where('id', $post['id'])->update($postArr);
                return response(\Helpers::sendSuccessAjaxResponse("Data updated successfully."));
            }else{
              return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error'.$ex)));
        }
        NewsAds::where('id', $id)->delete();
        return back()->with('success',__('message_alerts.blog_deleted_success'));
    }
}
