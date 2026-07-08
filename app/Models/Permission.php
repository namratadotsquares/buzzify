<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Permission extends Model
{
    protected $table = "permissions";
    use SoftDeletes;
    
    protected $dates = ['deleted_at'];
}
