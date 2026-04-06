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
        // Update Distributor Payment Settings Table
        Schema::table('distributor_payment_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('distributor_payment_settings', 'is_bank_enabled')) {
                $table->boolean('is_bank_enabled')->default(false);
            }
            if (!Schema::hasColumn('distributor_payment_settings', 'bank_name')) {
                $table->string('bank_name')->nullable();
            }
            if (!Schema::hasColumn('distributor_payment_settings', 'bank_account_name')) {
                $table->string('bank_account_name')->nullable();
            }
            if (!Schema::hasColumn('distributor_payment_settings', 'bank_account_number')) {
                $table->string('bank_account_number')->nullable();
            }
        });

        // Update Supplier Payment Settings Table
        Schema::table('supplier_payment_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('supplier_payment_settings', 'is_bank_enabled')) {
                $table->boolean('is_bank_enabled')->default(false);
            }
            if (!Schema::hasColumn('supplier_payment_settings', 'bank_name')) {
                $table->string('bank_name')->nullable();
            }
            if (!Schema::hasColumn('supplier_payment_settings', 'bank_account_name')) {
                $table->string('bank_account_name')->nullable();
            }
            if (!Schema::hasColumn('supplier_payment_settings', 'bank_account_number')) {
                $table->string('bank_account_number')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributor_payment_settings', function (Blueprint $table) {
            $table->dropColumn(['is_bank_enabled', 'bank_name', 'bank_account_name', 'bank_account_number']);
        });

        Schema::table('supplier_payment_settings', function (Blueprint $table) {
            $table->dropColumn(['is_bank_enabled', 'bank_name', 'bank_account_name', 'bank_account_number']);
        });
    }
};