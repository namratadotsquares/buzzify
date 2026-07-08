<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogVisibilityOptionsTable extends Migration
{
    /**
     * Run the migrations.
     * Stores dynamic blog visibility option definitions managed by admin.
     * Each row maps a display label to one of the 4 blog DB columns.
     */
    public function up()
    {
        Schema::create('blog_visibility_options', function (Blueprint $table) {
            $table->increments('id');
            $table->string('label');                           // Display label e.g. "Featured (App)"
            $table->string('field_key');                       // DB column: is_featured | is_slider | is_editor_picks | is_weekly_top_picks | custom
            $table->string('color_class')->default('bg-theme-1'); // Badge color class for list display
            $table->tinyInteger('is_active')->default(1);     // 1=active (shown in forms), 0=hidden
            $table->integer('sort_order')->default(0);        // Display order
            $table->timestamps();
        });

        // Seed default 4 options matching existing hardcoded fields
        DB::table('blog_visibility_options')->insert([
            ['label' => 'Featured (App)',    'field_key' => 'is_featured',        'color_class' => 'bg-theme-1',  'is_active' => 1, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['label' => 'Add to slider',     'field_key' => 'is_slider',          'color_class' => 'bg-theme-9',  'is_active' => 1, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['label' => 'Editing',           'field_key' => 'is_editor_picks',    'color_class' => 'bg-theme-12', 'is_active' => 1, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['label' => 'Final',             'field_key' => 'is_weekly_top_picks','color_class' => 'bg-theme-6',  'is_active' => 1, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('blog_visibility_options');
    }
}
