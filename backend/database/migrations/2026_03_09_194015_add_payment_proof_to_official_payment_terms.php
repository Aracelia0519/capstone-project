<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Add new statuses for payment processing
        DB::statement("ALTER TABLE official_payment_terms MODIFY COLUMN status ENUM('pending', 'agreed', 'declined', 'awaiting_proof_approval', 'paid') NOT NULL DEFAULT 'pending'");
        
        Schema::table('official_payment_terms', function (Blueprint $table) {
            $table->string('proof_of_payment')->nullable()->after('payment_term');
        });
    }

    public function down()
    {
        Schema::table('official_payment_terms', function (Blueprint $table) {
            $table->dropColumn('proof_of_payment');
        });
        
        DB::statement("ALTER TABLE official_payment_terms MODIFY COLUMN status ENUM('pending', 'agreed', 'declined') NOT NULL DEFAULT 'pending'");
    }
};