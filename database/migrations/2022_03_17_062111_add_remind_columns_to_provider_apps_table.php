<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemindColumnsToProviderAppsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provider_apps', function (Blueprint $table) {
            $table->unsignedBigInteger('remind_third_login')->default(0)->comment('三方登录不足提醒');
            $table->unsignedBigInteger('remind_reward')->default(0)->comment('奖励金额不足提醒');
            $table->unsignedBigInteger('remind_fuel')->default(0)->comment('燃料不足提醒');
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
            $table->dropColumn('remind_third_login');
            $table->dropColumn('remind_reward');
            $table->dropColumn('remind_fuel');
        });
    }
}
