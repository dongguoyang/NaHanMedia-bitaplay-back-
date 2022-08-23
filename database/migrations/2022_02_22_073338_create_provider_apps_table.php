<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderAppsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_apps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id')->comment('服务商ID');
            $table->char('app_id', 32);
            $table->char('app_key', 32);
            $table->char('app_secret', 16);
            $table->string('version')->default('')->comment('版本号');
            $table->string('name')->comment('应用名称');
            $table->string('package_name')->default('')->comment('包名');
            $table->string('ios_package_name')->default('')->comment('IOS包名');
            $table->string('icon')->default('')->comment('首页图标');
            $table->text('desc')->comment('介绍');
            $table->json('image')->comment('截图');
            $table->string('web')->default('')->comment('网页地址');
            $table->string('ios')->default('')->comment('IOS地址');
            $table->unsignedTinyInteger('is_ios')->default(0)->comment('0-不支持，1-支持');
            $table->unsignedTinyInteger('is_android')->default(0)->comment('0-不支持，1-支持');
            $table->unsignedTinyInteger('is_web')->default(0)->comment('0-不支持，1-支持');
            $table->unsignedTinyInteger('is_download_reward')->default(0)->comment('0-不开通，1-开通');
            $table->unsignedTinyInteger('is_to_chain')->default(0)->comment('0-不开通，1-开通');
            $table->unsignedTinyInteger('is_third_login')->default(0)->comment('0-不开通，1-开通');
            $table->unsignedTinyInteger('status')->default(1)->comment('1-下架，2-上架');
            $table->unsignedBigInteger('download_reward')->default(0)->comment('下载奖励');
            $table->unsignedBigInteger('reward_amount')->default(0)->comment('下载奖金');
            $table->unsignedBigInteger('fuel_amount')->default(0)->comment('燃料');
            $table->unsignedBigInteger('third_login_amount')->default(0)->comment('三方登录调用次数');
            $table->unsignedTinyInteger('is_recommend')->default(0)->comment('0-不推荐，1-推荐');
            $table->timestamps();

            $table->index('provider_id');
            $table->unique('app_id');
            $table->unique('app_key');
            $table->unique('name');
            $table->index('reward_amount');
            $table->index(['is_ios','ios_package_name']);
            $table->index(['is_android','package_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_apps');
    }
}
