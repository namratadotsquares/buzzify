<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsContentTranslationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cms_content_translation', function (Blueprint $table) {
            $table->id();
            $table->integer('cms_id');
            $table->string('language_code');
            $table->string('title')->nullable();
            $table->string('page_title')->nullable();
            $table->string('description')->nullable();
            $table->string('meta_char')->nullable();
            $table->string('meta_desc')->nullable();
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
        Schema::dropIfExists('cms_content_translation');
    }
}
