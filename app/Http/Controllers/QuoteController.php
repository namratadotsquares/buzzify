<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Quote;
use App\Models\QuoteTranslation;
use App\Models\Languages;
use App\Models\SiteContent;
use App\Models\User;
use App\Models\DeviceToken;
use Illuminate\Support\Facades\Gate;

class QuoteController extends Controller
{

    function __construct(){
        $this->middleware('permission:quote-list|quote-status|quote-delete', ['only' => ['index','changequoteStatus','deletequote']]);
        $this->middleware('permission:quote-create', ['only' => ['changequoteStatus']]);
        $this->middleware('permission:quote-delete', ['only' => ['deletequote']]);
    }



        /**
     * Show Category view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$layout = 'side-menu', $theme = 'light')
    {
        $quotes = Quote::getQuotes($request->all());
        $Languages = Languages::get();

        foreach ($quotes as $row) {
            $val = QuoteTranslation::where('quote_id',$row->id)->where('language_code',setting('preferred_site_language'))->first();

            if ($val) {
                $row->language_code = $val->language_code;
                $row->quote = $val->quote;
              
            }else{
                $row->language_code = setting('preferred_site_language');
                $row->quote = $row->quote;
            }
        }

        return view('super-admin/quote.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'quotes'=>$quotes,
            'languages'=>$Languages,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans('admin.dashboard').'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/quotes/side-menu/light').'" class="breadcrumb--active">'.trans('admin.quotes_list').'</a>'
            
        ]);
    }


    /**
     * add update quote
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      try {
            $post = $request->all();
            $validate = [
                'id' => 'required',
                'quote' => 'required',
                'schedule_date' => 'required',
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                 $data['error'] = $validator->errors();
                return redirect()->back()->with('failure',__('message_alerts.required_field_missing'));
            }else{
                $postData = [];

                if(isset($post['schedule_date']) && $post['schedule_date'] != ''){
                    if(isset($post['schedule_time']) && $post['schedule_time'] != ''){
                        $date = date("Y-m-d",strtotime($post['schedule_date']));
                        $time = date("H:i:s",strtotime($post['schedule_time']));
                        $postData['schedule_date'] = $date." ".$time;
                    }else if(isset($postData['schedule_date']) && !isset($postData['schedule_time'])){
                        $postData['schedule_date'] = date("Y-m-d H:i:s",strtotime($post['schedule_date']));
                    }else{
                        $postData['schedule_date'] = date("Y-m-d H:i:s");
                    }
                }else{
                    $postData['schedule_date'] = date("Y-m-d H:i:s");
                }
                $postData['quote'] = $post['quote'];

                if (isset($post['id'])&& $post['id'] > 0) {
                    if (Gate::check('quote-edit')) {
                       Quote::where('id',$post['id'])->update($postData);
                    }else{
                         return response(\Helpers::sendFailureAjaxResponse('User does not have a right permission.'));
                    }   
                }else{
                    if (Gate::check('quote-create')) {
                        $postData['created_at'] = date("Y-m-d H:i:s");
                        $id = Quote::insertGetId($postData);
                        $Languages = Languages::get();
                        foreach ($Languages as $lang) {
                            $inject = [
                                    'quote_id'=>$id,
                                    'language_code'=>$lang->language,
                                    'quote'=>$post['quote'],
                                    'created_at'=>date('Y-m-d h:i s'),
                                ];
                        QuoteTranslation::insertGetId($inject);
                        }
                    }else{
                         return response(\Helpers::sendFailureAjaxResponse('User does not have a right permission.'));
                    }        
                }
                if (setting('enable_notifications')) {
                    if(date("Y-m-d H:i:s",strtotime($post['schedule_date'])) <= date("Y-m-d H:i:s")){
                        $user = User::where('active',1)->get();
                        $setting = SiteContent::where('key','firebase_msg_key')->first();
                        foreach($user as $detail){
                            if($detail->device_token!=null){
                                \Helpers::sendNotification($detail->device_token,$post['quote'],'',$setting->value);
                            }
                        }
                        $non_logged_in = DeviceToken::get();
                        if(count($non_logged_in)){
                            foreach($non_logged_in as $non_logged_in_data){
                                if($non_logged_in_data->device_token!=null){
                                    \Helpers::sendNotification($non_logged_in_data->device_token,$post['quote'],'',$setting->value);
                                }
                            }
                        }
                    }
                }
                return redirect()->back()->with('success',__('message_alerts.notification_sent'));
            }
           
        } catch (\Exception $ex) {
            return redirect()->back()->with('failure',$ex->getMessage());
        }
    }


       /**
     * Method to change status
     * @param array $request post data ,id ,status
    */
    public function changequoteStatus($id,$status)
    {
        Quote::where('id',$id)->update(['status'=>$status]);         
        return back()->with('success',__('message_alerts.status_changed_success'));  
    }


    /**
     * Method to delete 
     * @param array $request post data, id
    */
    public function deletequote($id)
    {
        Quote::where('id', $id)->delete(); 
        QuoteTranslation::where('quote_id',$id)->delete();
        return back()->with('success',__('message_alerts.quote_deleted'));
    }


}


