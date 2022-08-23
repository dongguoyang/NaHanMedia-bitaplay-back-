<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin', function (Blueprint $table) {
            $table->id();
            $table->string('username', 20)->comment('用户名');
            $table->string('password')->comment('密码');
            $table->string('nickname', 30)->comment('昵称');
            $table->string('avatar', 150)->comment('头像');
            $table->char('token', 32)->comment('登录凭证');
            $table->timestamps();

            $table->unique('username');
            $table->unique('token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin');
    }
}
