<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Models\CategoryTranslation;
class Wallet extends Model
{

    protected $table = "wallet";
    protected $fillable = ['user_id','point','story_id','range_date'];
    use SoftDeletes;
    public function story(){
        return $this->hasOne('App\Models\Story',"id","story_id");
      
    }
    protected $dates = ['deleted_at'];
    public static function addEpaper($data) {
        try {
            $category = new self;
            $id=0;
           return  $category->insertGetId($data);
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
   public static function getuserwallet($user_id = ''){
        try {
            $contact = new self;
            $pagination_no = 10;
            
            //$contact = $contact->where('status', 1);
            $contact = $contact->where('user_id', $user_id);
            $data = $contact->with('story')->orderBy('id', 'desc')->paginate($pagination_no)->appends('per_page', $pagination_no)->appends('user_id', $user_id);
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
  
}
