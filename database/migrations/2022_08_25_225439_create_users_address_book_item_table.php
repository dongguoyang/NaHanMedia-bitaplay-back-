<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersAddressBookItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_address_book_item', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('接听用户id');
            $table->unsignedBigInteger('incoming_user_id')->comment('来电用户id');
            $table->string('answer_nickname', 20)->comment('接听用户呢称');
            $table->string('answer_phone', 20)->comment('接听用户手机号');
            $table->string('incoming_phone', 20)->comment('来电用户手机号');
            $table->string('incoming_real_name', 20)->comment('来电用户真实姓名');
            $table->string('incoming_company', 20)->comment('来电用户公司');
            $table->string('incoming_time', 20)->comment('通话时长');
            $table->string('incoming_nickname', 20)->comment('来电用户呢称');
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
        Schema::dropIfExists('users_address_book_item');
    }
}
