<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersAddressBookLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_address_book_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_book_id')->comment('关联通讯录id');
            $table->tinyInteger('type')->default(1)->comment('变更类型 1-系统变更，2-用户变更');
            $table->tinyInteger('status')->default(1)->comment('是否真实姓名 1-否，2-是');
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
        Schema::dropIfExists('users_address_book_log');
    }
}
