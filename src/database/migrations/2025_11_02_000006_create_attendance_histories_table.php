<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade'); // 変更者
            $table->tinyInteger('changed_type')->comment('0:申請承認, 1:管理者直接修正');
            $table->time('before_clock_in')->nullable();
            $table->time('after_clock_in')->nullable();
            $table->time('before_clock_out')->nullable();
            $table->time('after_clock_out')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            
            // インデックス
            $table->index('attendance_id');
            $table->index('changed_by');
            $table->index('changed_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_histories');
    }
}
