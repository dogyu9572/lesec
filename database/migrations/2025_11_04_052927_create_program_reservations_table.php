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
        Schema::create('program_reservations', function (Blueprint $table) {
            $table->id();
            $table->enum('education_type', ['middle_semester', 'middle_vacation', 'high_semester', 'high_vacation', 'special'])->comment('교육유형');
            $table->enum('application_type', ['group', 'individual'])->comment('신청유형');
            $table->string('program_name')->comment('프로그램명');
            $table->date('education_start_date')->nullable()->comment('교육 시작일');
            $table->date('education_end_date')->nullable()->comment('교육 종료일');
            $table->json('payment_methods')->nullable()->comment('결제수단 (JSON 배열)');
            $table->enum('reception_type', ['application', 'remaining', 'closed', 'first_come', 'lottery', 'naver_form'])->nullable()->comment('접수유형 (단체용: 신청/잔여석 신청/마감, 개인용: 선착순/추첨/네이버폼)');
            $table->date('application_start_date')->nullable()->comment('신청 시작일');
            $table->date('application_end_date')->nullable()->comment('신청 종료일');
            $table->integer('capacity')->nullable()->comment('신청정원');
            $table->boolean('is_unlimited_capacity')->default(false)->comment('제한없음 여부');
            $table->decimal('education_fee', 10, 2)->nullable()->comment('교육비');
            $table->boolean('is_free')->default(false)->comment('무료 여부');
            $table->string('naver_form_url')->nullable()->comment('네이버폼 링크');
            $table->string('waitlist_url')->nullable()->comment('대기자 신청 링크');
            $table->string('author', 100)->nullable()->comment('작성자');
            $table->boolean('is_active')->default(true)->comment('활성화 여부');
            $table->timestamps();

            // 인덱스
            $table->index('education_type');
            $table->index('application_type');
            $table->index('education_start_date');
            $table->index('application_start_date');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_reservations');
    }
};
