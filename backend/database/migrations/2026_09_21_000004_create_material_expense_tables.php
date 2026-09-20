<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Materials reimbursement:
     *  - material_expense_requests  = one "batch" of materials the Service Provider
     *    bought with his/her own money, with one proof photo (receipt) and a
     *    status (pending -> approved/rejected by the client).
     *  - material_expense_items     = the line items inside each request
     *    (item name, quantity, unit price, computed total).
     *  - official_payment_terms     = gains is_materials_term + amount so the
     *    approved materials total can be billed via the same payment flow
     *    (gcash or on_hand) as the main deal.
     */
    public function up(): void
    {
        Schema::create('material_expense_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_service_request_id')->constrained('client_service_requests')->onDelete('cascade');
            $table->unsignedBigInteger('provider_id');
            $table->string('proof_photo_path')->nullable();
            $table->string('status')->default('pending'); // pending | approved | rejected
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('provider_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('material_expense_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_expense_request_id')->constrained('material_expense_requests')->onDelete('cascade');
            $table->string('item_name');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2); // quantity * unit_price
            $table->timestamps();
        });

        Schema::table('official_payment_terms', function (Blueprint $table) {
            $table->boolean('is_materials_term')->default(false);
            $table->decimal('amount', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('official_payment_terms', function (Blueprint $table) {
            $table->dropColumn(['is_materials_term', 'amount']);
        });
        Schema::dropIfExists('material_expense_items');
        Schema::dropIfExists('material_expense_requests');
    }
};