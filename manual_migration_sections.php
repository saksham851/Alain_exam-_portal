<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Adding 'exam_standard_category_id' to 'sections'...\n";
if (Schema::hasTable('sections')) {
    if (!Schema::hasColumn('sections', 'exam_standard_category_id')) {
        Schema::table('sections', function (Blueprint $table) {
            $table->foreignId('exam_standard_category_id')->nullable()->after('exam_id')->constrained('exam_standard_categories')->onDelete('set null');
        });
        echo "Column added successfully.\n";
    } else {
        echo "Column already exists.\n";
    }
}
echo "Done.\n";
