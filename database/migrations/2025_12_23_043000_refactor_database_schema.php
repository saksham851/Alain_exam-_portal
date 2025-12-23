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
        // 1. Rename Tables
        // case_studies -> sections
        if (Schema::hasTable('case_studies')) {
            Schema::rename('case_studies', 'sections');
        }
        
        // sub_case_studies -> case_studies
        // WARNING: We must do this after renaming the original 'case_studies' to avoid collision
        if (Schema::hasTable('sub_case_studies')) {
            Schema::rename('sub_case_studies', 'case_studies');
        }

        // 2. Rename Columns / Fix Foreign Keys
        // 'sections' table (formerly case_studies) is fine (exam_id is correct)

        // 'case_studies' table (formerly sub_case_studies)
        // Has 'case_study_id' which pointed to parent.
        // We need to rename 'case_study_id' -> 'section_id'
        Schema::table('case_studies', function (Blueprint $table) {
            // Drop FK first if it exists (usually strictly named, but let's try standard Laravel convention removal)
            // Or just renaming the column might work if DB supports it. 
            // Better to be explicit.
            // Note: In SQLite/MySQL renaming column with FK might require dropping FK.
            // Assuming standard Laravel naming: sub_case_studies_case_study_id_foreign
            
            // To be safe against unknown FK names, we just rename column. 
            // Modern Laravel/MySQL 8+ handles renameColumn well usually.
            $table->renameColumn('case_study_id', 'section_id');
        });

        // 'questions' table
        // Has 'sub_case_id'. Needs to be 'case_study_id'.
        Schema::table('questions', function (Blueprint $table) {
             $table->renameColumn('sub_case_id', 'case_study_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
             $table->renameColumn('case_study_id', 'sub_case_id');
        });

        Schema::table('case_studies', function (Blueprint $table) {
            $table->renameColumn('section_id', 'case_study_id');
        });

        if (Schema::hasTable('case_studies')) {
            Schema::rename('case_studies', 'sub_case_studies');
        }

        if (Schema::hasTable('sections')) {
            Schema::rename('sections', 'case_studies');
        }
    }
};
