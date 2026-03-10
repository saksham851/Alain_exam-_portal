<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exam;
use App\Models\Question;

echo "Total Questions in DB: " . Question::count() . "\n";
echo "Exams:\n";
foreach (Exam::all() as $e) {
    try {
        $qCount = $e->getAllQuestions()->count();
        echo "ID: {$e->id} | Name: {$e->name} | QCount: {$qCount}\n";
    } catch (\Error $err) {
        echo "ID: {$e->id} | Name: {$e->name} | Error: " . $err->getMessage() . "\n";
    }
}
