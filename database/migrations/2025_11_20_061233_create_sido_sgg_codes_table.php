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
        Schema::create('sido_sgg_codes', function (Blueprint $table) {
            $table->id();
            $table->string('sido_code', 2)->comment('시도코드');
            $table->string('sido_name', 50)->comment('시도명');
            $table->string('sgg_code', 5)->comment('시군구코드');
            $table->string('sgg_name', 100)->comment('시군구명');
            $table->timestamps();
            
            // 인덱스
            $table->index('sido_code');
            $table->index('sgg_code');
            $table->index(['sido_code', 'sgg_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sido_sgg_codes');
    }
};
