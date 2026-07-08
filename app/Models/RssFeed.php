<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class RssFeed extends Model
{
    protected $table = "rss_feed";
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function categoryData(){
        return $this->hasOne('App\Models\Category',"id","category_id")->where('status',1);
    }
    
    /**
     * Add RSS feed
     * @param Array of post data
     * @return category_id 
    */
    public static function addRssFeed($data) {
        try {
            $category = new self;
            $id=0;
            if($id = $category->insertGetId($data)) {
                return ['status' => true, 'message' => "RSS feed added sucessfully", 'id' =>$id];
            } else {
                return ['status' => false, 'message' => "Error in adding rss feed" ];
            }
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Upadte RSS Feed
     * @param Array of post data
     * @return template_id
    */
    public static function updateRssFeed($data) {
        try {
            $template = new self;
            $id=0;              
            if($id = $template->where('id', $data['id'])->update($data)) {
                return ['status' => true, 'message' => "RSS feed updated sucessfully", 'id' =>$id];
            } else {
                return ['status' => false, 'message' => "Error in updating rss feed" ];
            }
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Get All RSS feed
     * @param Search data
     * @return array 
    */
    public static function getAllRssFeed($search = ''){
        try {
            $contact = new self;

            if (isset($search['name']) && !empty($search['name']) && $search['name'] != '') {
              $contact = $contact->where(DB::raw('LOWER(rss_name)'), 'like', '%' . strtolower($search['name']) . '%')
                                   ->orWhere(DB::raw('LOWER(rss_url)'), 'like', '%' . strtolower($search['name']) . '%');
            }
            $perPage = (isset($_GET['per_page']))?$_GET['per_page']:config('constant.paginate.num_per_page');
            $data = $contact->orderBy('id','DESC')->with('categoryData')->paginate($perPage);
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
    /**
     * Get All RSS feed
     * @param Search data
     * @return array 
    */
    public static function getAllforDashRssFeed($search = ''){
        try {
            $contact = new self;
            if(isset($search['name']) && !empty($search['name'] && $search['name'] != '')){
              $contact = $contact->where(DB::raw('LOWER(name)'), 'like', '%'.strtolower($search['name']). '%');
            }
            $data = $contact->orderBy('id','DESC')->get();
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Get All RSS feed
     * @param Search data
     * @return array 
    */
    public static function getAllActiveRssFeed(){
        try {
            $category = new self;
            $data = $category->where('status',1)->orderBy('name','ASC')->get();
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
