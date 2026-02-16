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
        // We use DB::statement because standard Schema builder has limitations with changing ENUM columns directly
        DB::statement("ALTER TABLE employee_attendances MODIFY COLUMN status ENUM('Present','Late','Absent','Half Day','Leave') NOT NULL DEFAULT 'Present'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the original ENUM list
        // WARNING: This will fail if you have rows with 'Leave' status. You might want to delete them or change them first.
        DB::statement("ALTER TABLE employee_attendances MODIFY COLUMN status ENUM('Present','Late','Absent','Half Day') NOT NULL DEFAULT 'Present'");
    }
};