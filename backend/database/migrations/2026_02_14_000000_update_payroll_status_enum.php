<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Modify the status column to include 'rejected'
        // Note: DB::statement is used because modifying enums in Laravel depends on the driver
        DB::statement("ALTER TABLE payrolls MODIFY COLUMN status ENUM('pending', 'approved', 'paid', 'cancelled', 'rejected') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        // Revert back to original
        DB::statement("ALTER TABLE payrolls MODIFY COLUMN status ENUM('pending', 'approved', 'paid', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};