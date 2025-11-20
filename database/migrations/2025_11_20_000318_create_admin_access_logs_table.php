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
        Schema::create('admin_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null')->comment('관리자 ID');
            $table->string('name')->comment('관리자명');
            $table->string('ip_address', 45)->comment('IP 주소');
            $table->text('user_agent')->nullable()->comment('브라우저 정보');
            $table->string('referer', 500)->nullable()->comment('이전 페이지 URL');
            $table->timestamp('accessed_at')->comment('접속 시각');
            $table->timestamps();
            
            // 인덱스
            $table->index('admin_id');
            $table->index('accessed_at');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_access_logs');
    }
};
