<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStampCorrectionRequestBreakTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stamp_correction_request_break_times', function (Blueprint $table) {
            $table->id();
            
            // 🌟 分けて書くことで、長すぎエラーを回避！
            $table->unsignedBigInteger('stamp_correction_request_id');
            $table->foreign('stamp_correction_request_id', 'scr_break_times_fk')
                  ->references('id')
                  ->on('stamp_correction_requests')
                  ->cascadeOnDelete();
            
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            
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
        Schema::dropIfExists('stamp_correction_request_break_times');
    }
}
