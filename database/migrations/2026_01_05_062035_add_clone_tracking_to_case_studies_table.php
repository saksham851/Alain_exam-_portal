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
        Schema::table('case_studies', function (Blueprint $table) {
            $table->unsignedBigInteger('cloned_from_id')->nullable()->after('status');
            $table->unsignedBigInteger('cloned_from_section_id')->nullable()->after('cloned_from_id');
            $table->timestamp('cloned_at')->nullable()->after('cloned_from_section_id');
            
            // Add foreign key constraints
            $table->foreign('cloned_from_id')->references('id')->on('case_studies')->onDelete('set null');
            $table->foreign('cloned_from_section_id')->references('id')->on('sections')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_studies', function (Blueprint $table) {
            $table->dropForeign(['cloned_from_id']);
            $table->dropForeign(['cloned_from_section_id']);
            $table->dropColumn(['cloned_from_id', 'cloned_from_section_id', 'cloned_at']);
        });
    }
};
