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
        Schema::table('program_reservations', function (Blueprint $table) {
            $table->integer('applied_count')->default(0)->after('capacity')->comment('신청 인원 수');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_reservations', function (Blueprint $table) {
            $table->dropColumn('applied_count');
        });
    }
};
