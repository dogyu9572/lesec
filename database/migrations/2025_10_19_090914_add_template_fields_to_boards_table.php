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
        Schema::table('boards', function (Blueprint $table) {
            $table->unsignedBigInteger('template_id')->nullable()->after('skin_id');
            $table->json('field_config')->nullable()->after('custom_fields_config')->comment('필드 활성화 설정');
            
            $table->foreign('template_id')->references('id')->on('board_templates')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropColumn(['template_id', 'field_config']);
        });
    }
};
