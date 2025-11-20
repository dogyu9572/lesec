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
        Schema::create('mail_sms_message_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_sms_message_id')->constrained('mail_sms_messages')->cascadeOnDelete()->comment('메일/SMS 메시지 ID');
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete()->comment('회원 ID');
            $table->string('member_name')->comment('회원 이름');
            $table->string('member_email')->nullable()->comment('회원 이메일');
            $table->string('member_contact', 50)->nullable()->comment('회원 연락처');
            $table->boolean('is_selected')->default(true)->comment('선택 여부');
            $table->timestamps();

            $table->unique(['mail_sms_message_id', 'member_id'], 'mail_sms_message_member_unique');
            $table->index('member_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_sms_message_member');
    }
};
