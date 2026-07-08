<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Translations extends Model
{
    protected $table = "translations";


        /**
     * Get All 
     * @param Search data
     * @return array 
    */
    public static function getTanslations($search = ''){
        try {
            $contact = new self;
            //$pagination_no = 10;
            if (isset($search['search']) && $search['search'] !='') {
            	// if(isset($search['per_page']) && !empty($search['per_page'])){
	            //     $pagination_no = $search['per_page'];
	            // }
	            if(isset($search['key']) && !empty($search['key'] && $search['key'] != '')){
	                $keyword = $search['key'];
	                $contact = $contact->where(function($q) use ($keyword){
                        $q->where(DB::raw('LOWER(keyword)'), 'like', '%'.strtolower($keyword). '%')
                        ->orWhere(DB::raw('value'), 'like', '%'.strtolower($keyword). '%');
                    });
	            }
	            if(isset($search['language_id']) && !empty($search['language_id'] && $search['language_id'] != '')){
	                $contact = $contact->where('language_id',$search['language_id']);
	            }
	            if(isset($search['group']) && !empty($search['group'] && $search['group'] != '')){
	                $contact = $contact->where('group',$search['group']);
	            }
                $perPage = (isset($_GET['per_page']))?$_GET['per_page']:config('constant.paginate.num_per_page');
                $data = $contact->paginate($perPage);
	            // $data = $contact->paginate($pagination_no)->appends('per_page', $pagination_no);
            } else {
                $perPage = (isset($_GET['per_page']))?$_GET['per_page']:config('constant.paginate.num_per_page');
                $data = $contact->groupBy('keyword')->whereNotIn('group',array('frontend','front_end'))->paginate($perPage);
            	// $data = $contact->groupBy('keyword')->paginate($pagination_no)->appends('per_page', $pagination_no);
            }
            
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
