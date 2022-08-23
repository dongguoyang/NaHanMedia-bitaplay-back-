<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id')->comment('服务商ID');
            $table->string('code')->comment('信用代码');
            $table->string('name')->comment('公司名称');
            $table->string('license')->comment('营业执照图片');
            $table->unsignedTinyInteger('status')->default(1)->comment('1-待认证，2-通过，3-拒绝');
            $table->string('refuse_reason')->default('')->comment('拒绝理由');
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
        Schema::dropIfExists('provider_infos');
    }
}
