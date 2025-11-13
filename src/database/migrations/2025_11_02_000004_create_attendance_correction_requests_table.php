<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceCorrectionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_correction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 申請者
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->text('note'); // 修正理由
            $table->tinyInteger('status')->default(0)->comment('0:承認待ち, 1:承認済み');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null'); // 承認者
            $table->timestamps();
            
            // インデックス
            $table->index('attendance_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_correction_requests');
    }
}
