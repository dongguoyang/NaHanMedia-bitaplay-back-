<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersAddressBookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_address_book', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('关联用户id');
            $table->unsignedBigInteger('user_book_id')->comment('关联注册的用户id');
            $table->string('nickname', 20)->comment('呢称');
            $table->string('real_name', 20)->comment('真实姓名');
            $table->string('phone', 18)->comment('手机号');
            $table->string('company')->comment('公司');
            $table->tinyInteger('type')->default(1)->comment('添加类型 1-自动导入，2-人工上传');
            $table->tinyInteger('status')->default(1)->comment('是否是注册用户 1-否，2-是');
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
        Schema::dropIfExists('users_address_book');
    }
}
