<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBannerToProviderAppVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provider_app_versions', function (Blueprint $table) {
            $table->string('banner')->default('')->comment('详情页背景图');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provider_app_versions', function (Blueprint $table) {
            $table->dropColumn('banner');
        });
    }
}
