<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking 'exams' table for 'total_questions' column...\n";

if (Schema::hasTable('exams')) {
    if (!Schema::hasColumn('exams', 'total_questions')) {
        echo "Column 'total_questions' missing. Adding it...\n";
        Schema::table('exams', function (Blueprint $table) {
            $table->integer('total_questions')->nullable()->after('exam_standard_id');
        });
        echo "Column added successfully.\n";
    } else {
        echo "Column 'total_questions' already exists.\n";
    }
} else {
    echo "Table 'exams' does not exist!\n";
}

echo "Done.\n";
