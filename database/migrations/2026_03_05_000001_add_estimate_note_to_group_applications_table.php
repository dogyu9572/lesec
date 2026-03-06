<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 단체 신청 테이블에 견적서 비고 컬럼 추가
     */
    public function up(): void
    {
        // 운영 서버처럼 기존 테이블에 컬럼이 없는 경우에만 추가
        if (!Schema::hasColumn('group_applications', 'estimate_note')) {
            Schema::table('group_applications', function (Blueprint $table) {
                $table->text('estimate_note')->nullable()->comment('견적서 비고')->after('participation_date');
            });
        }
    }

    /**
     * 롤백 시 견적서 비고 컬럼 제거
     */
    public function down(): void
    {
        if (Schema::hasColumn('group_applications', 'estimate_note')) {
            Schema::table('group_applications', function (Blueprint $table) {
                $table->dropColumn('estimate_note');
            });
        }
    }
};

