<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('group_menu_permissions')) {
            Schema::rename('group_menu_permissions', 'admin_group_menu_permissions');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('admin_group_menu_permissions')) {
            Schema::rename('admin_group_menu_permissions', 'group_menu_permissions');
        }
    }
};
