<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 이미 migrate 된 DB에는 CREATE 마이그레이션 수정만으로 컬럼 타입이 바뀌지 않습니다.
 * 암호화 문자열 저장을 위해 기존 DB의 PII 컬럼을 TEXT로 맞춥니다.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasTable('members')) {
            DB::statement("ALTER TABLE `members`
                MODIFY `email` TEXT NOT NULL COMMENT '이메일(암호화 저장)',
                MODIFY `birth_date` TEXT NULL COMMENT '생년월일(암호화 저장)',
                MODIFY `contact` TEXT NULL COMMENT '연락처(암호화 저장)',
                MODIFY `parent_contact` TEXT NULL COMMENT '보호자 연락처(암호화 저장)',
                MODIFY `school_name` TEXT NULL COMMENT '학교명(암호화 저장)'");
        }

        if (Schema::hasTable('group_applications')) {
            DB::statement("ALTER TABLE `group_applications`
                MODIFY `applicant_contact` TEXT NULL COMMENT '신청자 연락처(암호화 저장)',
                MODIFY `school_name` TEXT NULL COMMENT '학교명(암호화 저장)'");
        }

        if (Schema::hasTable('individual_applications')) {
            DB::statement("ALTER TABLE `individual_applications`
                MODIFY `applicant_school_name` TEXT NULL COMMENT '학교명(암호화 저장)',
                MODIFY `applicant_contact` TEXT NOT NULL COMMENT '신청자 연락처(암호화 저장)',
                MODIFY `guardian_contact` TEXT NULL COMMENT '보호자 연락처(암호화 저장)'");
        }
    }

    /**
     * 암호화된 값이 들어간 뒤 이전 타입으로 되돌리면 데이터가 깨집니다. 운영에서는 실행하지 마세요.
     */
    public function down(): void
    {
    }
};
