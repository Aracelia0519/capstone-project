<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('service_request_id')->nullable()->constrained('client_service_requests')->onDelete('set null');
            
            $table->text('message')->nullable();
            // Enum to handle standard text and our structured quick-chats
            $table->enum('type', ['text', 'request_summary', 'official_deal'])->default('text');
            $table->json('payload')->nullable(); // Stores form data for official deals/summaries
            
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sp_messages');
    }
};