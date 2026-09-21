<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ────────────────────────────────────────────────────────────
        // Service-provider GROUPS (teams of service providers)
        // ────────────────────────────────────────────────────────────
        Schema::create('provider_groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('leader_id'); // creator / leader
            $table->enum('status', ['active', 'disbanded'])->default('active');
            $table->timestamps();

            $table->foreign('leader_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('provider_group_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('member_id');
            $table->enum('role', ['leader', 'member'])->default('member');
            $table->enum('status', ['pending', 'accepted', 'declined', 'left', 'removed'])->default('pending');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->foreign('group_id')->references('id')->on('provider_groups')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['group_id', 'member_id'], 'pg_members_group_member_uniq');
        });

        // ────────────────────────────────────────────────────────────
        // Group service-creation approval workflow
        // Every OTHER member must approve a draft before it is published
        // ────────────────────────────────────────────────────────────
        Schema::create('provider_group_service_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_offering_id');
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('member_id');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->foreign('service_offering_id')->references('id')->on('service_offerings')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('provider_groups')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['service_offering_id', 'member_id'], 'pg_svc_appr_member_uniq');
        });

        // ────────────────────────────────────────────────────────────
        // Revenue-split: every member proposes the % they will keep for
        // a NON-daily group job; the other members approve / reject.
        // ────────────────────────────────────────────────────────────
        Schema::create('provider_group_share_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('official_deal_id');
            $table->unsignedBigInteger('member_id');
            $table->decimal('percentage', 5, 2);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->foreign('group_id')->references('id')->on('provider_groups')->onDelete('cascade');
            $table->foreign('official_deal_id')->references('id')->on('official_deals')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('provider_group_share_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('share_request_id');
            $table->unsignedBigInteger('member_id');
            $table->enum('status', ['approved', 'rejected'])->default('approved');
            $table->text('reason')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->foreign('share_request_id')->references('id')->on('provider_group_share_requests')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['share_request_id', 'member_id'], 'pg_share_appr_member_uniq');
        });

        // Locked-in, final allocation once every member has an approved
        // proposal and the percentages total 100%.
        Schema::create('provider_group_deal_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('official_deal_id');
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('member_id');
            $table->decimal('percentage', 5, 2);
            $table->timestamps();

            $table->foreign('official_deal_id')->references('id')->on('official_deals')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('provider_groups')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['official_deal_id', 'member_id'], 'pg_alloc_deal_member_uniq');
        });

        // ────────────────────────────────────────────────────────────
        // One-at-a-time action locks.
        // When a member performs an action on a group job (approve the
        // request, create the deal, mark a work day, ...) the action is
        // locked so the other members see it disabled. Locks auto-expire
        // after 10 minutes.
        // ────────────────────────────────────────────────────────────
        Schema::create('provider_group_action_locks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->string('entity_type');     // client_service_request | official_deal | official_payment_term | service_offering
            $table->unsignedBigInteger('entity_id');
            $table->string('action');          // approve_request, create_deal, payment_term, propose_split, ...
            $table->unsignedBigInteger('member_id');
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();

            $table->foreign('group_id')->references('id')->on('provider_groups')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['entity_type', 'entity_id', 'action'], 'pg_lock_entity_action_uniq');
        });

        // ────────────────────────────────────────────────────────────
        // Alter existing tables to support group service offerings
        // ────────────────────────────────────────────────────────────
        Schema::table('service_offerings', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable()->after('provider_id');
            $table->boolean('is_published')->default(true)->after('is_active');

            $table->foreign('group_id')->references('id')->on('provider_groups')->onDelete('cascade');
            $table->index('group_id');
        });

        Schema::table('client_service_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable()->after('provider_id');
            $table->foreign('group_id')->references('id')->on('provider_groups')->onDelete('set null');
        });

        Schema::table('official_deals', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable()->after('provider_id');
            $table->foreign('group_id')->references('id')->on('provider_groups')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('official_deals', function (Blueprint $table) {
            Schema::hasColumn('official_deals', 'group_id') ? $table->dropForeign(['group_id']) : null;
            Schema::hasColumn('official_deals', 'group_id') ? $table->dropColumn('group_id') : null;
        });

        Schema::table('client_service_requests', function (Blueprint $table) {
            Schema::hasColumn('client_service_requests', 'group_id') ? $table->dropForeign(['group_id']) : null;
            Schema::hasColumn('client_service_requests', 'group_id') ? $table->dropColumn('group_id') : null;
        });

        Schema::table('service_offerings', function (Blueprint $table) {
            Schema::hasColumn('service_offerings', 'group_id') ? $table->dropIndex(['group_id']) : null;
            Schema::hasColumn('service_offerings', 'group_id') ? $table->dropForeign(['group_id']) : null;
            Schema::hasColumn('service_offerings', 'group_id') ? $table->dropColumn('group_id') : null;
            Schema::hasColumn('service_offerings', 'is_published') ? $table->dropColumn('is_published') : null;
        });

        Schema::dropIfExists('provider_group_action_locks');
        Schema::dropIfExists('provider_group_deal_allocations');
        Schema::dropIfExists('provider_group_share_approvals');
        Schema::dropIfExists('provider_group_share_requests');
        Schema::dropIfExists('provider_group_service_approvals');
        Schema::dropIfExists('provider_group_members');
        Schema::dropIfExists('provider_groups');
    }
};