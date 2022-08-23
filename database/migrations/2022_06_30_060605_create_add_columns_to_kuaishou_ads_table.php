<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddColumnsToKuaishouAdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kuaishou_ads', function (Blueprint $table) {
            $table->text('callback')->default('');
            $table->string('oaid')->default('')->index();
            $table->unsignedTinyInteger('status')->default(1)->comment('1-未回调，2-已回调');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kuaishou_ads', function (Blueprint $table) {
            $table->dropColumn('callback');
            $table->dropColumn('oaid');
            $table->dropColumn('status');
        });
    }
}
