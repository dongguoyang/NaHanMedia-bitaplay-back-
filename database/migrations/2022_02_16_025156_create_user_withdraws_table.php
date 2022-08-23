<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserWithdrawsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_withdraws', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->char('number', 18)->comment('订单号');
            $table->unsignedBigInteger('amount')->comment('提现金额');
            $table->string('name', 40)->comment('真实姓名');
            $table->string('bank_name', 80)->default('')->comment('银行名称');
            $table->string('bank_number', 30)->default('')->comment('银行卡号');
            $table->string('alipay_number')->default('')->comment('支付宝账号');
            $table->unsignedTinyInteger('payment_method')->default(1)->comment('1-支付宝，2-银行卡');
            $table->unsignedTinyInteger('status')->default(1)->comment('1-待处理，2-取消，3-已处理，4-已拒绝');
            $table->string('evidence')->default('')->comment('打款凭证');
            $table->string('refuse_reason')->default('')->comment('拒绝理由');
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->unique('number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_withdraws');
    }
}
