<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('program_reservations', 'education_fee')) {
            DB::statement('UPDATE program_reservations SET education_fee = ROUND(education_fee) WHERE education_fee IS NOT NULL');
            DB::statement('ALTER TABLE program_reservations MODIFY education_fee INT UNSIGNED NULL COMMENT "교육비"');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('program_reservations', 'education_fee')) {
            DB::statement('ALTER TABLE program_reservations MODIFY education_fee DECIMAL(10, 2) NULL COMMENT "교육비"');
        }
    }
};

