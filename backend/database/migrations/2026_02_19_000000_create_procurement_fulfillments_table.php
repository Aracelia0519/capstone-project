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
        // 1. Update the status enum in procurement_requests to include 'prepared'
        // Note: Changing enums in some DBs requires raw SQL. This is a generic approach.
        DB::statement("ALTER TABLE procurement_requests MODIFY COLUMN status ENUM('pending','approved','ready','processing','prepared','shipped','delivered','rejected','cancelled') NOT NULL DEFAULT 'pending'");

        // 2. Create the table for fulfillment details (proofs/receipts)
        Schema::create('procurement_fulfillments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('procurement_request_id');
            $table->string('receipt_file_path'); // Path to the uploaded receipt/invoice
            $table->string('proof_file_path');   // Path to the proof of preparation photo
            $table->text('notes')->nullable();   // Additional remarks
            $table->timestamp('prepared_at')->useCurrent();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('procurement_request_id')
                  ->references('id')
                  ->on('procurement_requests')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurement_fulfillments');
        
        // Revert status enum (optional, usually risky to remove enum values in down)
        // DB::statement("ALTER TABLE procurement_requests MODIFY COLUMN status ENUM('pending','approved','ready','processing','shipped','delivered','rejected','cancelled') NOT NULL DEFAULT 'pending'");
    }
};