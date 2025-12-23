<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttemptAnswer extends Model
{
    protected $fillable = [
        'attempt_id',
        'question_id',
        'selected_options',
        'is_correct',
        'ig_score',
        'dm_score',
        'autosave_snapshot',
    ];

    protected $casts = [
        'selected_options' => 'array',
        'autosave_snapshot' => 'array',
        'is_correct' => 'boolean',
        'ig_score' => 'float',
        'dm_score' => 'float',
    ];

    public function attempt()
    {
        return $this->belongsTo(ExamAttempt::class, 'attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
