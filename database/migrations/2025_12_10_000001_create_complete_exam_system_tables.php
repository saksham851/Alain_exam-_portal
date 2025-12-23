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
        // Drop all existing tables in reverse dependency order
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('attempt_answers');
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('student_exams');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('sub_case_studies');
        Schema::dropIfExists('case_studies');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('users');

        // 1) USERS TABLE (Admins + Students)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('role', ['admin', 'student'])->default('student');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 150)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->tinyInteger('is_blocked')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->rememberToken();
            $table->timestamps();
            
            $table->index('role');
        });

        // Password Reset Tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 2) EXAMS TABLE
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('duration_minutes')->default(180);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            
            $table->index('status');
        });

        // 3) CASE STUDIES (11 per exam)
        Schema::create('case_studies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->string('title');
            $table->longText('content')->nullable();
            $table->integer('order_no');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            
            $table->index('exam_id');
        });

        // 4) SUB CASE STUDIES (3 per case)
        Schema::create('sub_case_studies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_study_id')->constrained('case_studies')->onDelete('cascade');
            $table->string('title');
            $table->longText('content')->nullable();
            $table->integer('order_no');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            
            $table->index('case_study_id');
        });

        // 5) QUESTIONS TABLE (Updated for multiple correct answers & unlimited options)
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_case_id')->constrained('sub_case_studies')->onDelete('cascade');
            $table->text('question_text');
            $table->enum('question_type', ['single', 'multiple'])->default('single');
            $table->float('ig_weight')->default(0);
            $table->float('dm_weight')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            
            $table->index('sub_case_id');
        });

        // 6) QUESTION OPTIONS (Unlimited options, multiple correct)
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->char('option_key', 1); // A, B, C, D, E, F...
            $table->text('option_text');
            $table->tinyInteger('is_correct')->default(0);
            $table->timestamps();
            
            $table->index('question_id');
        });

        // 7) STUDENT EXAMS (Purchased exam access)
        Schema::create('student_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->date('expiry_date');
            $table->integer('attempts_allowed')->default(3);
            $table->integer('attempts_used')->default(0);
            $table->string('source', 50)->default('GHL');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            
            $table->index(['student_id', 'exam_id']);
        });

        // 8) EXAM ATTEMPTS (Each attempt by student)
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_exam_id')->constrained('student_exams')->onDelete('cascade');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'timeout', 'forfeit'])->default('in_progress');
            $table->integer('time_remaining')->default(0);
            $table->float('ig_score')->default(0);
            $table->float('dm_score')->default(0);
            $table->float('total_score')->default(0);
            $table->tinyInteger('is_passed')->default(0);
            $table->integer('tab_switch_count')->default(0);
            $table->timestamps();
            
            $table->index('student_exam_id');
        });

        // 9) ATTEMPT ANSWERS (Supports multiple selected options)
        Schema::create('attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('exam_attempts')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->json('selected_options')->nullable(); // example: ["A","C","E"]
            $table->tinyInteger('is_correct')->default(0);
            $table->float('ig_score')->default(0);
            $table->float('dm_score')->default(0);
            $table->json('autosave_snapshot')->nullable();
            $table->timestamps();
            
            $table->index(['attempt_id', 'question_id']);
        });

        // 10) WEBHOOK LOGS
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->longText('payload')->nullable();
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('attempt_answers');
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('student_exams');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('sub_case_studies');
        Schema::dropIfExists('case_studies');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
