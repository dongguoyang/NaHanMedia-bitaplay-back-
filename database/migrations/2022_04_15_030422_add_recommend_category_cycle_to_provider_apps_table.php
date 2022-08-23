<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecommendCategoryCycleToProviderAppsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provider_apps', function (Blueprint $table) {
            $table->unsignedTinyInteger('recommend_category_cycle')->default(0)->comment('0-全部，1-7天，2-一月');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provider_apps', function (Blueprint $table) {
            $table->dropColumn('recommend_category_cycle');
        });
    }
}
