<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderRechargeBalanceRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_recharge_balance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id')->comment('服务商ID');
            $table->char('number', 18)->comment('订单号');
            $table->unsignedBigInteger('amount')->comment('充值金额');
            $table->unsignedTinyInteger('payment_method')->default(1)->comment('1-微信，2-支付宝');
            $table->string('payment_number')->default('')->comment('三方订单号');
            $table->unsignedTinyInteger('status')->default(1)->comment('1-待支付，2-成功');
            $table->timestamps();

            $table->index('provider_id');
            $table->unique('number');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_recharge_balance_records');
    }
}
