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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('cascade')->comment('상위 카테고리 ID');
            $table->string('category_group', 50)->comment('카테고리 그룹 (board, product 등)');
            $table->string('name', 100)->comment('카테고리명');
            $table->tinyInteger('depth')->default(1)->comment('깊이 (1~5)');
            $table->integer('display_order')->default(0)->comment('정렬 순서');
            $table->boolean('is_active')->default(true)->comment('활성화 여부');
            $table->timestamps();

            // 인덱스
            $table->index(['category_group', 'parent_id']);
            $table->index(['category_group', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
