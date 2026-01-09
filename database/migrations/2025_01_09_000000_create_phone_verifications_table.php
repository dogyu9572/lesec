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
        Schema::create('phone_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->comment('휴대폰 번호');
            $table->string('verification_code', 6)->comment('인증번호');
            $table->timestamp('verified_at')->nullable()->comment('인증 완료 시각');
            $table->timestamp('expires_at')->comment('만료 시각');
            $table->unsignedTinyInteger('attempts')->default(0)->comment('시도 횟수');
            $table->string('purpose', 50)->default('register')->comment('용도');
            $table->string('session_id', 255)->comment('세션 ID');
            $table->timestamps();
            
            // 인덱스
            $table->index('phone');
            $table->index(['phone', 'session_id', 'purpose']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phone_verifications');
    }
};
