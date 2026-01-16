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
        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasColumn('exams', 'cloned_from_id')) {
                $table->unsignedBigInteger('cloned_from_id')->nullable()->after('id');
            }
        });

        Schema::table('sections', function (Blueprint $table) {
            if (!Schema::hasColumn('sections', 'cloned_from_id')) {
                $table->unsignedBigInteger('cloned_from_id')->nullable()->after('id');
            }
        });

        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'cloned_from_id')) {
                $table->unsignedBigInteger('cloned_from_id')->nullable()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('cloned_from_id');
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn('cloned_from_id');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('cloned_from_id');
        });
    }
};
