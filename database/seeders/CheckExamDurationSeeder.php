<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CheckExamDurationSeeder extends Seeder
{
    public function run(): void
    {
        $exams = DB::table('exams')->get();
        
        echo "=== EXAMS TABLE DATA ===\n";
        foreach ($exams as $exam) {
            echo "ID: {$exam->id}\n";
            echo "Name: {$exam->name}\n";
            echo "Duration: " . ($exam->duration_minutes ?? 'NULL') . " minutes\n";
            echo "---\n";
        }
        
        // Add duration if missing
        if (isset($exam->duration_minutes) && $exam->duration_minutes === null) {
            echo "\nAdding default duration to exams...\n";
            DB::table('exams')->update(['duration_minutes' => 120]);
            echo "✅ All exams now have 120 minutes duration\n";
        }
    }
}
