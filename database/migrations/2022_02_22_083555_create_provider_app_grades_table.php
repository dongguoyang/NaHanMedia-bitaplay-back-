<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderAppGradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_app_grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_app_id')->comment('服务商应用ID');
            $table->unsignedBigInteger('app_grade_id')->comment('适用人群ID');
            $table->timestamps();

            $table->index('provider_app_id');
            $table->index('app_grade_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_app_grades');
    }
}
