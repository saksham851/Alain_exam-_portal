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
            $table->string('certification_type')->nullable()->after('category_id');
        });

        Schema::table('exam_categories', function (Blueprint $table) {
            $table->dropColumn('certification_type');
        });
    }

    public function down(): void
    {
        Schema::table('exam_categories', function (Blueprint $table) {
            $table->string('certification_type')->nullable();
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('certification_type');
        });
    }
};
