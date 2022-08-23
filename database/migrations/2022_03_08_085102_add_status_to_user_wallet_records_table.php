<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToUserWalletRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_wallet_records', function (Blueprint $table) {
            $table->unsignedTinyInteger('status')->default(3)->comment('1-待处理，2-拒绝，3-成功');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_wallet_records', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
