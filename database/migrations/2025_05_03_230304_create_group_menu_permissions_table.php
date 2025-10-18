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
        Schema::create('group_menu_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('admin_groups')->onDelete('cascade')->comment('권한 그룹 ID');
            $table->foreignId('menu_id')->constrained('admin_menus')->onDelete('cascade')->comment('메뉴 ID');
            $table->boolean('granted')->default(true)->comment('권한 부여 여부');
            $table->timestamps();

            // 그룹별 메뉴 중복 방지
            $table->unique(['group_id', 'menu_id'], 'unique_group_menu_permission');
            
            // 인덱스 추가
            $table->index(['group_id', 'granted']);
            $table->index('menu_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_menu_permissions');
    }
};

