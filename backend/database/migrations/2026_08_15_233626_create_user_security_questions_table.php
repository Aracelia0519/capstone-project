<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_security_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('question_1');
            $table->string('answer_1');
            $table->string('question_2');
            $table->string('answer_2');
            $table->string('question_3');
            $table->string('answer_3');
            $table->string('question_4');
            $table->string('answer_4');
            $table->string('question_5');
            $table->string('answer_5');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_security_questions');
    }
};