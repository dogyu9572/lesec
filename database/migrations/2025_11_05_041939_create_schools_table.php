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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            
            // 구분
            $table->enum('source_type', ['api', 'user_registration', 'admin_registration'])->comment('구분');
            
            // 지역 정보
            $table->string('city', 100)->nullable()->comment('시/도');
            $table->string('district', 100)->nullable()->comment('시/군/구');
            
            // 학교 정보
            $table->enum('school_level', ['elementary', 'middle', 'high'])->nullable()->comment('학교급');
            $table->string('school_name')->comment('학교명');
            $table->string('school_code', 50)->nullable()->comment('학교 코드');
            
            // 상세 정보
            $table->string('address')->nullable()->comment('주소');
            $table->string('phone', 50)->nullable()->comment('전화번호');
            $table->string('homepage')->nullable()->comment('홈페이지 주소');
            $table->boolean('is_coed')->nullable()->comment('남녀공학 여부');
            $table->string('day_night_division', 50)->nullable()->comment('주야구분');
            $table->date('founding_date')->nullable()->comment('개교기념일');
            
            // 상태 관리
            $table->enum('status', ['normal', 'error'])->default('normal')->comment('상태');
            $table->timestamp('last_synced_at')->nullable()->comment('마지막 동기화 일시');
            
            // 등록자/수정자
            $table->foreignId('created_by')->nullable()->constrained('users')->comment('등록자');
            $table->foreignId('updated_by')->nullable()->constrained('users')->comment('수정자');
            
            $table->timestamps();
            $table->softDeletes();
            
            // 인덱스
            $table->index('source_type');
            $table->index('city');
            $table->index('district');
            $table->index('school_level');
            $table->index('school_name');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
