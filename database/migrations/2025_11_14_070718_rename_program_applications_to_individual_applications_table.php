<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('program_applications') && !Schema::hasTable('individual_applications')) {
            DB::statement('RENAME TABLE program_applications TO individual_applications');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('individual_applications') && !Schema::hasTable('program_applications')) {
            DB::statement('RENAME TABLE individual_applications TO program_applications');
        }
    }
};
