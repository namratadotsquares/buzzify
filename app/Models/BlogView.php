<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BlogView extends Model
{
	protected $table = "view_blog";
	
	protected $fillable = ['user_id', 'blog_id'];
	
	 public function users()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }


}