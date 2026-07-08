<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogTranslationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blog_translation', function (Blueprint $table) {
            $table->id();
            $table->integer('blog_id');
            $table->string('language_code');
            $table->string('title')->nullable();;
            $table->text('tags')->nullable();;
            $table->text('description')->nullable();;
            $table->text('seo_title')->nullable();;
            $table->text('seo_keyword')->nullable();;
            $table->text('seo_tag')->nullable();;
            $table->text('seo_description')->nullable();;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blog_translation');
    }
}
