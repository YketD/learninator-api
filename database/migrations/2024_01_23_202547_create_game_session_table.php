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
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('game_session_id')->unique();
            $table->boolean('is_complete')->default(false);
            $table->integer('score')->default(0);
            $table->integer('question_count')->default(0);
            $table->integer('correct_count')->default(0);
            $table->dateTime('start');
            $table->dateTime('end')->nullable();

            $table->timestamps();
        });

        Schema::create('game_session_question', function (Blueprint $table) {
            $table->foreignId('game_session_id')->constrained();
            $table->foreignId('question_id')->constrained();
            $table->primary(['game_session_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
        Schema::dropIfExists('game_session_question');
    }
};
