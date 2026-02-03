<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_saved_colors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('hex_code');
            $table->string('rgb_values');
            $table->string('hsl_values');
            $table->integer('hue');
            $table->integer('saturation');
            $table->integer('lightness');
            $table->decimal('contrast_ratio', 4, 1);
            $table->integer('luminance');
            $table->string('temperature');
            $table->string('accessibility');
            $table->string('color_family');
            $table->string('stability');
            $table->string('frequency');
            $table->string('quantum_state');
            $table->json('source_colors')->nullable()->comment('JSON array of source colors with weights');
            $table->json('palettes')->nullable()->comment('JSON containing color palettes');
            $table->boolean('is_favorite')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            // Add indexes for better performance
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'is_favorite']);
            $table->index('hex_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_saved_colors');
    }
};