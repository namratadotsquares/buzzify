<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoryViewCount extends Model
{
    use SoftDeletes;

    protected $table = 'story_view_counts';

    protected $fillable = [
        'story_id',
        'user_id',
        'device_id',
        'action'
    ];

    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
