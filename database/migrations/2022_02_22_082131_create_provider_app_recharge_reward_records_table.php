<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderAppRechargeRewardRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_app_recharge_reward_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id')->comment('服务商ID');
            $table->unsignedBigInteger('provider_app_id')->comment('服务商应用ID');
            $table->char('number', 18)->comment('订单号');
            $table->unsignedBigInteger('amount')->comment('充值金额');
            $table->timestamps();

            $table->index(['provider_id', 'provider_app_id'],'reward');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_app_recharge_reward_records');
    }
}
