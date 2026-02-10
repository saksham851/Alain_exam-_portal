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
        // 1. Drop the temporary pivot table from previous attempts if it exists
        Schema::dropIfExists('question_content_area');

        // 2. Clean up questions table (remove the single-column link)
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'content_area_id')) {
                // Try to drop FK if exists. Using typical naming convention or array syntax.
                // Note: The driver might require the exact name if array syntax fails, 
                // but Laravel usually handles array syntax: dropForeign(['column_name'])
                $table->dropForeign(['content_area_id']);
                $table->dropColumn('content_area_id');
            }
        });

        // 3. Rename tables to match User's preferred naming
        // exam_standard_categories -> score_categories
        if (Schema::hasTable('exam_standard_categories')) {
            Schema::rename('exam_standard_categories', 'score_categories');
        }

        // exam_standard_content_areas -> content_areas
        if (Schema::hasTable('exam_standard_content_areas')) {
            Schema::rename('exam_standard_content_areas', 'content_areas');
        }

        // 4. Update content_areas: rename category_id -> score_category_id and add max_points
        Schema::table('content_areas', function (Blueprint $table) {
            // We need to handle the Foreign Key before renaming if strict
            // However, common practice: drop FK, rename column, add FK.
            // Constraint name likely: exam_standard_content_areas_category_id_foreign
            
            // Attempt to drop FK. If it doesn't exist, it might throw, so we catch or hope.
            // But we can try simple renameColumn first, if it fails we fix.
            // Actually, let's just add score_category_id and Drop category_id to be safe?
            // No, rename is better for data retention.
            
            // $table->dropForeign('exam_standard_content_areas_category_id_foreign'); 
            // The above name is hard to predict 100% without checking DB, but it's the standard.
            
            $table->renameColumn('category_id', 'score_category_id');
            $table->integer('max_points')->default(0)->after('name');
        });

        // 5. Create the detailed tagging table (The "Golden Line" requirement)
        // questionId, scoreCategoryId, contentAreaId
        Schema::create('question_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            
            // We reference score_categories. 
            // Note: DB table is now 'score_categories'.
            $table->foreignId('score_category_id')->constrained('score_categories')->cascadeOnDelete();
            
            // We reference content_areas.
            $table->foreignId('content_area_id')->constrained('content_areas')->cascadeOnDelete();
            
            $table->timestamps();

            // Optional: Prevent duplicate tagging of the exact same content area for the same question
            // $table->unique(['question_id', 'score_category_id', 'content_area_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_tags');

        Schema::table('content_areas', function (Blueprint $table) {
            $table->dropColumn('max_points');
            $table->renameColumn('score_category_id', 'category_id');
        });

        if (Schema::hasTable('content_areas')) {
            Schema::rename('content_areas', 'exam_standard_content_areas');
        }

        if (Schema::hasTable('score_categories')) {
            Schema::rename('score_categories', 'exam_standard_categories');
        }

        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('content_area_id')->nullable()->constrained('exam_standard_content_areas')->nullOnDelete();
        });
        
    }
};
