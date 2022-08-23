<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderAppRecommendCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_app_recommend_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_app_id')->comment('服务商APP ID');
            $table->unsignedBigInteger('l1')->default(0)->comment('一级分类');
            $table->unsignedBigInteger('l2')->default(0)->comment('二级分类');
            $table->unsignedBigInteger('l3')->default(0)->comment('三级分类');
            $table->timestamps();

            $table->index('provider_app_id');
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
        Schema::dropIfExists('provider_app_recommend_categories');
    }
}
