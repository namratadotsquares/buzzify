<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;


class Quote extends Model
{

    use SoftDeletes;
    protected $table = "quote";
    protected $dates = ['deleted_at'];



    /**
     * Get All 
     * @param Search data
     * @return array 
    */
    public static function getQuotes($search = ''){
        try {
            $contact = new self;
            $pagination_no = 10;
            if(isset($search['per_page']) && !empty($search['per_page'])){
                $pagination_no = $search['per_page'];
            }

            if(isset($search['quote']) && !empty($search['quote'] && $search['quote'] != '')){
                $contact = $contact->where(DB::raw('LOWER(quote)'), 'like', '%'.strtolower($search['quote']). '%');
            }
            $data = $contact->orderBy('id','DESC')->paginate($pagination_no)->appends('per_page', $pagination_no);
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

}
