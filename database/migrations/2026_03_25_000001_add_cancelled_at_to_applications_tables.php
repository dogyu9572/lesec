<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('individual_applications') && !Schema::hasColumn('individual_applications', 'cancelled_at')) {
            Schema::table('individual_applications', function (Blueprint $table) {
                $table->timestamp('cancelled_at')->nullable()->comment('취소일시')->after('applied_at');
            });
        }

        if (Schema::hasTable('group_applications') && !Schema::hasColumn('group_applications', 'cancelled_at')) {
            Schema::table('group_applications', function (Blueprint $table) {
                $table->timestamp('cancelled_at')->nullable()->comment('취소일시')->after('applied_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('individual_applications') && Schema::hasColumn('individual_applications', 'cancelled_at')) {
            Schema::table('individual_applications', function (Blueprint $table) {
                $table->dropColumn('cancelled_at');
            });
        }

        if (Schema::hasTable('group_applications') && Schema::hasColumn('group_applications', 'cancelled_at')) {
            Schema::table('group_applications', function (Blueprint $table) {
                $table->dropColumn('cancelled_at');
            });
        }
    }
};

