<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LiveNews;
use Illuminate\Support\Facades\Validator;
use App\Models\Languages;
use App\Models\LiveNewsTranslation;
use Illuminate\Support\Facades\Gate;

class LiveNewsController extends Controller
{
    

    function __construct(){
         $this->middleware('permission:live-news-list|live-news-delete|live-news-status', ['only' => ['index','deleteNews','changeNewsStatus']]);
         $this->middleware('permission:live-news-delete', ['only' => ['deleteNews']]);
         $this->middleware('permission:live-news-status', ['only' => ['changeNewsStatus']]);
    }


    /**
     * Show Category view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$layout = 'side-menu', $theme = 'light')
    {
        $news = LiveNews::getAllNews($request->all());
        $languages =  Languages::get();
        foreach ($news as $row) {
            $val = LiveNewsTranslation::where('live_news_id',$row->id)->where('language_code',setting('preferred_site_language'))->first();
            if ($val) {
                $row->language_code = $val->language_code;
                $row->company_name_trans = $val->company_name;
                $row->url_trans = $val->url;

            }else{
                $row->language_code = setting('preferred_site_language');
                $row->company_name_trans = $row->company_name;
                $row->url_trans = $row->url;
            }

        }

        return view('super-admin/live-news.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'languages'=>$languages,
            'News'=>$news,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans('admin.dashboard').'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/live_new/side-menu/light').'" class="breadcrumb--active">'.trans('admin.live_new_list').'</a>'
            
        ]);
    }

    /**
     * Show Category view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addUpdateNews(Request $request)
    {
        $post = $request->all();
        if(!empty($post)){

            if (isset($post['thumb_image']) && $post['thumb_image'] != '') {
               $post['image'] = $post['thumb_image'];
               unset($post['thumb_image']);
            }
            if(!isset($post['id'])){
                if (Gate::check('live-news-create')) {
                    $post['created_at'] = date('Y-m-d h:i:s');
                    $id = LiveNews::addNews($post);
    
                    $Languages = Languages::get();
                    foreach ($Languages as $lang) {
                        $inject = [
                                'live_news_id'=>$id['id'],
                                'language_code'=>$lang->language,
                                'company_name'=>$post['company_name'],
                                'url'=>$post['url'],
                                'created_at'=>date('Y-m-d h:i s'),
                            ];
                        LiveNewsTranslation::insertGetId($inject);
                    }
                    $msg = __('message_alerts.record_inserted');
                }else{
                    return response(\Helpers::sendFailureAjaxResponse('User does not have a right permission.'));
                }    
            }else{
                if (Gate::check('live-news-edit')) {
                    $post['updated_at'] = date('Y-m-d h:i:s');
                    $id = LiveNews::updateNews($post);
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
     * upload category thumb image
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function uploadLogo(Request $request){
        try {
            if($request->ajax()){
                $post = $request->all();
                $name = '';
                if($post['image']!=''){
                    $file=$request->file('image');
                    $name = time() . rand() .'.'.$file->getClientOriginalExtension();
                    $destination =  public_path('/upload/company-logo/original/').$name;
                    $c = \Helpers::compress_image($file,$destination,30);
                }
                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'),$name));
            }else{
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
    public function deleteNews(Request $request,$id)
    {
        LiveNews::where('id', $id)->delete();  
        LiveNewsTranslation::where('live_news_id',$id)->delete();    
        return back()->with('success',__('message_alerts.category_deleted_success'));
    }
    /**
     * Method to change status of category
     * @param array $request post data ,id ,status
    */
    public function changeNewsStatus(Request $request,$id,$status)
    {
        $post['status'] = $status;
        $post['id'] = $id;
        LiveNews::updateNews($post);         
        return back()->with('success',__('message_alerts.status_changed_success'));  
    }   


}
