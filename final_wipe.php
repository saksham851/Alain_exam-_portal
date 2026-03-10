<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exam;
use App\Models\Section;
use App\Models\CaseStudy;
use App\Models\Visit;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTag;
use App\Models\ExamStandard;
use App\Models\ScoreCategory;
use App\Models\ContentArea;
use Illuminate\Support\Facades\DB;

echo "Cleaning up all data for a fresh 0-question start...\n";

DB::statement('SET FOREIGN_KEY_CHECKS=0;');
QuestionTag::truncate();
QuestionOption::truncate();
Question::truncate();
Visit::truncate();
CaseStudy::truncate();
Section::truncate();
Exam::truncate();
ContentArea::truncate();
ScoreCategory::truncate();
ExamStandard::truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "Database is now completely empty.\n";
