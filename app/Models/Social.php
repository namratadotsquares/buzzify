<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Social extends Model
{

    protected $table = "social";
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    
    /**
     * Add Contact
     * @param Array of post data
     * @return category_id 
    */
    public static function addSocial($data) {
        try {
            $category = new self;
            $id=0;
            if($id = $category->insertGetId($data)) {
                return ['status' => true, 'message' => "Social added sucessfully", 'id' =>$id];
            } else {
                return ['status' => false, 'message' => "Error in adding social" ];
            }
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Upadte Category
     * @param Array of post data
     * @return template_id
    */
    public static function updateSocial($data) {
        try {
            $template = new self;
            $id=0;              
            if($id = $template->where('id', $data['id'])->update($data)) {
                return ['status' => true, 'message' => "Social updated sucessfully", 'id' =>$id];
            } else {
                return ['status' => false, 'message' => "Error in updating social" ];
            }
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Get All category
     * @param Search data
     * @return array 
    */
    public static function getAllSocial($search = ''){
        try {
            $contact = new self;
            $pagination_no = 10;
            if(isset($search['per_page']) && !empty($search['per_page'])){
                $pagination_no = $search['per_page'];
            }

            if(isset($search['name']) && !empty($search['name'] && $search['name'] != '')){
              $contact = $contact->where(DB::raw('LOWER(name)'), 'like', '%'.strtolower($search['name']). '%');
            }
            $data = $contact
                    ->paginate($pagination_no)->appends('per_page', $pagination_no);
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Get All category
     * @param Search data
     * @return array 
    */
    public static function getAllActiveSocial(){
        try {
            $category = new self;
            $data = $category->where('status',1)->orderBy('name','ASC')->get();
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
