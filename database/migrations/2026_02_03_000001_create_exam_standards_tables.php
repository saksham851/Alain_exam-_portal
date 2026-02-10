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
        // 1. EXAM STANDARDS TABLE
        Schema::create('exam_standards', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "NCMHCE 2026"
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. EXAM STANDARD CATEGORIES (Section Category 1 & 2)
        Schema::create('exam_standard_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_standard_id')->constrained('exam_standards')->onDelete('cascade');
            $table->string('name'); // e.g., "Counselor Work Behavior Areas"
            $table->tinyInteger('category_number'); // 1 or 2
            $table->timestamps();
            
            $table->index(['exam_standard_id', 'category_number']);
        });

        // 3. EXAM STANDARD CONTENT AREAS
        Schema::create('exam_standard_content_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('exam_standard_categories')->onDelete('cascade');
            $table->string('name'); // e.g., "Professional Practice and Ethics"
            $table->integer('percentage'); // e.g., 15
            $table->integer('order_no')->default(0);
            $table->timestamps();
            
            $table->index('category_id');
        });

        // 4. ADD COLUMNS TO EXAMS TABLE
        Schema::table('exams', function (Blueprint $table) {
            $table->foreignId('exam_standard_id')->nullable()->after('category_id')->constrained('exam_standards')->onDelete('set null');
            $table->integer('passing_score_overall')->nullable()->default(65);
            $table->integer('passing_score_category_1')->nullable()->default(65);
            $table->integer('passing_score_category_2')->nullable()->default(65);
        });

        // 5. ADD COLUMNS TO QUESTIONS TABLE
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('content_area_1_id')->nullable()->after('dm_weight')->constrained('exam_standard_content_areas')->onDelete('set null');
            $table->foreignId('content_area_2_id')->nullable()->after('content_area_1_id')->constrained('exam_standard_content_areas')->onDelete('set null');
        });

        // 6. ATTEMPT SCORE REPORTS TABLE
        Schema::create('attempt_score_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('exam_attempts')->onDelete('cascade');
            $table->decimal('overall_score', 5, 2)->default(0);
            $table->decimal('category_1_overall_score', 5, 2)->nullable();
            $table->decimal('category_2_overall_score', 5, 2)->nullable();
            $table->json('category_1_breakdown')->nullable();
            $table->json('category_2_breakdown')->nullable();
            $table->boolean('passed')->default(false);
            $table->string('pdf_path')->nullable();
            $table->timestamps();
            
            $table->index('attempt_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attempt_score_reports');
        
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['content_area_1_id']);
            $table->dropForeign(['content_area_2_id']);
            $table->dropColumn(['content_area_1_id', 'content_area_2_id']);
        });
        
        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign(['exam_standard_id']);
            $table->dropColumn(['exam_standard_id', 'passing_score_overall', 'passing_score_category_1', 'passing_score_category_2']);
        });
        
        Schema::dropIfExists('exam_standard_content_areas');
        Schema::dropIfExists('exam_standard_categories');
        Schema::dropIfExists('exam_standards');
    }
};
