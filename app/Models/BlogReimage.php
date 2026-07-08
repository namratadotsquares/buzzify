<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class BlogReimage extends Model
{
    protected $table = "blog_reimages";
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $primaryKey = "id";
    protected $fillable = [
        'blog_id',
        'image',
    ];
}
