<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class BlogImages extends Model
{
    protected $table = "blog_images";
    use SoftDeletes;
    protected $dates = ['deleted_at'];
}
