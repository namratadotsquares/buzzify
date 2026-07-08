<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LiveNews;
use App\Models\LiveNewsTranslation;
use Illuminate\Support\Facades\Validator;

class LiveNewsTranslationController extends Controller
{
    

    function __construct(){
         $this->middleware('permission:live-news-translate', ['only' => ['show']]);
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
                    'live_news_id' => 'required',
                    'language_code' => 'required',
                ];
                $validator = Validator::make($post, $validate);
                if ($validator->fails()) {
                     $data['error'] = $validator->errors();
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.required_field_missing')));
                }else{
                   	$data = LiveNewsTranslation::where('live_news_id',$post['live_news_id'])->where('language_code',$post['language_code'])->first();
                    if($data){
                      return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_found'),$data));
                    }else{
                      $livenews = LiveNews::find($post['live_news_id']);
                      if($livenews){
                        $inject = [
                          'live_news_id'=>$post['live_news_id'],
                          'language_code'=>$post['language_code'],
                          'company_name'=>$livenews->company_name,
                          'url'=>$livenews->url
                        ];
                        $inject['created_at'] = date('Y-m-d h:i s');
                        LiveNewsTranslation::insertGetId($inject);
                        $data = LiveNewsTranslation::where('live_news_id',$post['live_news_id'])->where('language_code',$post['language_code'])->first();
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
                    'live_news_id' => 'required',
                    'language_code' => 'required',
                    'company_name' => 'required',
                    'url' => 'required',
                ];
                $validator = Validator::make($post, $validate);
                if ($validator->fails()) {
                     $data['error'] = $validator->errors();
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.required_field_missing')));
                }else{
                   	$exist = LiveNewsTranslation::where('live_news_id',$post['live_news_id'])->where('language_code',$post['language_code'])->first();

                   	$inject = [
                   		'live_news_id'=>$post['live_news_id'],
                   		'language_code'=>$post['language_code'],
                   		'company_name'=>$post['company_name'],
                   		'url'=>$post['url'],
                   	];
                   	if ($exist) {
                   		LiveNewsTranslation::where('id',$exist->id)->update($inject);
                   		$id = $exist->id;
                   	}else{
                   		$inject['created_at'] = date('Y-m-d h:i s');
                   		$id = LiveNewsTranslation::insertGetId($inject);
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
