<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            // Add service_provider_id as nullable (so client reviews don't break)
            $table->unsignedBigInteger('service_provider_id')->nullable()->after('client_id');
            
            // Note: If your original 'client_id' is NOT nullable, you should make it nullable now 
            // so Service Providers can leave reviews without a client_id.
            $table->unsignedBigInteger('client_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropColumn('service_provider_id');
        });
    }
};