<?php

use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking questions table...\n";
$hasC1 = Schema::hasColumn('questions', 'content_area_1_id');
$hasC2 = Schema::hasColumn('questions', 'content_area_2_id');

echo "content_area_1_id: " . ($hasC1 ? "YES" : "NO") . "\n";
echo "content_area_2_id: " . ($hasC2 ? "YES" : "NO") . "\n";
