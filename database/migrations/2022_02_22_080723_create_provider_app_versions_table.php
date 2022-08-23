<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderAppVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_app_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id')->comment('服务商ID');
            $table->unsignedBigInteger('provider_app_id')->comment('服务商应用ID');
            $table->string('package_name')->default('')->comment('包名');
            $table->string('ios_package_name')->default('')->comment('IOS包名');
            $table->string('version')->comment('版本号');
            $table->string('icon')->comment('首页图标');
            $table->json('image')->comment('截图');
            $table->text('desc')->comment('介绍');
            $table->string('web')->default('')->comment('网页地址');
            $table->string('ios')->default('')->comment('IOS地址');
            $table->unsignedTinyInteger('is_ios')->default(1)->comment('0-不支持，1-支持');
            $table->unsignedTinyInteger('is_android')->default(1)->comment('0-不支持，1-支持');
            $table->unsignedTinyInteger('is_web')->default(1)->comment('0-不支持，1-支持');
            $table->unsignedTinyInteger('status')->default(1)->comment('1-待审核，2-通过，3-拒绝');
            $table->string('refuse_reason')->default('')->comment('拒绝理由');
            $table->timestamps();

            $table->index('provider_id');
            $table->index('provider_app_id');
            $table->index('status');
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
        Schema::dropIfExists('provider_app_versions');
    }
}
