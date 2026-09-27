<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add manual-review support to identity verification results.
     *
     * When at least one of the automatic checks (face / name / ID number)
     * matched, the user may request a manual review by the admin. The admin's
     * manual decision is recorded here so the User Management screen can show
     * the review state and its outcome.
     */
    public function up()
    {
        Schema::table('identity_verification_results', function (Blueprint $table) {
            $table->boolean('manual_review_requested')->default(false)->after('extracted_id_number');
            $table->string('manual_review_status')->nullable()->after('manual_review_requested');
            $table->text('manual_review_reason')->nullable()->after('manual_review_status');
            $table->unsignedBigInteger('manual_reviewed_by')->nullable()->after('manual_review_reason');
            $table->timestamp('manual_reviewed_at')->nullable()->after('manual_reviewed_by');
        });
    }

    public function down()
    {
        Schema::table('identity_verification_results', function (Blueprint $table) {
            $table->dropColumn([
                'manual_review_requested',
                'manual_review_status',
                'manual_review_reason',
                'manual_reviewed_by',
                'manual_reviewed_at',
            ]);
        });
    }
};