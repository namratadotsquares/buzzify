<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Auth;


class BookMarkPost extends Model
{
    protected $table = "bookmark_post";
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function myblog(){
        return $this->hasOne('App\Models\Blog',"id","blog_id")->with('category');
    }

    public static function getSavedStories(){
        $stories = new self;
        $saved_stories = $stories->where('user_id',Auth::user()->id)->get();
        foreach($saved_stories as $rows){
            $rows->blog = Blog::getSingleBlog($rows->blog_id);
        } 
        return $saved_stories;
    }
}
