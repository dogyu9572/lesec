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
        Schema::create('mail_sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_sms_message_id')->constrained('mail_sms_messages')->cascadeOnDelete()->comment('메일/SMS 메시지 ID');
            $table->unsignedInteger('send_sequence')->default(1)->comment('발송 회차');
            $table->foreignId('mail_sms_message_member_id')->nullable()->constrained('mail_sms_message_member')->nullOnDelete()->comment('수신 대상 연결 ID');
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete()->comment('회원 ID');
            $table->string('member_name')->comment('회원 이름');
            $table->string('member_email')->nullable()->comment('회원 이메일');
            $table->string('member_contact', 50)->nullable()->comment('회원 연락처');
            $table->enum('result_status', ['success', 'failure'])->default('success')->comment('발송 결과');
            $table->timestamp('sent_at')->nullable()->comment('발송 일시');
            $table->string('response_code')->nullable()->comment('외부 API 응답 코드');
            $table->text('response_message')->nullable()->comment('응답 메시지');
            $table->timestamps();

            $table->index(['mail_sms_message_id', 'send_sequence'], 'mail_sms_logs_message_sequence_idx');
            $table->index(['mail_sms_message_id', 'result_status'], 'mail_sms_logs_message_status_idx');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_sms_logs');
    }
};
