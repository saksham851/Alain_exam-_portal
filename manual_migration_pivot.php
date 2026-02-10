<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Running manual schema update for Question Content Areas Pivot...\n";

// 1. Create table if not exists
if (!Schema::hasTable('question_content_area')) {
    echo "Creating 'question_content_area' table...\n";
    Schema::create('question_content_area', function (Blueprint $table) {
        $table->id();
        $table->foreignId('question_id')->constrained()->onDelete('cascade');
        $table->foreignId('content_area_id')->constrained('exam_standard_content_areas')->onDelete('cascade');
        $table->timestamps();

        $table->unique(['question_id', 'content_area_id'], 'q_content_area_unique');
    });
    echo "Table created.\n";
} else {
    echo "Table 'question_content_area' already exists.\n";
}

// 2. Make content_area_id in questions table nullable (legacy)
if (Schema::hasColumn('questions', 'content_area_id')) {
    Schema::table('questions', function (Blueprint $table) {
        $table->foreignId('content_area_id')->nullable()->change();
    });
    echo "Made 'content_area_id' nullable in 'questions' table.\n";
}

echo "Schema update completed successfully.\n";
