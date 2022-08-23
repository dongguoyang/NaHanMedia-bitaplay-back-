<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pid')->default(0)->comment('推荐用户ID');
            $table->char('tel', 11)->comment('手机号');
            $table->string('email', 32)->default('')->comment('邮箱');
            $table->string('trans_password')->default('')->comment('交易密码');
            $table->char('token', 32)->comment('登录凭证');
            $table->unsignedTinyInteger('status')->default(2)->comment('1-禁用，2-正常');
            $table->timestamps();

            $table->index('pid');
            $table->unique('tel');
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
        Schema::dropIfExists('providers');
    }
}
