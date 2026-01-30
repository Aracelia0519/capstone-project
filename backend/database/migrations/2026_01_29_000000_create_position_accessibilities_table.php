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
        Schema::create('position_accessibilities', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('position_id')->unsigned();
            $table->string('permission_key'); // e.g., 'dashboard', 'employee_list'
            $table->string('permission_label'); // e.g., 'Dashboard', 'Employee List'
            $table->boolean('is_granted')->default(true);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('position_id')
                  ->references('id')
                  ->on('positions')
                  ->onDelete('cascade');

            // Indexes for better performance
            $table->index(['position_id', 'permission_key']);
            $table->index('permission_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('position_accessibilities');
    }
};