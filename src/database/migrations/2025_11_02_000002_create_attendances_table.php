<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0:勤務外, 1:出勤中, 2:休憩中, 3:退勤済');
            $table->text('note')->nullable();
            $table->timestamps();

            // 複合ユニークキー: 1ユーザーにつき1日1レコード
            $table->unique(['user_id', 'date']);
            
            // インデックス
            $table->index('date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
