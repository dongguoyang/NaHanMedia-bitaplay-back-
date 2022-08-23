<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderAppChainRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_app_chain_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_app_id')->comment('服务商应用ID');
            $table->char('number', 18)->comment('订单号');
            $table->char('content', 32)->comment('上链内容');
            $table->char('hash', 64)->comment('上链HASH');
            $table->unsignedBigInteger('fuel')->default(0)->comment('燃料');
            $table->timestamps();

            $table->index('provider_app_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_app_chain_records');
    }
}
