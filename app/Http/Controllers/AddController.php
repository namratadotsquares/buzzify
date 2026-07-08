<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\Ads;
use App\Models\Ads_images;
use App\Models\Languages;
use App\Models\Ads_action;

use Illuminate\Support\Facades\Storage;

class AddController extends Controller
{
    function __construct(){
       $this->middleware('permission:ads-list|ads-create|ads-edit|ads-status|ads-analytics', ['only' => ['index','add','update','changeAdStatus','deleteAd','analytics']]);
       $this->middleware('permission:ads-create', ['only' => ['create','add']]);
       $this->middleware('permission:ads-edit', ['only' => ['editAd','update']]);
       $this->middleware('permission:ads-status', ['only' => ['changeAdStatus']]);
       $this->middleware('permission:ads-delete', ['only' => ['deleteAd']]);
       $this->middleware('permission:ads-analytics', ['only' => ['analytics']]);
    }


    /**
     * Show Ad view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$layout = 'side-menu', $theme = 'light')
    {
        $ads = new Ads();
        $ads = $ads->getAllAds($request->all());
        return view('super-admin/ads.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'ads'=>$ads,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans("admin.dashboard").'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('ads/side-menu/light?post=all').'" class="breadcrumb--active">Ads</a>'
        ]);
    }

    public function create($layout = 'side-menu', $theme = 'light')
    {

        $Languages = Languages::get();

        return view('super-admin/ads.create', [
            'theme' => $theme,
            'page_name' => 'create',
            'side_menu' => array(),
            'layout' => $layout,

            'languages'=>$Languages,
            'voice_accent'=>config('constant.voice_accent'),

            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans("admin.dashboard").'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('ads/side-menu/light?post=all').'" class="breadcrumb">Ads</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/create-add/side-menu/light').'/" class="breadcrumb--active">Ads Create</a>'

        ]);
    }
    public function add(Request $request,$layout = 'side-menu', $theme = 'light')
    {
        $post = $request->all();
        $request->validate([
            'title'=> 'required',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'frequency'=> 'required',
            'url'=> 'required',
        ]);
        $postArr = array(
            'title'=> $post['title'],
            'frequency'=> $post['frequency'],
            'url'=> $post['url'],
            'start_date' => $post['start_date'],
            'end_date' => $post['end_date'],
        );
        unset($request['_token']);
        if($id = Ads::insertGetId($postArr)) {
            if(isset($post['images_name']) && count($post['images_name'])>0){
                foreach($post['images_name'] as $images){
                    $imgArr = array(
                        'ad_id'=>$id,
                        'file'=>$images,
                        'created_at'=>date('Y-m-d H:i:s')
                    );
                    Ads_images::insertGetId($imgArr);
                }
            }
            return redirect('/ads/'.$layout.'/'.$theme)->with('success','Ad added successfully');
        } else {
            return redirect('/ads/'.$layout.'/'.$theme)->with('error','somthing went wrong');
        }
        // if($id = Ads::insertGetId($request->all())) {
        //     return redirect()->route('add-slider',$id);
        // } else {
        //     return redirect('/ads/'.$layout.'/'.$theme)->with('error','somthing went wrong');
        // }

    }
    public function editAd($layout = 'side-menu', $theme = 'light',$id=0)
    {
        $Ads = Ads::where('id',$id)->with('media')->first();
        return view('super-admin/ads.edit', [
            'theme' => $theme,
            'page_name' => 'create',
            'side_menu' => array(),
            'layout' => $layout,

            'voice_accent'=>config('constant.voice_accent'),


            'ads'=>$Ads,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">Dashboard</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('ads/side-menu/light?post=all').'" class="breadcrumb">Ads</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/edit-ad/side-menu/light').'/'.$id.'" class="breadcrumb--active">Edit Ads</a>'

        ]);
    }
    public function update(Request $request,$layout = 'side-menu', $theme = 'light')
    {
        $post = $request->all();
        $request->validate([
            'title'=> 'required',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'frequency'=> 'required',
            'url'=> 'required',
        ]);
        unset($request['_token']);
        $postArr = array(
            'title'=> $post['title'],
            'start_date' => $post['start_date'],
            'end_date' => $post['end_date'],
            'frequency'=> $post['frequency'],
            'url'=> $post['url'],
        );
       unset($request['_token']);
        if($id = Ads::where('id', $post['id'])->update($postArr)) {
            if(isset($post['images_name']) && count($post['images_name'])>0){
                // Ads_images::where('ad_id',$post['id'])->where('type','image')->delete();
                foreach($post['images_name'] as $images){
                    $find_ad = Ads_images::where('file',$images)->where('type','image')->first();
                    if(!$find_ad){
                        $imgArr = array(
                            'ad_id'=>$post['id'],
                            'type'=>'image',
                            'file'=>$images,
                            'created_at'=>date('Y-m-d H:i:s')
                        );
                        Ads_images::insertGetId($imgArr);
                    }
                }
            }
            return redirect('/ads/'.$layout.'/'.$theme)->with('success','Ad Update successfully');
        } else {
            return redirect('/ads/'.$layout.'/'.$theme)->with('error','somthing went wrong');
        }
        // if($id = Ads::where('id', $request['id'])->update($request->all())) {
        //     return redirect('/ads/'.$layout.'/'.$theme)->with('success','Ad Update successfully');
        // } else {
        //     return redirect('/ads/'.$layout.'/'.$theme)->with('error','somthing went wrong');
        // }

}
    /**
     * Method to change status of Ad
     * @param array $request post data ,id ,status
     */
    public function changeAdStatus($id,$status,Request $request)
    {
        $post['status'] = $status;
        $post['id'] = $id;
        Ads::where('id',$id)->update(['status' =>$status]);
        return back()->with('success',__('message_alerts.status_changed_success'));
    }

    public function deleteAd(Request $request,$id)
    {
        Ads::where('id', $id)->delete();
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

    public function deleteAddImage($id){
        $image = Ads_images::where('id',$id)->first();
        Storage::delete('/public/ads/'.$image->stored_name);
        $image->delete();
        $ads = [];
        $ads['id'] = $id;
        $layout = 'side-menu'; $theme = 'light';
        return back();
    }

    public function analytics(Request $request,$id,$layout = 'side-menu', $theme = 'light'){
        $analytics['Ads'] = Ads::where('id',$id)->orderBy('id','DESC')->first()->toArray();
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
        $posts = Ads::all();
        foreach ($posts as $post) {
            foreach ($request->order as $order) {
                if ($order['id'] == $post->id) {
                    $c = Ads::where('id',$post->id)->update(['order' => $order['position']]);
                    
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
        $Ads = Ads::where('id',$id)->with('media')->first();
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
        $Ads = Ads::where('id',$id)->with('media')->first();
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

    public function deleteAdImage(Request $request){
        try {
            if($request->ajax()){
                $post = $request->all();
                Ads_images::where('id', $post['id'])->delete();
                return response(\Helpers::sendSuccessAjaxResponse("Data deleted successfully."));
            }else{
              return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error'.$ex)));
        }
        Ads::where('id', $id)->delete();
        return back()->with('success',__('message_alerts.blog_deleted_success'));
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
        Ads::where('id', $id)->delete();
        return back()->with('success',__('message_alerts.blog_deleted_success'));
    }
}
