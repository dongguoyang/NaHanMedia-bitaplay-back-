<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToKuaishouAdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kuaishou_ads', function (Blueprint $table) {
            $table->char('oaid2', 32);
            $table->index('oaid2');
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
            $table->dropColumn('oaid2');
        });
    }
}
