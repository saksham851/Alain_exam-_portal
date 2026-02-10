<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttemptScoreReport extends Model
{
    protected $fillable = [
        'attempt_id',
        'overall_score',
        'category_1_overall_score',
        'category_2_overall_score',
        'category_1_breakdown',
        'category_2_breakdown',
        'passed',
        'pdf_path',
    ];

    protected $casts = [
        'overall_score' => 'decimal:2',
        'category_1_overall_score' => 'decimal:2',
        'category_2_overall_score' => 'decimal:2',
        'category_1_breakdown' => 'array',
        'category_2_breakdown' => 'array',
        'passed' => 'boolean',
    ];

    public function attempt()
    {
        return $this->belongsTo(ExamAttempt::class, 'attempt_id');
    }
}
