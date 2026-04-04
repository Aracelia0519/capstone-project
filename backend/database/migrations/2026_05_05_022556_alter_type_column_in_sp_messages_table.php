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
        // Safely converts the column to a standard String (VARCHAR)
        DB::statement("ALTER TABLE sp_messages MODIFY type VARCHAR(255) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // If you ever need to rollback, you can specify the original ENUM values here.
        // Leaving it blank or commented out is fine if you intend to keep it as VARCHAR permanently.
        
        // DB::statement("ALTER TABLE sp_messages MODIFY type ENUM('text', 'official_deal', 'payment_term', 'request_summary') NOT NULL");
    }
};