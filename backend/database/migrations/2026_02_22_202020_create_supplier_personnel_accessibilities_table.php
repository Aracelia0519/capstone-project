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
        Schema::create('supplier_personnel_accessibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('supplier_personnels')->onDelete('cascade');
            $table->string('module_path');
            $table->string('module_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_personnel_accessibilities');
    }
};