<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdCardImageToProviderInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provider_infos', function (Blueprint $table) {
            $table->string('id_card_face')->default('')->comment('身份证正面');
            $table->string('id_card_back')->default('')->comment('身份证正面');
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
            $table->dropColumn('id_card_face');
            $table->dropColumn('id_card_back');
        });
    }
}
