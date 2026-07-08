<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogVisibilityOption extends Model
{
    protected $table = 'blog_visibility_options';

    protected $fillable = [
        'label',
        'field_key',
        'color_class',
        'is_active',
        'sort_order',
    ];

    /**
     * The 4 built-in DB columns that visibility options can map to.
     * Admin cannot use field_keys outside this list.
     */
    public static $allowedFieldKeys = [
        'is_featured'        => 'Featured (App)',
        'is_slider'          => 'Add to slider',
        'is_editor_picks'    => 'Editing',
        'is_weekly_top_picks'=> 'Final',
    ];

    /**
     * Get all active visibility options ordered by sort_order.
     */
    public static function getActive()
    {
        return static::where('is_active', 1)->orderBy('sort_order')->get();
    }

    /**
     * Get all options (active + inactive) for admin management.
     */
    public static function getAll()
    {
        return static::orderBy('sort_order')->get();
    }
}
