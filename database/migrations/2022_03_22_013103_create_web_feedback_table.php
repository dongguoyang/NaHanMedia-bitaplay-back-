<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_feedback', function (Blueprint $table) {
            $table->id();
            $table->char('tel', 11)->comment('联系电话');
            $table->string('name')->default('')->comment('联系人');
            $table->text('content')->comment('内容');
            $table->string('remark')->default('')->comment('处理备注');
            $table->unsignedTinyInteger('status')->default(1)->comment('1-待处理，2-已处理');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('web_feedback');
    }
}
