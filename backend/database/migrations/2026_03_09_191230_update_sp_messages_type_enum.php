<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Update the enum to include 'payment_term'
        DB::statement("ALTER TABLE sp_messages MODIFY COLUMN type ENUM('text', 'request_summary', 'official_deal', 'payment_term') NOT NULL DEFAULT 'text'");
    }

    public function down()
    {
        // Revert back (Optional, but might cause data loss if there are payment_term records)
        DB::statement("ALTER TABLE sp_messages MODIFY COLUMN type ENUM('text', 'request_summary', 'official_deal') NOT NULL DEFAULT 'text'");
    }
};