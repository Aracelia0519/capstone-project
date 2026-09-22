<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_verification_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role')->nullable();
            $table->string('requirement_type')->nullable();
            $table->unsignedBigInteger('requirement_id')->nullable();
            $table->string('selfie_photo')->nullable();
            $table->boolean('face_detected')->default(false);
            $table->boolean('face_match')->default(false);
            $table->decimal('face_similarity', 6, 4)->nullable();
            $table->boolean('name_match')->default(false);
            $table->boolean('id_number_match')->default(false);
            $table->boolean('credentials_matched')->default(false);
            $table->text('failure_reason')->nullable();
            $table->longText('extracted_text')->nullable();
            $table->string('extracted_id_number')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'requirement_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_verification_results');
    }
};