<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('status');
            $table->string('payment_reference')->nullable()->after('payment_method'); // For bank transfer codes
            $table->text('admin_notes')->nullable()->after('payment_reference');
            $table->timestamp('paid_at')->nullable()->after('admin_notes');
        });
    }

    public function down()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_reference', 'admin_notes', 'paid_at']);
        });
    }
};