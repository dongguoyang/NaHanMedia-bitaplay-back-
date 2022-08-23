<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderWithdrawAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_withdraw_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id')->comment('服务商ID');
            $table->string('bank_name', 80)->default('')->comment('银行名称');
            $table->string('bank_account_name', 20)->default('')->comment('银行账户名');
            $table->string('bank_number', 30)->default('')->comment('银行卡号');
            $table->string('alipay_account_name')->default('')->comment('支付宝账户名');
            $table->string('alipay_number')->default('')->comment('支付宝账户');
            $table->timestamps();

            $table->index('provider_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_withdraw_accounts');
    }
}
