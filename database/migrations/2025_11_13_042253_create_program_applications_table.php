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
        if (Schema::hasTable('program_applications')) {
            return;
        }

        Schema::create('program_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_reservation_id')->constrained('program_reservations')->cascadeOnUpdate()->restrictOnDelete()->comment('프로그램 예약 ID');
            $table->foreignId('member_id')->nullable()->constrained('members')->cascadeOnUpdate()->nullOnDelete()->comment('신청 회원 ID');
            $table->string('application_number', 30)->unique()->comment('신청 번호');
            $table->enum('education_type', ['middle_semester', 'middle_vacation', 'high_semester', 'high_vacation', 'special'])->comment('교육유형');
            $table->enum('reception_type', ['first_come', 'lottery', 'naver_form'])->comment('신청유형');
            $table->string('program_name')->comment('프로그램명');
            $table->date('participation_date')->comment('참가일');
            $table->unsignedInteger('participation_fee')->nullable()->comment('참가비');
            $table->enum('payment_method', ['bank_transfer', 'on_site_card', 'online_card'])->nullable()->comment('결제방법');
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid')->comment('결제상태');
            $table->enum('draw_result', ['pending', 'win', 'waitlist', 'fail'])->default('pending')->comment('추첨결과');
            $table->string('applicant_name', 50)->comment('신청자명');
            $table->string('applicant_school_name', 100)->nullable()->comment('학교명');
            $table->unsignedTinyInteger('applicant_grade')->nullable()->comment('학년');
            $table->unsignedTinyInteger('applicant_class')->nullable()->comment('반');
            $table->string('applicant_contact', 20)->comment('신청자 연락처');
            $table->string('guardian_contact', 20)->nullable()->comment('보호자 연락처');
            $table->timestamp('applied_at')->useCurrent()->comment('신청일시');
            $table->timestamps();

            $table->index('program_reservation_id');
            $table->index('member_id');
            $table->index('education_type');
            $table->index('reception_type');
            $table->index('participation_date');
            $table->index('payment_status');
            $table->index('draw_result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_applications');
    }
};
