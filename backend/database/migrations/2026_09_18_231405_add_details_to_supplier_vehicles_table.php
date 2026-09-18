<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('supplier_vehicles', function (Blueprint $table) {
            $table->string('make')->nullable()->after('plate_number');
            $table->string('vin')->nullable()->after('make');
            $table->string('mv_file_number', 15)->nullable()->after('vin');
            $table->string('chassis_number', 17)->nullable()->after('mv_file_number');
            $table->string('color')->nullable()->after('chassis_number');
            $table->enum('fuel_type', ['Gasoline', 'Diesel', 'Electric', 'Hybrid'])->nullable()->after('color');
            $table->string('cr_file_path')->nullable()->after('status');
            $table->string('or_file_path')->nullable()->after('cr_file_path');
            $table->string('proof_of_ownership_path')->nullable()->after('or_file_path');
        });

        // Fill up newly added columns for the existing vehicles in the database
        DB::table('supplier_vehicles')->update([
            'make' => 'Toyota', // Fallback default
            'vin' => 'N/A',
            'mv_file_number' => '000000000000000',
            'chassis_number' => '00000000000000000',
            'color' => 'White',
            'fuel_type' => 'Gasoline',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'make',
                'vin',
                'mv_file_number',
                'chassis_number',
                'color',
                'fuel_type',
                'cr_file_path',
                'or_file_path',
                'proof_of_ownership_path'
            ]);
        });
    }
};