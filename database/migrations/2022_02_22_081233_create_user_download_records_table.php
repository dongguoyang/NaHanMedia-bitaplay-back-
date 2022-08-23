<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDownloadRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_download_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->unsignedBigInteger('provider_app_id')->comment('服务商应用ID');
            $table->char('number', 18)->comment('订单号');
            $table->unsignedBigInteger('reward_amount')->default(0)->comment('奖金');
            $table->unsignedBigInteger('user_reward_amount')->default(0)->comment('用户得到奖金');
            $table->timestamps();

            $table->index('user_id');
            $table->index('provider_app_id');
            $table->unique('number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_download_records');
    }
}
