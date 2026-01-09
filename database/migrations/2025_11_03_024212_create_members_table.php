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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            
            // 기본 정보
            $table->string('login_id')->unique()->comment('로그인 ID');
            $table->string('password')->comment('비밀번호');
            $table->enum('member_type', ['teacher', 'student'])->comment('회원 구분');
            $table->foreignId('member_group_id')->nullable()->constrained('member_groups')->comment('회원 그룹');
            
            // 개인 정보
            $table->string('name')->comment('이름');
            $table->string('email')->comment('이메일');
            $table->date('birth_date')->nullable()->comment('생년월일');
            $table->enum('gender', ['male', 'female'])->nullable()->comment('성별');
            $table->string('contact', 50)->nullable()->comment('연락처');
            $table->string('parent_contact', 50)->nullable()->comment('보호자 연락처');
            
            // 소속 정보
            $table->string('city', 100)->nullable()->comment('시/도');
            $table->string('district', 100)->nullable()->comment('시/군/구');
            $table->string('school_name')->nullable()->comment('학교명');
            $table->unsignedBigInteger('school_id')->nullable()->comment('학교 ID');
            $table->tinyInteger('grade')->nullable()->comment('학년');
            $table->tinyInteger('class_number')->nullable()->comment('반');
            
            // 주소 정보
            $table->string('address')->nullable()->comment('주소');
            $table->string('zipcode', 10)->nullable()->comment('우편번호');
            
            // 비상 연락처
            $table->string('emergency_contact', 50)->nullable()->comment('비상 연락처');
            $table->string('emergency_contact_relation', 50)->nullable()->comment('비상 연락처 관계');
            
            // 프로필
            $table->string('profile_image')->nullable()->comment('프로필 이미지');
            
            // 수신 동의
            $table->boolean('email_consent')->default(false)->comment('이메일 수신 동의');
            $table->boolean('sms_consent')->default(false)->comment('SMS 수신 동의');
            $table->boolean('kakao_consent')->default(false)->comment('카카오 알림톡 수신 동의');
            
            // 메모
            $table->text('memo')->nullable()->comment('메모');
            
            // 상태 관리
            $table->boolean('is_active')->default(true)->comment('활성화 여부');
            $table->timestamp('joined_at')->nullable()->comment('가입일');
            $table->timestamp('last_login_at')->nullable()->comment('마지막 로그인');
            $table->timestamp('withdrawal_at')->nullable()->comment('탈퇴일');
            $table->text('withdrawal_reason')->nullable()->comment('탈퇴 사유');
            
            $table->rememberToken();
            $table->timestamps();
            
            // 인덱스
            $table->index('member_type');
            $table->index('member_group_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
