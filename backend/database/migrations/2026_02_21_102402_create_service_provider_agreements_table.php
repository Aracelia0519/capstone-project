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
        Schema::create('service_provider_agreements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_provider_distributor_id');
            $table->string('document_path');
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();

            // Foreign Key constraint
            $table->foreign('service_provider_distributor_id', 'sp_dist_agreement_fk')
                  ->references('id')
                  ->on('service_provider_distributors')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_provider_agreements');
    }
};