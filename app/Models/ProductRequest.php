<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Models\CategoryTranslation;
class ProductRequest extends Model
{

    protected $table = "product_request";
    protected $fillable = ['user_id','point','product_id','product_code'];
 
    public function Walletproduct(){
        return $this->hasOne('App\Models\Walletproduct',"id","product_id");
      
    }
    public function product(){
        return $this->hasOne('App\Models\Walletproduct',"id","product_id");
      
    }
    public function user(){
        return $this->hasOne('App\Models\User',"id","user_id");
      
    }
    
   
    public static function editProductRequest($data) {
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
   public static function getuserwallet($user_id = ''){
        try {
            $contact = new self;
            $pagination_no = 10;
            
            //$contact = $contact->where('status', 1);
            $contact = $contact->where('user_id', $user_id);
            $data = $contact->with('Walletproduct','user')->paginate($pagination_no)->appends('per_page', $pagination_no)->appends('user_id', $user_id);
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
    public static function getAllproduct($search = ''){
        try {
            $contact = new self;
            $pagination_no = 10;
            if(isset($search['per_page']) && !empty($search['per_page'])){
                $pagination_no = $search['per_page'];
            }
            if(isset($search['company_name']) && !empty($search['company_name'] && $search['company_name'] != '')){
              $contact = $contact->where(DB::raw('LOWER(company_name)'), 'like', '%'.strtolower($search['company_name']). '%');
            }
            $data = $contact->latest()->with('Walletproduct')->paginate($pagination_no)->appends('per_page', $pagination_no);
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
    public static function getlatestproduct($search = ''){
        try {
            $contact = new self;
            $pagination_no = 10;
            if(isset($search['per_page']) && !empty($search['per_page'])){
                $pagination_no = $search['per_page'];
            }

            if (isset($search['paper_name']) && !empty($search['paper_name']) && $search['paper_name'] != '') {
                $contact = $contact->whereHas('user', function($query) use ($search) {
                    $query->where(DB::raw('LOWER(name)'), 'like', '%' . strtolower($search['paper_name']) . '%');
                });
            }
            
            $data = $contact->with('Walletproduct', 'user')->orderBy('id', 'desc')->paginate($pagination_no)->appends(['per_page' => $pagination_no]);
            
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
     public static function getuserrequest($user_id = ''){
        try {
            $contact = new self;
            $pagination_no = 10;
            
            $contact = $contact->where('user_id', $user_id);
            $data = $contact->with('product')->orderBy('id', 'desc')->paginate($pagination_no)->appends('per_page', $pagination_no)->appends('user_id', $user_id);
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

  
}