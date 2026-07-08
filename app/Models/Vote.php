<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Vote extends Model
{
    protected $table = "vote";
    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->hasOne(User::class,'id','user_id');
    }
    /**
     * Add Blog
     * @param Array of post data
     * @return category_id 
    */
    public static function addVote($data) {
        try {
            $vote = new self;
            $id=0;
            if($id = $vote->insertGetId($data)) {
                return ['status' => true, 'message' => "Vote added sucessfully", 'id' =>$id];
            } else {
                return ['status' => false, 'message' => "Error in adding vote" ];
            }
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Get Search log
     * @param Search data
     * @return array 
    */
    public static function getAllVote($search = ''){
        try {
            $contact = new self;
            $pagination_no = 10;
            if(isset($search['per_page']) && !empty($search['per_page'])){
                $pagination_no = $search['per_page'];
            }
            $data = $contact->orderBy('id','DESC')
                    ->paginate($pagination_no)->appends('per_page', $pagination_no);
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
