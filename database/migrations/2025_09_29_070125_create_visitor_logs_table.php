<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('visitor_logs')) {
            Schema::create('visitor_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('사용자 ID (로그인한 경우)');
                $table->unsignedBigInteger('member_id')->nullable()->comment('일반 사용자 ID (로그인한 경우)');
                $table->string('ip_address', 45)->comment('방문자 IP 주소');
                $table->text('user_agent')->nullable()->comment('브라우저 정보');
                $table->string('page_url', 500)->comment('방문한 페이지 URL');
                $table->string('referer', 500)->nullable()->comment('이전 페이지 URL');
                $table->string('session_id', 100)->nullable()->comment('세션 ID');
                $table->boolean('is_unique')->default(false)->comment('고유 방문자 여부');
                $table->timestamps();
                
                // 인덱스
                $table->index(['created_at']);
                $table->index(['ip_address', 'created_at']);
                $table->index(['is_unique', 'created_at']);
            });
        }
        
        // members 테이블이 생성된 후 외래키 추가
        if (Schema::hasTable('members') && Schema::hasTable('visitor_logs')) {
            // 기존 외래키가 있는지 확인
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'visitor_logs' 
                AND COLUMN_NAME = 'member_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            if (empty($foreignKeys)) {
                Schema::table('visitor_logs', function (Blueprint $table) {
                    $table->foreign('member_id')->references('id')->on('members')->onDelete('set null');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
    }
};
