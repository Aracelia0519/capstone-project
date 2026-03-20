<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->string('plan_name');
            $table->json('features')->nullable();
            $table->decimal('amount', 8, 2)->default(0);
            $table->string('status')->default('pending'); // pending, active, expired
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('reference_number')->unique();
            $table->string('paymongo_session_id')->nullable();
            $table->string('admin_receiving_number')->default('09922617057'); // Saved as requested
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_subscriptions');
    }
};