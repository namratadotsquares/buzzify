<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CmsPagesTranslation;
use App\Models\Languages;
use App\Models\CmsPages;
use Illuminate\Support\Facades\Validator;
class CmsPagesTranslationController extends Controller
{

    // function __construct(){
    //      $this->middleware('permission:cms-translate|cms-translate-show|cms-translate-store', ['only' => ['index','show','store']]);
    //      $this->middleware('permission:cms-translate', ['only' => ['index']]);
    //      $this->middleware('permission:cms-translate-show', ['only' => ['show']]);
    //      $this->middleware('permission:cms-translate-store', ['only' => ['store']]);
    // }


     /**
     * Show Edit Page view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index($layout = 'side-menu', $theme = 'light',$id=0)
    {
        $data = CmsPages::find($id);
    	   $languages =  Languages::get();
        $page_translate = CmsPagesTranslation::where('cms_id',$id)->where('language_code',setting('preferred_site_language'))->first();

        return view('super-admin/cms.edit-translation', [
            'theme' => $theme,
            'page_name' => 'create',
            'side_menu' => array(),
            'layout' => $layout,
            'data'=>$data,
            'languages'=>$languages,
            'page_translate'=>$page_translate,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans('admin.dashboard').'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/cms-pages/side-menu/light').'" class="breadcrumb">'.trans('admin.cms_pages').'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/edit-cms-page-translation').'/'.$id.'" class="breadcrumb--active">'.$data->page_title.' '.trans('admin.translation').'</a>'

        ]);
    }

     	/**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        try {
            if($request->ajax()){
                $post = $request->all();
                $validate = [
                    'cms_id' => 'required',
                    'language_code' => 'required',
                ];
                $validator = Validator::make($post, $validate);
                if ($validator->fails()) {
                     $data['error'] = $validator->errors();
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.required_field_missing')));
                }else{
                   	$data = CmsPagesTranslation::where('cms_id',$post['cms_id'])->where('language_code',$post['language_code'])->first();
                    if($data){
                      return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_found'),$data));
                    }else{
                      $cmspage = CmsPages::find($post['cms_id']);
                      if($cmspage){
                        $inject = [
                          'cms_id'=>$post['cms_id'],
                          'language_code'=>$post['language_code'],
                          'description'=>$cmspage->description,
                          'title'=>$cmspage->title,
                          'page_title'=>$cmspage->page_title,
                          'meta_char'=>$cmspage->meta_char,
                          'meta_desc'=>$cmspage->meta_desc
                        ];
                        $inject['created_at'] = date('Y-m-d h:i s');
                        CmsPagesTranslation::insertGetId($inject);
                        $data = CmsPagesTranslation::where('cms_id',$post['cms_id'])->where('language_code',$post['language_code'])->first();
                        return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_found'),$data));
                      }else{
                        return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
                      }
                    }
                }
            }else{
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }

   	/**
     * store data
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            if($request->ajax()){
                $post = $request->all();
                $validate = [
                    'cms_id' => 'required',
                    'language_code' => 'required',
                ];

                $validator = Validator::make($post, $validate);
                if ($validator->fails()) {
                     $data['error'] = $validator->errors();
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.required_field_missing')));
                }else{
                   	$exist = CmsPagesTranslation::where('cms_id',$post['cms_id'])->where('language_code',$post['language_code'])->first();

                   	$inject = [
                   		'cms_id'=>$post['cms_id'],
                   		'language_code'=>$post['language_code'],
                   		'description'=>$post['description'],
                   		'title' => $post['title'],
                   		'page_title' => $post['title'],
                   		'meta_char' => $post['title'],
                   		'meta_desc' => $this->limit_text($post['description'], 20),
                   	];
                   	if ($exist) {
                   		CmsPagesTranslation::where('id',$exist->id)->update($inject);
                   		$id = $exist->id;
                   	}else{
                   		$inject['created_at'] = date('Y-m-d h:i s');
                   		$id = CmsPagesTranslation::insertGetId($inject);
                   	}
                   	if ($id) {
                    	return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'),$id));
                   	}else{
                    	return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
                   	}
                }
            }else{
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) { dd($ex);
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }

    function limit_text($text, $limit) {
    if (str_word_count($text, 0) > $limit) {
        $words = str_word_count($text, 2);
        $pos   = array_keys($words);
        $text  = substr($text, 0, $pos[$limit]) . '...';
    }
    return $text;
}

}
