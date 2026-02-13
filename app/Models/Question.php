<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'visit_id',
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

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function caseStudy()
    {
        // Helper to access parent case study
        return $this->visit ? $this->visit->caseStudy() : null;
    }

    public function section()
    {
        return $this->visit && $this->visit->caseStudy ? $this->visit->caseStudy->section() : null;
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

