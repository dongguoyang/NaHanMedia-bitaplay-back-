<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserWithdrawAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_withdraw_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->string('bank_name', 80)->default('')->comment('银行名称');
            $table->string('bank_account_name', 20)->default('')->comment('银行账户名');
            $table->string('bank_number', 30)->default('')->comment('银行卡号');
            $table->string('alipay_account_name', 20)->default('')->comment('支付宝账户名');
            $table->string('alipay_number', 50)->default('')->comment('支付宝账户');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_withdraw_accounts');
    }
}
