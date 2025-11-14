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
        Schema::create('group_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_reservation_id')->constrained('program_reservations')->cascadeOnUpdate()->restrictOnDelete()->comment('프로그램 예약 ID');
            $table->string('application_number', 30)->unique()->comment('신청 번호');
            $table->enum('education_type', ['middle_semester', 'middle_vacation', 'high_semester', 'high_vacation', 'special'])->comment('교육 유형');
            $table->json('payment_methods')->nullable()->comment('결제 방법(다중 선택)');
            $table->enum('payment_method', ['bank_transfer', 'on_site_card', 'online_card'])->nullable()->comment('선택 결제 방법');
            $table->enum('application_status', ['pending', 'approved', 'cancelled'])->default('pending')->comment('신청 상태');
            $table->enum('reception_status', ['application', 'in_progress', 'completed', 'cancelled'])->default('application')->comment('접수 상태');
            $table->string('applicant_name', 50)->comment('신청자명');
            $table->foreignId('member_id')->nullable()->constrained('members')->cascadeOnUpdate()->nullOnDelete()->comment('회원 ID');
            $table->string('applicant_contact', 30)->nullable()->comment('신청자 연락처');
            $table->string('school_level', 50)->nullable()->comment('학교급');
            $table->string('school_name', 100)->nullable()->comment('학교명');
            $table->unsignedInteger('applicant_count')->default(0)->comment('신청 인원');
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid')->comment('결제 상태');
            $table->unsignedInteger('participation_fee')->nullable()->comment('참가비');
            $table->date('participation_date')->nullable()->comment('참가일');
            $table->timestamp('applied_at')->nullable()->comment('신청일시');
            $table->timestamps();

            $table->index('program_reservation_id');
            $table->index('application_status');
            $table->index('reception_status');
            $table->index('education_type');
            $table->index('member_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_applications');
    }
};


