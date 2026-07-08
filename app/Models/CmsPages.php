<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\CmsPagesTranslation;
class CmsPages extends Model
{
    protected $table = "cms_content";


    /**
     * Get All Pages
     * @param Search data
     * @return array 
    */
    public static function getCmsPages(){
        try {
            $contact = new self;
            $data = $contact->get();
            $language = \Helpers::returnUserLangCode();
            foreach($data as $likes){
                $page = CmsPagesTranslation::where('cms_id',$likes->id)->where('language_code',$language)->first();
                if ($page) {
                    $likes->title = $page->title;
                    $likes->page_title = $page->page_title;
                    $likes->description = $page->description;
                    $likes->meta_char = $page->meta_char;
                    $likes->meta_desc = $page->meta_desc;
                }
            }
            return $data;               
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }


}
