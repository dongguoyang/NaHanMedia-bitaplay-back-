<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDownloadRewardStatusToProviderAppsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provider_apps', function (Blueprint $table) {
            $table->unsignedTinyInteger('download_reward_status')->default(1)->comment('0-关闭，1-开启');
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
            $table->dropColumn('download_reward_status');
        });
    }
}
