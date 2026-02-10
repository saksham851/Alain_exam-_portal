<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

$tables = ['sections', 'questions', 'exam_standard_categories', 'exam_standard_content_areas'];
foreach ($tables as $table) {
    echo "Table: $table\n";
    if (Schema::hasTable($table)) {
        print_r(Schema::getColumnListing($table));
    } else {
        echo "Does not exist.\n";
    }
    echo "--------------------\n";
}
