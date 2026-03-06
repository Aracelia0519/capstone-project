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
        Schema::table('position_accessibilities', function (Blueprint $table) {
            $table->boolean('can_view')->default(false)->after('is_granted');
            $table->boolean('can_create')->default(false)->after('can_view');
            $table->boolean('can_update')->default(false)->after('can_create');
            $table->boolean('can_delete')->default(false)->after('can_update');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('position_accessibilities', function (Blueprint $table) {
            $table->dropColumn(['can_view', 'can_create', 'can_update', 'can_delete']);
        });
    }
};