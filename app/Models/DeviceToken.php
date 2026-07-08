<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class DeviceToken extends Model{    
    protected $table = "device_tokens";
    use SoftDeletes;
    protected $fillable = [ 'device_token'];
    protected $dates = ['deleted_at'];
}
