<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreakHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('break_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('break_id')->constrained('breaks')->onDelete('cascade'); // 重要：break_idを参照
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade'); // 変更者
            $table->time('before_break_start')->nullable();
            $table->time('after_break_start')->nullable();
            $table->time('before_break_end')->nullable();
            $table->time('after_break_end')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            
            // インデックス
            $table->index('break_id');
            $table->index('changed_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('break_histories');
    }
}
