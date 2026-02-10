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
        Schema::create('exam_category_passing_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_standard_category_id')->constrained('exam_standard_categories')->onDelete('cascade');
            $table->integer('passing_score')->default(65);
            $table->timestamps();

            $table->unique(['exam_id', 'exam_standard_category_id'], 'exam_cat_score_unique');
        });

        // Remove hardcoded columns from exams table
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['passing_score_category_1', 'passing_score_category_2']);
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->integer('passing_score_category_1')->nullable()->default(65);
            $table->integer('passing_score_category_2')->nullable()->default(65);
        });

        Schema::dropIfExists('exam_category_passing_scores');
    }
};
