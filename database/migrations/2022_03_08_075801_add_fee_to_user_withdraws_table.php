<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeeToUserWithdrawsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_withdraws', function (Blueprint $table) {
            $table->unsignedBigInteger('fee')->default(0)->comment('手续费');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_withdraws', function (Blueprint $table) {
            $table->dropColumn('fee');
        });
    }
}
