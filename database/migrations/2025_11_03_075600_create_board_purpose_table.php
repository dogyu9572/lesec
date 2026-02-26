<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('board_purpose', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title');
            $table->text('content');
            $table->string('author_name');
            $table->string('password')->nullable();
            $table->boolean('is_notice')->default(false);
            $table->boolean('is_secret')->default(false);
            $table->boolean('is_active')->default(true)->comment('활성화 여부');
            $table->string('category')->nullable();
            $table->json('attachments')->nullable();
            $table->integer('view_count')->default(0);
            $table->integer('sort_order')->default(0)->comment('정렬 순서');
            $table->json('custom_fields')->nullable();
            $table->string('thumbnail')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_notice', 'created_at']);
            $table->index(['category', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['thumbnail']);
            $table->index(['sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('board_purpose');
    }
};
