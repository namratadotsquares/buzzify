<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class BlogCategory extends Model
{
    protected $table = "blog_category";
    use SoftDeletes;
    protected $dates = ['deleted_at'];
     protected $fillable = [
        'blog_id',
        'category_id',
     ];
  
  	public function category(){
        return $this->hasOne('App\Models\Category',"id","category_id");
      //->where('status',1)
    }
}
