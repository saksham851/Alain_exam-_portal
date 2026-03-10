<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$standard = App\Models\ExamStandard::with('categories.contentAreas')->find(1);
file_put_contents('standard_info.json', json_encode($standard, JSON_PRETTY_PRINT));
echo "Standard info saved to standard_info.json\n";
