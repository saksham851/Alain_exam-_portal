<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'case_study_id',
        'question_text',
        'question_type',
        'status',
        'cloned_from_id',
        'max_question_points',
    ];

    protected $casts = [
        'status' => 'boolean',
        'max_question_points' => 'integer',
    ];

    public function caseStudy()
    {
        return $this->belongsTo(CaseStudy::class);
    }

    public function section()
    {
        return $this->caseStudy->section();
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

    public function tags()
    {
        return $this->hasMany(QuestionTag::class);
    }


}

