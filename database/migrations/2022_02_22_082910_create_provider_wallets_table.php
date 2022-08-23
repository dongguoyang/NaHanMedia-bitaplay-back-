<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_wallets', function (Blueprint $table) {
            $table->unsignedBigInteger('provider_id')->comment('服务商ID');
            $table->unsignedBigInteger('balance')->default(0)->comment('余额');
            $table->unsignedBigInteger('frozen_balance')->default(0)->comment('冻结余额');
            $table->timestamps();

            $table->primary('provider_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_wallets');
    }
}
