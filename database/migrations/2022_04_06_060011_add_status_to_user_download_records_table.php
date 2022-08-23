<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToUserDownloadRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_download_records', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1)->comment('1-未奖励，2-已奖励');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_download_records', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
