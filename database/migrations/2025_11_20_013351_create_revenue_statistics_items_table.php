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
        Schema::create('revenue_statistics_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('revenue_statistics_id')->constrained('revenue_statistics')->onDelete('cascade')->comment('수익 통계 ID');
            $table->string('item_name')->comment('항목명');
            $table->integer('participants_count')->default(0)->comment('참가인원');
            $table->string('school_name')->nullable()->comment('참가학교');
            $table->integer('revenue')->default(0)->comment('수익');
            $table->integer('sort_order')->default(0)->comment('정렬 순서');
            $table->timestamps();
            
            // 인덱스
            $table->index('revenue_statistics_id');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenue_statistics_items');
    }
};
