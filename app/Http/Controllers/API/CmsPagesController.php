<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CmsPages;
use App\Models\Ads;
use App\Models\CmsPagesTranslation;
use App\Models\SiteContent;
use Carbon\Carbon;

class CmsPagesController extends Controller
{
    public function index($language_code = null){
        $response['ads'] = Ads::where('status',1)->where('start_date',"<=",Carbon::now())->where('end_date',">=",Carbon::now())->withCount('click')->withCount('view')->with('section')->with('media')->orderBy('order','ASC')->get();
        if(count($response['ads'])){
            foreach($response['ads'] as $ads_list_data){
                foreach($ads_list_data->media  as $media_list){
                    $media_list->file = url('storage/ads/' .$media_list->file);
                }
            }
        }
        $response['frequency'] = 0;
        $content = SiteContent::where('key','ads_frequency')->first();
        if($content){
            $response['frequency'] = $content->value;
        }
        
        if($language_code){
            $response['list'] = CmsPagesTranslation::where(['language_code'=> $language_code])->get();
            foreach($response['list'] as $row){
                $getCms = CmsPages::where('id',$row->cms_id)->first();
                if($getCms){
                    $row->image = $getCms->image;
                }
            }
            return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.registerd_successfully'),$response));
         }else{
             $response['list'] = CmsPages::select('id','title','page_title','image','description')->get();
            return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.registerd_successfully'),$response));
         }
        
    }
}
