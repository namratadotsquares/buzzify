<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Models\CategoryTranslation;
class Ads_images extends Model
{

    protected $table = "ads_images";
    
    // protected $fillable = ['ad_id','img_order','original_name','stored_name','location','extension','size','redirectUrl','status'];

    public static function getAllAdsImages($search = '',$id=0){
        try {
            $contact = new self;
            $pagination_no = 10;
            if(isset($search['per_page']) && !empty($search['per_page'])){
                $pagination_no = $search['per_page'];
            }

            $data = $contact->where('ad_id',$id)->orderBy('position','ASC')
                    ->paginate($pagination_no)->appends('per_page', $pagination_no);
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

}
