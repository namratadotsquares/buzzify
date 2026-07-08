<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Epaper;
use Illuminate\Support\Facades\Validator;
use App\Models\Languages;
use App\Models\EpaperTranslation;
use Illuminate\Support\Facades\Gate;

class EpaperController extends Controller
{
    
    function __construct(){
         $this->middleware('permission:epaper-list|epaper-delete|epaper-status', ['only' => ['index','deleteEpaper','changeEpaperStatus']]);
         $this->middleware('permission:epaper-delete', ['only' => ['deleteEpaper']]);
         $this->middleware('permission:epaper-status', ['only' => ['changeEpaperStatus']]);
    }


    /**
     * Show Category view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$layout = 'side-menu', $theme = 'light')
    {

        $news = Epaper::getAllEpaper($request->all());
        $languages =  Languages::get();

        foreach ($news as $row) {
            $val = EpaperTranslation::where('e_paper_id',$row->id)->where('language_code',setting('preferred_site_language'))->first();

            if ($val) {
                $row->language_code = $val->language_code;
                $row->paper_name_trans = $val->paper_name;
                $row->pdf_name_trans = $val->pdf;
                if (is_file(public_path().'/upload/e-paper/pdf/'.$val->pdf)) {
                  $row->pdf_trans = url('/upload/e-paper/pdf/').'/'.$val->pdf;
                }else{
                  $row->pdf_trans = false;
                }
            }else{
                $row->language_code = setting('preferred_site_language');
                $row->paper_name_trans = $row->paper_name;
                $row->pdf_name_trans = $row->pdf;
                if (is_file(public_path().'/upload/e-paper/pdf/'.$row->pdf)) {
                  $row->pdf_trans = url('/upload/e-paper/pdf/').'/'.$row->pdf;
                }else{
                  $row->pdf_trans = false;
                }
            }
        }

        return view('super-admin/e-paper.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'languages'=>$languages,
            'News'=>$news,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">Dashboard</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/category/side-menu/light').'" class="breadcrumb--active">E-Paper List</a>'
            
        ]);
    }

    /**
     * Show Category view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addUpdateEpaper(Request $request)
    {
        $post = $request->all();
        if(!empty($post)){
            if (isset($post['thumb_image']) && $post['thumb_image'] != '') {
               $post['image'] = $post['thumb_image'];
               unset($post['thumb_image']);
            }
            if (isset($post['upload_file']) && $post['upload_file'] != '') {
               $post['pdf'] = $post['upload_file'];
            }

            unset($post['upload_file']);
            if(!isset($post['id'])){
              if (Gate::check('epaper-create')) {
                $post['created_at'] = date('Y-m-d h:i:s');
                $id = Epaper::addEpaper($post);
                if ($id['id']) {
                    $Languages = Languages::get();
                    foreach ($Languages as $lang) {
                        $inject = [
                                'e_paper_id'=>$id['id'],
                                'language_code'=>$lang->language,
                                'paper_name'=>$post['paper_name'],
                                'pdf'=>(isset($post['pdf']))?$post['pdf']:'',
                                'created_at'=>date('Y-m-d h:i s'),
                            ];
                        EpaperTranslation::insertGetId($inject);
                    }
                }else{
                    return array('success'=>false,'data'=>null,'message'=>__('message_alerts.something_went_wrong'));
                }
              
              $msg = __('message_alerts.record_inserted');
              }else{
                return response(\Helpers::sendFailureAjaxResponse('User does not have a right permission.'));
              }
            }else{
              if (Gate::check('epaper-edit')) {
                $post['updated_at'] = date('Y-m-d h:i:s');
                $id = Epaper::updateEpaper($post);
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
                    $destination =  public_path('/upload/e-paper/original/').$name;
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
    public function deleteEpaper(Request $request,$id)
    {
        Epaper::where('id', $id)->delete();  
        EpaperTranslation::where('e_paper_id',$id)->delete();    
        return back()->with('success',__('message_alerts.category_deleted_success'));
    }
    /**
     * Method to change status of category
     * @param array $request post data ,id ,status
    */
    public function changeEpaperStatus(Request $request,$id,$status)
    {
        $post['status'] = $status;
        $post['id'] = $id;
        Epaper::updateEpaper($post);         
        return back()->with('success',__('message_alerts.status_changed_success'));  
    }

    public function uploadPdf(Request $request){
        try {
            if($request->ajax()){
                $post = $request->all();
                $name = '';
                if($post['upload_file']!=''){
                    $file=$request->file('upload_file');
                    $name = time() . rand() .'.'.$file->getClientOriginalExtension();
                    $destination =  public_path('/upload/e-paper/pdf/').$name;
                    $file->move(public_path('/upload/e-paper/pdf/') , $name);
                }
                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'),$name));
            }else{
              return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }

}
