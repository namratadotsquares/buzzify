<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LiveNews;
use App\Models\LiveNewsTranslation;
use App\Models\User;

class LiveNewsAPIController extends Controller
{
    
    private $language;
    public function __construct(Request $request){
        $this->request = $request;
        $newUserId = $this->request->header('userData');
        $user  =  User::find($newUserId);
        if ($user && $user->lang_code != '') {
           $this->language = ($request->header('lang-code') && $request->header('lang-code') != '' ? $request->header('lang-code') : $user->lang_code);
        }else{
            $this->language = ($request->header('lang-code') && $request->header('lang-code') != '' ? $request->header('lang-code') : setting('preferred_site_language'));
        }
    }


    /**
     * Show Live News.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        try {
            $news = LiveNews::getAllActiveNews();
            foreach ($news as $row) {
                $data = LiveNewsTranslation::where('live_news_id',$row->id)->where('language_code',$this->language)->first();
                if ($data) {
                    $row->company_name =  $data->company_name;
                    $row->url =  $data->url;
                }
            }
            return $this->sendResponse($news, __('message_alerts.record_found'));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }
    }
}
