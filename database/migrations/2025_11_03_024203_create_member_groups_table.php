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
        Schema::create('member_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('그룹명');
            $table->text('description')->nullable()->comment('그룹 설명');
            $table->boolean('is_active')->default(true)->comment('활성화 여부');
            $table->integer('member_count')->default(0)->comment('소속 회원 수');
            $table->integer('sort_order')->default(0)->comment('정렬 순서');
            $table->string('color', 20)->nullable()->comment('표시 색상');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_groups');
    }
};
