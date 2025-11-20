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
        Schema::table('visitor_logs', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->after('user_id')->constrained('members')->onDelete('set null')->comment('일반 사용자 ID (로그인한 경우)');
            $table->index('member_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitor_logs', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->dropIndex(['member_id']);
            $table->dropColumn('member_id');
        });
    }
};
