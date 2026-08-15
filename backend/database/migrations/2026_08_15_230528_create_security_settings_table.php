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
        Schema::create('security_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('full_name')->nullable();
            $table->string('role')->nullable();
            
            // Security settings boolean toggles (is enable or disable)
            $table->boolean('email_login_alerts')->default(false);
            $table->boolean('one_device_login')->default(false);
            $table->boolean('session_timeout')->default(false);
            $table->boolean('remember_this_device')->default(false);
            $table->boolean('account_recovery_email')->default(false);
            $table->boolean('security_questions')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_settings');
    }
};