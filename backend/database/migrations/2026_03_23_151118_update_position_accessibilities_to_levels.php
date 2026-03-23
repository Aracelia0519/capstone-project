<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add new level columns
        Schema::table('position_accessibilities', function (Blueprint $table) {
            $table->boolean('can_manage')->default(0)->after('can_view');
            $table->boolean('can_approve')->default(0)->after('can_manage');
        });

        // 2. Safely migrate existing data (If they could do anything, they get 'manage' level)
        DB::statement('
            UPDATE position_accessibilities 
            SET can_manage = 1 
            WHERE can_create = 1 OR can_update = 1 OR can_delete = 1
        ');

        // 3. Drop old CRUD columns
        Schema::table('position_accessibilities', function (Blueprint $table) {
            $table->dropColumn(['can_create', 'can_update', 'can_delete']);
        });
    }

    public function down(): void
    {
        // Reverse process
        Schema::table('position_accessibilities', function (Blueprint $table) {
            $table->boolean('can_create')->default(0)->after('can_view');
            $table->boolean('can_update')->default(0)->after('can_create');
            $table->boolean('can_delete')->default(0)->after('can_update');
        });

        DB::statement('
            UPDATE position_accessibilities 
            SET can_create = can_manage, can_update = can_manage, can_delete = can_manage
        ');

        Schema::table('position_accessibilities', function (Blueprint $table) {
            $table->dropColumn(['can_manage', 'can_approve']);
        });
    }
};