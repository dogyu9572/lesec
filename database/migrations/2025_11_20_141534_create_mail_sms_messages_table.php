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
        Schema::create('mail_sms_messages', function (Blueprint $table) {
            $table->id();
            $table->enum('message_type', ['email', 'sms', 'kakao'])->comment('발송 구분');
            $table->string('title')->comment('제목');
            $table->mediumText('content')->comment('내용');
            $table->foreignId('writer_id')->constrained('users')->comment('작성 관리자');
            $table->foreignId('member_group_id')->nullable()->constrained('member_groups')->nullOnDelete()->comment('선택한 회원 그룹');
            $table->unsignedInteger('success_count')->default(0)->comment('성공 건수');
            $table->unsignedInteger('failure_count')->default(0)->comment('실패 건수');
            $table->enum('status', ['prepared', 'sending', 'completed'])->default('prepared')->comment('진행 상태');
            $table->timestamp('send_requested_at')->nullable()->comment('발송 요청 일시');
            $table->timestamp('send_started_at')->nullable()->comment('발송 시작 일시');
            $table->timestamp('send_completed_at')->nullable()->comment('발송 완료 일시');
            $table->text('last_error_message')->nullable()->comment('마지막 오류 메시지');
            $table->timestamps();

            $table->index('message_type');
            $table->index('status');
            $table->index('send_requested_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_sms_messages');
    }
};
