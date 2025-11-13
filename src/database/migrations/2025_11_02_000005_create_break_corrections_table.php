<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreakCorrectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('break_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correction_request_id')
                ->constrained('attendance_correction_requests')
                ->onDelete('cascade');
            $table->time('break_start');
            $table->time('break_end')->nullable(); // 休憩中の修正もあり得る
            $table->timestamps();
            
            // インデックス
            $table->index('correction_request_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('break_corrections');
    }
}
