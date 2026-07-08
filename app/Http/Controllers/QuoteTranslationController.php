<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quote;
use App\Models\QuoteTranslation;
use Illuminate\Support\Facades\Validator;
class QuoteTranslationController extends Controller
{
    


    public function show(Request $request)
    {
        try {
            if($request->ajax()){
                $post = $request->all();
                $validate = [
                    'quote_id' => 'required',
                    'language_code' => 'required',
                ];
                $validator = Validator::make($post, $validate);
                if ($validator->fails()) {
                     $data['error'] = $validator->errors();
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.required_field_missing')));
                }else{
                   		$Quote = Quote::find($post['quote_id']);
	                  	if($Quote){
		                    $inject = [
		                      'quote_id'=>$post['quote_id'],
		                      'language_code'=>$post['language_code'],
		                      'quote'=>$Quote->quote,
		                    ];
		                    $inject['created_at'] = date('Y-m-d h:i s');
		                    QuoteTranslation::insertGetId($inject);
		                    $data = QuoteTranslation::where('quote_id',$post['quote_id'])->where('language_code',$post['language_code'])->first();
		                    return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_found'),$data));
	                  	}else{
		                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
		                }
                }
            }else{
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error').$ex));
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
            $post = $request->all();
            $validate = [
                'quote_id' => 'required',
                'language_code' => 'required',
                'quote' => 'required',
           
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                 $data['error'] = $validator->errors();
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.required_field_missing')));
            }else{
               	$exist = QuoteTranslation::where('quote_id',$post['quote_id'])->where('language_code',$post['language_code'])->first();

               	$inject = [
               		'quote_id'=>$post['quote_id'],
               		'language_code'=>$post['language_code'],
               		'quote'=>$post['quote'],
               	];

               	if ($exist) {
               		QuoteTranslation::where('id',$exist->id)->update($inject);
               		$id = $exist->id;
               	}else{
               		$inject['created_at'] = date('Y-m-d h:i s');
               		$id = QuoteTranslation::insertGetId($inject);
               	}
               	if ($id) {
               		 return redirect()->back()->with('success',__('message_alerts.record_updated'));
               	}else{
               		 return redirect()->back()->with('failure',__('message_alerts.there_is_an_error'));
               	}
            }
        
        } catch (\Exception $ex) {
            return redirect()->back()->with('failure',$ex->getMessage());

        }
    }

}
