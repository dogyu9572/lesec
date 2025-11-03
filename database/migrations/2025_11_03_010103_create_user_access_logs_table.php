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
        Schema::create('user_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('사용자 ID');
            $table->string('name')->comment('회원명/관리자명');
            $table->string('ip_address', 45)->comment('IP 주소');
            $table->text('user_agent')->nullable()->comment('브라우저 정보');
            $table->string('referer', 500)->nullable()->comment('이전 페이지 URL');
            $table->timestamp('login_at')->comment('로그인 시각');
            $table->timestamps();
            
            // 인덱스
            $table->index('user_id');
            $table->index('login_at');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_access_logs');
    }
};
