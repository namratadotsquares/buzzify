<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Epaper;
use App\Models\EpaperTranslation;
use Illuminate\Support\Facades\Validator;
class EpaperTranslationController extends Controller
{
    
    // function __construct(){
    //      $this->middleware('permission:e-paper-translate-show|e-paper-translate-store', ['only' => ['show','store']]);
    //      $this->middleware('permission:e-paper-translate-show', ['only' => ['show']]);
    //      $this->middleware('permission:e-paper-translate-store', ['only' => ['store']]);
    // }


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
                    'e_paper_id' => 'required',
                    'language_code' => 'required',
                ];
                $validator = Validator::make($post, $validate);
                if ($validator->fails()) {
                     $data['error'] = $validator->errors();
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.required_field_missing')));
                }else{
                   	$data = EpaperTranslation::where('e_paper_id',$post['e_paper_id'])->where('language_code',$post['language_code'])->first();
                   	if ($data) {
                   		if(file_exists(public_path()."/upload/e-paper/pdf/".$data->pdf) && $data->pdf!='') { 
                            $data->pdf_file = url('upload/e-paper/pdf').'/'.$data->pdf;
                            $data->pdf_exist = true;
                      }
                      return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_found'),$data));
                   	}else{
                      $epapers = Epaper::find($post['e_paper_id']);
                      if($epapers){
                        $inject = [
                          'e_paper_id'=>$post['e_paper_id'],
                          'language_code'=>$post['language_code'],
                          'paper_name'=>$epapers->paper_name,
                          'pdf'=>$epapers->pdf
                        ];
                        $inject['created_at'] = date('Y-m-d h:i s');
                        EpaperTranslation::insertGetId($inject);
                        $data = EpaperTranslation::where('e_paper_id',$post['e_paper_id'])->where('language_code',$post['language_code'])->first();
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
                    'e_paper_id' => 'required',
                    'language_code' => 'required',
                    'paper_name' => 'required',
                    // 'upload_file' => 'required',
                ];
                $validator = Validator::make($post, $validate);
                if ($validator->fails()) {
                     $data['error'] = $validator->errors();
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.required_field_missing')));
                }else{
                   	$exist = EpaperTranslation::where('e_paper_id',$post['e_paper_id'])->where('language_code',$post['language_code'])->first();

                   	$inject = [
                   		'e_paper_id'=>$post['e_paper_id'],
                   		'language_code'=>$post['language_code'],
                   		'paper_name'=>$post['paper_name'],
                   	];

                   	if (isset($post['upload_file'])) {
                   		$inject['pdf']=$post['upload_file'];
                   	}

                   	if ($exist) {
                   		EpaperTranslation::where('id',$exist->id)->update($inject);
                   		$id = $exist->id;
                   	}else{
                   		$inject['created_at'] = date('Y-m-d h:i s');
                   		$id = EpaperTranslation::insertGetId($inject);
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
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }
}
