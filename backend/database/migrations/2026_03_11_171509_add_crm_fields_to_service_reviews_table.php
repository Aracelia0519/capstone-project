<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_reviews', function (Blueprint $table) {
            $table->text('reply')->nullable()->after('comment');
            $table->boolean('is_hidden')->default(false)->after('reply');
        });
    }

    public function down(): void
    {
        Schema::table('service_reviews', function (Blueprint $table) {
            $table->dropColumn(['reply', 'is_hidden']);
        });
    }
};