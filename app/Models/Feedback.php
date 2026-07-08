<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Models\CategoryTranslation;
class Feedback extends Model
{

    protected $table = "feedbacks";
    protected $fillable = ['name','phone','email','feed_back'];
    use SoftDeletes;

    protected $dates = ['deleted_at'];

  
  
  

}
