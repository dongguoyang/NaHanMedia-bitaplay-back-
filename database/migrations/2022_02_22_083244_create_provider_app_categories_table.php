<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderAppCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_app_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_app_id')->comment('服务商应用ID');
            $table->unsignedBigInteger('l1')->comment('一级分类ID');
            $table->unsignedBigInteger('l2')->comment('二级分类ID');
            $table->unsignedBigInteger('l3')->comment('三级分类ID');
            $table->timestamps();

            $table->index('provider_app_id');
            $table->index('l1');
            $table->index('l2');
            $table->index('l3');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_app_categories');
    }
}
