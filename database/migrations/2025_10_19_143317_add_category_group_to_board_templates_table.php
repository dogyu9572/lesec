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
        Schema::table('board_templates', function (Blueprint $table) {
            $table->string('category_group')->nullable()->comment('카테고리 그룹명');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('board_templates', function (Blueprint $table) {
            $table->dropColumn('category_group');
        });
    }
};
