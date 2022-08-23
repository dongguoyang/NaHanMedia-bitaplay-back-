<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserInviteRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_invite_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->unsignedBigInteger('provider_id')->comment('服务商应用ID');
            $table->unsignedBigInteger('provider_app_id')->comment('服务商应用ID');
            $table->char('number', 18)->comment('订单号');
            $table->unsignedBigInteger('amount')->comment('奖励金额');
            $table->timestamps();

            $table->index('user_id');
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
        Schema::dropIfExists('user_invite_rewards');
    }
}
