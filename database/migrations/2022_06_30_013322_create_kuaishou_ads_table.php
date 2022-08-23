<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKuaishouAdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kuaishou_ads', function (Blueprint $table) {
            $table->id();
            $table->string('account_id')->default('')->comment('广告账户ID');
            $table->string('aid')->default('')->comment('广告组ID');
            $table->string('cid')->default('')->comment('广告创意ID');
            $table->string('did')->default('')->comment('广告计划ID');
            $table->string('ts')->default('')->comment('时间');
            $table->string('ip')->default('')->comment('IP地址');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kuaishou_ads');
    }
}
