<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
class Epaper extends Model
{
    protected $table = "e_paper";
    use SoftDeletes;
    protected $dates = ['deleted_at'];


      /**
     * Add 
     * @param Array of post data
     * @return category_id 
    */
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

    /**
     * Upadte 
     * @param Array of post data
     * @return template_id
    */
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

    /**
     * Get All 
     * @param Search data
     * @return array 
    */
    public static function getAllEpaper($search = ''){
        try {
            $contact = new self;
            $pagination_no = 10;
            if(isset($search['per_page']) && !empty($search['per_page'])){
                $pagination_no = $search['per_page'];
            }
            if(isset($search['company_name']) && !empty($search['company_name'] && $search['company_name'] != '')){
              $contact = $contact->where(DB::raw('LOWER(company_name)'), 'like', '%'.strtolower($search['company_name']). '%');
            }
            $data = $contact->paginate($pagination_no)->appends('per_page', $pagination_no);
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Get All 
     * @param Search data
     * @return array 
    */
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
