<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHelpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('helps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id')->comment('软件商ID');
            $table->char('tel', 11)->comment('联系电话');
            $table->unsignedTinyInteger('type')->default(1)->comment('1-技术顾问，2-营销顾问');
            $table->unsignedTinyInteger('status')->default(1)->comment('1-待处理，2-已处理');
            $table->timestamps();

            $table->index(['provider_id', 'tel']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('helps');
    }
}
