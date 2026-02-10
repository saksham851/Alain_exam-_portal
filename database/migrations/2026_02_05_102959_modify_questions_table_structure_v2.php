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
        Schema::table('questions', function (Blueprint $table) {
            // Remove old dual-category columns and weights
            $table->dropForeign(['content_area_1_id']);
            $table->dropForeign(['content_area_2_id']);
            $table->dropColumn(['content_area_1_id', 'content_area_2_id', 'ig_weight', 'dm_weight']);

            // Add single content area column
            $table->foreignId('content_area_id')->nullable()->constrained('exam_standard_content_areas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('content_area_1_id')->nullable()->constrained('exam_standard_content_areas')->nullOnDelete();
            $table->foreignId('content_area_2_id')->nullable()->constrained('exam_standard_content_areas')->nullOnDelete();
            $table->integer('ig_weight')->default(0);
            $table->integer('dm_weight')->default(0);
            
            $table->dropForeign(['content_area_id']);
            $table->dropColumn('content_area_id');
        });
    }
};
