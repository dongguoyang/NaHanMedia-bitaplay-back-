<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppVersionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_version', function (Blueprint $table) {
            $table->id();
            $table->string('url', 255)->comment('安装包');
            $table->string('version', 50)->comment('版本号');
            $table->text('remark')->comment('更新内容');
            $table->tinyInteger('status')->default(1)->comment('是否启用 1-否，2-是');
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
        Schema::dropIfExists('app_version');
    }
}
