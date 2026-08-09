<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technical_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role');
            $table->enum('category', [
                'bug', 'system_error', 'login_issue', 'payment_issue', 'order_issue', 
                'inventory_issue', 'performance_issue', 'display_issue', 'security_issue', 'other'
            ]);
            $table->string('page');
            $table->string('device');
            $table->string('browser');
            $table->text('error_message');
            $table->string('attachment')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending', 'reviewed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technical_reports');
    }
};