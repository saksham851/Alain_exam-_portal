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
        // Disable foreign key checks to allow dropping columns even with constraints (sometimes needed)
        Schema::disableForeignKeyConstraints();

        try {
            // Drop sub_case_id FK and Column
            try {
                // Try dropping by standard name
                Schema::table('questions', function (Blueprint $table) {
                    $table->dropForeign(['sub_case_id']);
                });
            } catch (\Exception $e) {
                // Ignore if not found
            }
            
            if (Schema::hasColumn('questions', 'sub_case_id')) {
                 Schema::table('questions', function (Blueprint $table) {
                     $table->dropColumn('sub_case_id');
                 });
            }

            // Drop case_study_id FK and Column
            try {
                Schema::table('questions', function (Blueprint $table) {
                    $table->dropForeign(['case_study_id']);
                });
            } catch (\Exception $e) {
                // Ignore
            }

            if (Schema::hasColumn('questions', 'case_study_id')) {
                 Schema::table('questions', function (Blueprint $table) {
                     $table->dropColumn('case_study_id');
                 });
            }

        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not reversible easily without knowing constraints.
        // We assume we want these gone permanently.
    }
};
