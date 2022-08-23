<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->char('tel', 11)->comment('手机号');
            $table->char('code', 8)->comment('邀请码');
            $table->unsignedBigInteger('pid')->default(0)->comment('邀请人ID');
            $table->string('trans_password')->default('')->comment('支付密码');
            $table->string('nickname', 30)->comment('昵称');
            $table->string('avatar')->comment('头像');
            $table->string('email', 60)->default('')->comment('邮箱');
            $table->string('desc')->default('')->comment('个人简介');
            $table->char('token', 32)->comment('登录凭证');
            $table->unsignedTinyInteger('status')->default(3)->comment('1-注销，2-禁用，3-正常');
            $table->timestamps();

            $table->index(['tel', 'status']);
            $table->unique('code');
            $table->unique('token');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
