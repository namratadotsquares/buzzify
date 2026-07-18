<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSixVisibilityOptionsToBlogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blog', function (Blueprint $table) {
            $table->tinyInteger('visibility_opt_1')->default(0)->after('is_weekly_top_picks');
            $table->tinyInteger('visibility_opt_2')->default(0)->after('visibility_opt_1');
            $table->tinyInteger('visibility_opt_3')->default(0)->after('visibility_opt_2');
            $table->tinyInteger('visibility_opt_4')->default(0)->after('visibility_opt_3');
            $table->tinyInteger('visibility_opt_5')->default(0)->after('visibility_opt_4');
            $table->tinyInteger('visibility_opt_6')->default(0)->after('visibility_opt_5');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blog', function (Blueprint $table) {
            $table->dropColumn([
                'visibility_opt_1',
                'visibility_opt_2',
                'visibility_opt_3',
                'visibility_opt_4',
                'visibility_opt_5',
                'visibility_opt_6',
            ]);
        });
    }
}
