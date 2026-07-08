<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Models\CategoryTranslation;
class Walletproduct extends Model
{

    protected $table = "wallet_products";
    protected $fillable = ['name','img','redeem','point','status'];
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    
    
    public static function getAllproduct($search = ''){
        try {
            $contact = new self;
            $pagination_no = 10;
            if(isset($search['per_page']) && !empty($search['per_page'])){
                $pagination_no = $search['per_page'];
            }
            // Start Search by name- abha
            if(isset($search['name']) && !empty($search['name'] && $search['name'] != '')){
                $contact = $contact->where(DB::raw('LOWER(name)'), 'like', '%'.strtolower($search['name']). '%');
            }
             // End Search by name- abha
            if(isset($search['company_name']) && !empty($search['company_name'] && $search['company_name'] != '')){
              $contact = $contact->where(DB::raw('LOWER(company_name)'), 'like', '%'.strtolower($search['company_name']). '%');
            }
            $data = $contact->latest()->paginate($pagination_no)->appends('per_page', $pagination_no);
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

        public static function addEpaper($data) {
        try {
            $category = new self;
            $id=0;
            if($id = $category->insertGetId($data)) {
                return ['status' => true, 'message' => config('constant.messages.record_inserted'), 'id' =>$id];
            } else {
                return ['status' => false, 'message' => config('constant.messages.something_went_wrong') ];
            }
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
    public static function updateEpaper($data) {
        try {
            $template = new self;
            $id=0;              
            if($id = $template->where('id', $data['id'])->update($data)) {
                return ['status' => true, 'message' => config('constant.messages.record_updated'), 'id' =>$id];
            } else {
                return ['status' => false, 'message' => config('constant.messages.something_went_wrong') ];
            }
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
    public static function getAllActiveEpaper($search = ''){
        try {
            $contact = new self;
            $pagination_no = 10;
            if(isset($search['per_page']) && !empty($search['per_page'])){
                $pagination_no = $search['per_page'];
            }
            $contact = $contact->where('status', 1);
            $data = $contact->paginate($pagination_no)->appends('per_page', $pagination_no);
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
