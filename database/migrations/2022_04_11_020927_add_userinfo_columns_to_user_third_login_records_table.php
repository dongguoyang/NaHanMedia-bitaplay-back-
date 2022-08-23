<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserinfoColumnsToUserThirdLoginRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_third_login_records', function (Blueprint $table) {
            $table->string('nickname')->default('')->comment('用户昵称');
            $table->string('avatar')->default('')->comment('用户头像');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_third_login_records', function (Blueprint $table) {
            //
        });
    }
}
