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
        Schema::table('programs', function (Blueprint $table) {
            // 기존 unique 제약 제거
            $table->dropUnique(['type']);

            // application_type 컬럼 추가
            $table->enum('application_type', ['individual', 'group'])->default('individual')->after('type')->comment('신청유형');

            // type과 application_type 조합으로 unique 제약 추가
            $table->unique(['type', 'application_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropUnique(['type', 'application_type']);
            $table->dropColumn('application_type');
            $table->unique('type');
        });
    }
};
