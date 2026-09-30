<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A pending admin review of one renewed tracked document.
 *
 * Why a table rather than two more columns on the requirements rows: the
 * requirements row is mutated in place by a renewal, so "the DTI Certificate"
 * stops referring to a fixed file the moment the user uploads a second one. A
 * review that stored no copy of what it reviewed would approve whichever file
 * happened to be current, not the one the admin actually looked at.
 *
 * The snapshot columns make the review a statement about a specific upload:
 * file_path and expiration_date are copied in at submission time and never
 * changed, so "approved" means this file, with this date, was checked.
 *
 * Polymorphic for the same reason related_documents is: the renewals view reads
 * both roles in one query.
 *
 * One row per document per submission, so the two permits can be decided
 * independently. Forcing an all-or-nothing decision on the pair would mean a
 * forged Mayor's Permit drags a perfectly good DTI Certificate down with it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('document_reviews')) {
            return;
        }

        Schema::create('document_reviews', function (Blueprint $table) {
            $table->id();

            // Which requirement set this hangs off: SupplierRequirements or
            // DistributorRequirements. No FK is possible across two parents.
            $table->string('reviewable_type');
            $table->unsignedBigInteger('reviewable_id');

            // 'dti_certificate' or 'mayor_permit' -- DocumentExpiry's vocabulary,
            // so the label is never spelled twice.
            $table->string('document_key');

            // The snapshot under review. See the class docblock.
            $table->string('file_path');
            $table->date('expiration_date');

            // What this submission replaced, and the date an admin had last
            // accepted. Null on the first renewal of a document.
            //
            // This is what makes a rejection safe. The requirements row is already
            // overwritten by the time anyone reviews it, so a rejected renewal
            // would otherwise leave the rejected date live -- resetting the expiry
            // clock with a document the admin just called a forgery, and opening
            // the door to a revoked termination. Falling back to the last accepted
            // date is the honest reading of "we do not accept this renewal".
            $table->string('previous_file_path')->nullable();
            $table->date('previous_expiration_date')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected', 'superseded'])
                ->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();

            $table->timestamps();

            $table->index(['reviewable_type', 'reviewable_id'], 'document_reviews_owner_index');
            $table->index('status', 'document_reviews_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_reviews');
    }
};
