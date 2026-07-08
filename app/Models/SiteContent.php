<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteContent extends Model
{
    protected $table = "app_settings";

    protected $dates = ['deleted_at'];
    
    /**
     * Add Cms
     * @param Array of post data
     * @return category_id 
    */
    public static function addCms($data) {
        try {
            $category = new self;
            $id=0;
            if($id = $category->insertGetId($data)) {
                return ['status' => true, 'message' => "added sucessfully", 'id' =>$id];
            } else {
                return ['status' => false, 'message' => "Error in adding" ];
            }
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Upadte Cms
     * @param Array of post data
     * @return template_id
    */
    public static function updateCms($data) {
        try {
            $template = new self;
            $id=0;              
            if($id = $template->where('id', $data['id'])->update($data)) {
                return ['status' => true, 'message' => "updated sucessfully", 'id' =>$id];
            } else {
                return ['status' => false, 'message' => "Error in updating" ];
            }
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
    
}
