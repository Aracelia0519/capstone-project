<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Documents a supplier or distributor attaches beyond the fixed set, each with a
 * user-supplied name.
 *
 * Polymorphic rather than one table per role: the admin renewals view has to read
 * "all extra documents for this account" across both roles in a single query, and
 * a per-role table would force that to become a union of two unrelated shapes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('related_documents')) {
            return;
        }

        Schema::create('related_documents', function (Blueprint $table) {
            $table->id();

            // Which requirement set this hangs off. Stored as the model class name
            // by Eloquent's morphTo, so no FK is possible across two parents.
            $table->string('documentable_type');
            $table->unsignedBigInteger('documentable_id');

            $table->string('document_name');
            $table->string('file_path');

            // Optional: most extra paperwork (SEC registration, lease, ...)
            // expires, but not all of it does, and requiring a date for a document
            // that has none would be a lie.
            $table->date('expiration_date')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();

            $table->timestamps();

            $table->index(['documentable_type', 'documentable_id'], 'related_documents_owner_index');
            $table->index('status', 'related_documents_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('related_documents');
    }
};
