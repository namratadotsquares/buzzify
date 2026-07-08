<?php



namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;



class StoreViewed extends Model
{



    protected $table = "store_viewed";

    protected $fillable = ['user_id', 'blog_id', 'device_id'];
}