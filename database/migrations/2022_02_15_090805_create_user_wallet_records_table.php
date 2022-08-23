<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserWalletRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_wallet_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->unsignedBigInteger('order_id')->comment('订单ID');
            $table->char('number', 18)->comment('订单号');
            $table->unsignedTinyInteger('payment_method')->default(1)->comment('1-微信，2-支付宝，3-余额');
            $table->bigInteger('amount')->comment('变化金额');
            $table->unsignedTinyInteger('type')->comment('类型');
            $table->timestamps();

            $table->index('user_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_wallet_records');
    }
}
