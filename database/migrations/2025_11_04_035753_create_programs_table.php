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
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['middle_semester', 'middle_vacation', 'high_semester', 'high_vacation', 'special'])->comment('프로그램 타입');
            $table->enum('application_type', ['individual', 'group'])->default('individual')->comment('신청유형');
            $table->string('host')->nullable()->comment('주최');
            $table->date('period_start')->nullable()->comment('기간 시작일');
            $table->date('period_end')->nullable()->comment('기간 종료일');
            $table->string('location')->nullable()->comment('장소');
            $table->string('target')->nullable()->comment('대상');
            $table->text('detail_content')->nullable()->comment('상세내용 (HTML)');
            $table->text('other_info')->nullable()->comment('기타 안내 (HTML)');
            $table->boolean('is_active')->default(true)->comment('활성화 여부');
            $table->timestamps();

            // 타입+신청유형 조합당 1개
            $table->unique(['type', 'application_type']);
            
            // 인덱스
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
