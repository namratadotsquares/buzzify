<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ads;
use App\Models\Ads_action;
use Illuminate\Database\Eloquent\Model;
use App\Models\NewsAds;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdsController extends Controller
{

    public function action(Request $request)
    {
        $validate = [
            'userID' => 'required|integer|exists:users,id',
            'AdsID' => 'required|integer|exists:ads,id',
            'action' => 'required|boolean',//0=>for view,1=> for click
        ];
        $validatemessage = [
            'userID.required' => 'user ID required',
            'userID.integer' => 'user ID should be integer',
            'userID.exists' => 'Invalid User',
            'AdsID.exists' => 'AdsID Invalid',
            'AdsID.required' => 'user ID required',
            'action.required' => 'action  required',
            'action.boolean' => 'action should be boolean value',
            'AdsID.integer' => 'AdsID should be integer',
        ];
        $validator = Validator::make($request->all(), $validate, $validatemessage);
        if ($validator->fails()) {
            $data['error'] = $validator->errors();

            return response(\Helpers::sendFailureAjaxResponse($data['error']));
        } else {
            if ($request->action == 0){
               $data =  Ads_action::where(['userID' => $request->userID, 'AdsID'=> $request->AdsID, 'action' => $request->action])->first();
               if ($data){
                   return $this->sendError(__('Already Viewed'), 401);
               }else{
                   $Ads_action = new Ads_action();
                   $Ads_action->userID = $request->userID;
                   $Ads_action->AdsID = $request->AdsID;
                   $Ads_action->action = $request->action;
                   $Ads_action->save();

               }
            }else{
                $Ads_action = new Ads_action();
                $Ads_action->userID = $request->userID;
                $Ads_action->AdsID = $request->AdsID;
                $Ads_action->action = $request->action;
                $Ads_action->save();
            }



if($Ads_action){
$Ads = Ads::where('id',$request->AdsID)->first();
if ($request->action == 0){
    $Ads->view = $Ads->view+1;
}else{
    $Ads->click = $Ads->click+1;
}
    $Ads->save();
    return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.success'), $Ads));
}else{
    return $this->sendError(__('Error'), 401);
}

        }
    }
    
    
    
    // public function newsads(Request $request)
    // {
    //     $ads = NewsAds::get();
    //   return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'), $ads));
    // }
    public function newsads(Request $request)
{
    $ads = NewsAds::where('status',1)->get();
    $adsWithImageUrl = $ads->map(function ($ad) {
        $imageUrl = asset('storage/newsads/' . $ad->image);
        $ad->image = $imageUrl;
        return $ad;
    });
    return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'), $adsWithImageUrl));
}
  public function fullscreenads(Request $request)
{
     $ads_list = Ads::where('status',1)->where('end_date',">=",date('Y-m-d'))->withCount('click')->withCount('view')->with('media')->orderBy('order','ASC')->get();
            if(count($ads_list)){
                foreach($ads_list as $ads_list_data){
                    foreach($ads_list_data->media  as $media_list){
                        if($media_list->type=='image'){
                            $media_list->file = url('storage/ads/' .$media_list->file);
                        }else if($media_list->type=='video'){
                            $media_list->file = url('storage/ads/videos/' .$media_list->file);
                        }
                    }
                }
            }
    return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'), $ads_list));
}
}
