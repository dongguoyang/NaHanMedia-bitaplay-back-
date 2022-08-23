<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleToProviderInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provider_infos', function (Blueprint $table) {
            $table->unsignedTinyInteger('role')->default(1)->comment('1-企业，2-个人');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provider_infos', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
}
