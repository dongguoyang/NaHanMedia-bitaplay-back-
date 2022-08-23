<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSearchWordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_words', function (Blueprint $table) {
            $table->id();
            $table->string('word')->comment('搜索词');
            $table->unsignedBigInteger('count')->default(0)->comment('搜索次数');
            $table->timestamps();

            $table->index('word');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('search_words');
    }
}
