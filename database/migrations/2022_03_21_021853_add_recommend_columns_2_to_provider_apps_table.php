<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecommendColumns2ToProviderAppsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provider_apps', function (Blueprint $table) {
            $table->string('recommend_sex')->default(0)->comment('推荐性别');
            $table->longText('recommend_age')->comment('推荐年龄');
            $table->longText('recommend_preference')->comment('偏好');
            $table->longText('recommend_style')->comment('风格');
            $table->longText('recommend_educational')->comment('受教育程度');
            $table->longText('recommend_device')->comment('手机品牌');
            $table->unsignedTinyInteger('recommend_system')->default(0)->comment('手机系统:0-全部，1-Android，2-IOS');
            $table->unsignedTinyInteger('recommend_real')->default(0)->comment('0-全部，1-已实名');
            $table->unsignedTinyInteger('recommend_week_download')->default(0)->comment('0-全部，1-开启最近7天');
            $table->unsignedTinyInteger('recommend_month_download')->default(0)->comment('0-全部，1-开启最近1月');
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
            $table->dropColumn('recommend_sex');
            $table->dropColumn('recommend_age');
            $table->dropColumn('recommend_preference');
            $table->dropColumn('recommend_style');
            $table->dropColumn('recommend_educational');
            $table->dropColumn('recommend_device');
            $table->dropColumn('recommend_system');
            $table->dropColumn('recommend_real');
            $table->dropColumn('recommend_week_download');
            $table->dropColumn('recommend_month_download');
        });
    }
}
