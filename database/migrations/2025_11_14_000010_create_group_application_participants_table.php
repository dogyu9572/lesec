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
        Schema::create('group_application_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_application_id')->constrained('group_applications')->cascadeOnUpdate()->cascadeOnDelete()->comment('단체 신청 ID');
            $table->string('name', 50)->comment('이름');
            $table->unsignedTinyInteger('grade')->nullable()->comment('학년');
            $table->string('class', 20)->nullable()->comment('반');
            $table->date('birthday')->nullable()->comment('생년월일');
            $table->timestamps();

            $table->index('group_application_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_application_participants');
    }
};



