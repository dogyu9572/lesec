<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('일정 제목');
            $table->date('start_date')->comment('시작 날짜');
            $table->date('end_date')->comment('종료 날짜');
            $table->text('content')->nullable()->comment('일정 내용');
            $table->boolean('disable_application')->default(false)->comment('기간 내 교육신청 불가 여부');
            $table->timestamps();
            
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
