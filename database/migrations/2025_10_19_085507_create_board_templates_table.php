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
        Schema::create('board_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('skin_id');
            
            // 필드 설정
            $table->json('field_config')->nullable()->comment('기본 필드 활성화 설정');
            $table->json('custom_fields_config')->nullable()->comment('커스텀 필드 설정');
            
            // 기능 설정
            $table->boolean('enable_notice')->default(true)->comment('공지사항 활성화 여부');
            $table->boolean('enable_sorting')->default(false)->comment('정렬 기능 활성화 여부');
            $table->boolean('enable_category')->default(true)->comment('카테고리 기능 활성화 여부');
            $table->foreignId('category_id')->nullable()->after('enable_category')->constrained('categories')->onDelete('set null')->comment('카테고리 그룹 ID (depth=0)');
            $table->boolean('is_single_page')->default(false)->comment('단일 페이지 모드');
            
            // 목록 및 권한 설정
            $table->integer('list_count')->default(15)->comment('페이지당 게시물 수');
            $table->string('permission_read')->default('all')->comment('읽기 권한');
            $table->string('permission_write')->default('member')->comment('쓰기 권한');
            $table->string('permission_comment')->default('member')->comment('댓글 권한');
            
            // 시스템 설정
            $table->boolean('is_system')->default(false)->comment('시스템 템플릿 여부');
            $table->boolean('is_active')->default(true)->comment('활성화 여부');
            
            $table->timestamps();
            
            // 외래 키
            $table->foreign('skin_id')->references('id')->on('board_skins');
            
            // 인덱스
            $table->index('is_system');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('board_templates');
    }
};
