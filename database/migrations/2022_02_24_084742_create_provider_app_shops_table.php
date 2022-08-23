<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderAppShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_app_shops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_app_id')->comment('服务商应用ID');
            $table->unsignedBigInteger('android_shop_id')->comment('适用人群ID');
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
        Schema::dropIfExists('provider_app_shops');
    }
}
