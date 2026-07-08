<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\CategoryTranslation;
use Illuminate\Support\Facades\Validator;

class CategoryTranslationController extends Controller
{

    function __construct(){
        //$this->middleware('permission:category-translate-show|category-translate-store', ['only' => ['show','store']]);
        $this->middleware('permission:category-translate', ['only' => ['show']]);
        //$this->middleware('permission:category-translate-store', ['only' => ['store']]);
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
                    'category_id' => 'required',
                    'language_code' => 'required',
                ];
                $validator = Validator::make($post, $validate);
                if ($validator->fails()) {
                     $data['error'] = $validator->errors();
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.required_field_missing')));
                }else{
                   	$data = CategoryTranslation::where('category_id',$post['category_id'])->where('language_code',$post['language_code'])->first();
                    if($data){
                      return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_found'),$data));
                    }else{
                      $categorys = Category::find($post['category_id']);
                      if($categorys){
                        $inject = [
                          'category_id'=>$post['category_id'],
                          'language_code'=>$post['language_code'],
                          'name'=>$categorys->name
                        ];
                        $inject['created_at'] = date('Y-m-d h:i s');
                        CategoryTranslation::insertGetId($inject);
                        $data = CategoryTranslation::where('category_id',$post['category_id'])->where('language_code',$post['language_code'])->first();
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
                    'category_id' => 'required',
                    'language_code' => 'required',
                    'name' => 'required',
                ];
                $validator = Validator::make($post, $validate);
                if ($validator->fails()) {
                     $data['error'] = $validator->errors();
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.required_field_missing')));
                }else{
                   	$exist = CategoryTranslation::where('category_id',$post['category_id'])->where('language_code',$post['language_code'])->first();

                   	$inject = [
                   		'category_id'=>$post['category_id'],
                   		'language_code'=>$post['language_code'],
                   		'name'=>$post['name'],
                   	];
                   	if ($exist) {
                   		CategoryTranslation::where('id',$exist->id)->update($inject);
                   		$id = $exist->id;
                   	}else{
                   		$inject['created_at'] = date('Y-m-d h:i s');
                   		$id = CategoryTranslation::insertGetId($inject);
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
