<?php

namespace App\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class UserFeed extends Model
{
    protected $table = "user_feed";
    // use SoftDeletes;

  protected $fillable = ['category_id','user_id'];
  public $timestamps = false;

      public function categoryData(){

         return $this->hasOne(Category::class,"id","category_id");
    }





    /**
     * Upadte RSS Feed
     * @param Array of post data
     * @return template_id
    */
    public static function updateFeed($data) {
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
    public static function getAllFeed($search = ''){
        try {
            $contact = new self;
            $pagination_no = 10;
            if(isset($search['per_page']) && !empty($search['per_page'])){
                $pagination_no = $search['per_page'];
            }

            if(isset($search['name']) && !empty($search['name'] && $search['name'] != '')){
              $contact = $contact->where(DB::raw('LOWER(rss_name)'), 'like', '%'.strtolower($search['name']). '%');
            }

            $data = $contact->orderBy('id','DESC')->with('categoryData')
                    ->paginate($pagination_no)->appends('per_page', $pagination_no);
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
    public static function getAllforDashFeed($search = ''){
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
    public static function getAllActiveFeed(){
        try {
            $category = new self;
            $data = $category->where('status',1)->orderBy('name','ASC')->get();
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
