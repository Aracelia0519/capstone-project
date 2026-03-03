<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('official_deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_service_request_id')->constrained('client_service_requests')->onDelete('cascade');
            $table->foreignId('service_offering_id')->nullable()->constrained('service_offerings')->onDelete('set null');
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            
            // Deal Specifics
            $table->decimal('price', 10, 2)->nullable();
            $table->date('preferred_date')->nullable();
            $table->string('time_preference')->nullable();
            $table->string('contact_number')->nullable();
            $table->text('address')->nullable();
            $table->text('description')->nullable();
            
            // Status tracks the negotiation state
            $table->enum('status', ['pending', 'ongoing', 'declined', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('official_deals');
    }
};