<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDownloadCountToProviderAppsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provider_apps', function (Blueprint $table) {
            $table->unsignedBigInteger('download_count')->default(0)->comment('虚拟下载量');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provider_apps', function (Blueprint $table) {
            $table->dropColumn('download_count');
        });
    }
}
