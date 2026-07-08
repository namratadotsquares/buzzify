<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ads_action extends Model
{
    protected $table = 'ads_action';
    protected $fillable = ['userID','AdsID','action'];
    public function users()
    {
        return $this->hasOne(User::class,'id','userID');
    }
}
