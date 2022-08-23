<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->string('name', 50)->default('')->comment('姓名');
            $table->char('id_number', 18)->default('')->comment('身份证号');
            $table->string('id_face')->default('')->comment('身份证正面');
            $table->string('id_back')->default('')->comment('身份证反面');
            $table->string('province', 50)->default('')->comment('省');
            $table->string('city', 50)->default('')->comment('市');
            $table->string('county', 50)->default('')->comment('区县');
            $table->unsignedBigInteger('industry_id')->default(0)->comment('行业ID');
            $table->unsignedBigInteger('occupation_id')->default(0)->comment('职业ID');
            $table->json('educational_experience')->comment('教育经历');
            $table->json('address')->comment('收货地址');
            $table->char('hash', 64)->default('')->comment('链上HASH');
            $table->unsignedTinyInteger('status')->default(1)->comment('1-不开启，2-开启');
            $table->unsignedTinyInteger('ad_status')->default(1)->comment('1-不开启广告收益，2-开启广告收益');
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_infos');
    }
}
