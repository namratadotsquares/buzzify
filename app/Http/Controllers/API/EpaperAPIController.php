<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Epaper;
use App\Models\Walletproduct;
use App\Models\EpaperTranslation;
use App\Models\User;
class EpaperAPIController extends Controller
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
     * Show E-Paper News.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        try {
            $paper = Epaper::getAllActiveEpaper();
            foreach ($paper as $row) {
                $data = EpaperTranslation::where('e_paper_id',$row->id)->where('language_code',$this->language)->first();
                if ($data) {
                    $row->paper_name =  $data->paper_name;
                    $row->pdf =  $data->pdf;
                }
            }
            return $this->sendResponse($paper, __('message_alerts.record_found'));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }
    }
    public function productlist(Request $request)
    {
        try {
             $paper = WalletProduct::getAllActiveEpaper();
            foreach($paper as $key=>$row){
                if($row->img!=''){
                    $row->img= url('upload/e-paper/original/'.$row->img);
                }else{
                    $row->img= url('upload/category/default.png');
                }
            }
            
            return $this->sendResponse($paper, __('message_alerts.record_found'));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }
    }
}
