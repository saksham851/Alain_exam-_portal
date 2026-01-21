<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'case_study_id',
        'question_text',
        'question_type',
        'ig_weight',
        'dm_weight',
        'status',
        'cloned_from_id',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function caseStudy()
    {
        return $this->belongsTo(CaseStudy::class, 'case_study_id');
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function attemptAnswers()
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    public function clonedFrom()
    {
        return $this->belongsTo(Question::class, 'cloned_from_id');
    }

    public function getMarksAttribute()
    {
        return $this->ig_weight + $this->dm_weight;
    }
}
