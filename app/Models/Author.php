<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class Author extends Model
{

    protected $table = "author";
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    
    /**
     * Add Contact
     * @param Array of post data
     * @return category_id 
    */
    public static function addAuthor($data) {
        try {
            $category = new self;
            $id=0;
            if($id = $category->insertGetId($data)) {
                return ['status' => true, 'message' => "added sucessfully", 'id' =>$id];
            } else {
                return ['status' => false, 'message' => "Error in adding contact" ];
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
    public static function updateAuthor($data) {
        try {
            $template = new self;
            $id=0;              
            if($id = $template->where('id', $data['id'])->update($data)) {
                return ['status' => true, 'message' => "updated sucessfully", 'id' =>$id];
            } else {
                return ['status' => false, 'message' => "Error in updating category" ];
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
    public static function getAllAuthors($search = ''){
        try {
            $contact = new self;
            $pagination_no = 10;
            if(isset($search['per_page']) && !empty($search['per_page'])){
                $pagination_no = $search['per_page'];
            }


            if(isset($search['name']) && $search['name'] != ''){
              $keyword = $search['name'];
              $contact = $contact->where(function($q) use ($keyword){
                    $q->where(DB::raw('LOWER(name)'), 'like', '%'.strtolower($keyword). '%')
                    ->orWhere(DB::raw('email'), 'like', '%'.strtolower($keyword). '%')
                    ->orWhere(DB::raw('designation'), 'like', '%'.strtolower($keyword). '%');
                });
            }

            $data = $contact->orderBy('id','DESC')
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
    public static function getAllAuthorsForDash($search = ''){
        try {
            $contact = new self;
            if(isset($search['name']) && $search['name'] != ''){
              $keyword = $search['name'];
              $contact = $contact->where(function($q) use ($keyword){
                    $q->where(DB::raw('LOWER(name)'), 'like', '%'.strtolower($keyword). '%')
                    ->orWhere(DB::raw('email'), 'like', '%'.strtolower($keyword). '%')
                    ->orWhere(DB::raw('designation'), 'like', '%'.strtolower($keyword). '%');
                });
            }
            $data = $contact->orderBy('id','DESC')
                    ->get();            
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
    public static function getAllActiveAuthors(){
        try {
            $category = new self;
            $data = $category->where('status',1)->orderBy('name','ASC')->get();
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
