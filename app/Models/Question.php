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

    /**
     * Get the case study this question belongs to (via visit).
     * Uses hasOneThrough so it can also be eager-loaded.
     */
    public function caseStudy()
    {
        return $this->hasOneThrough(
            CaseStudy::class,
            Visit::class,
            'id',        // visits.id
            'id',        // case_studies.id
            'visit_id',  // questions.visit_id
            'case_study_id' // visits.case_study_id
        );
    }

    /**
     * Helper to get the section this question belongs to.
     * Not a relationship - use ->getSection() to access.
     */
    public function getSection()
    {
        if ($this->relationLoaded('visit') && $this->visit
            && $this->visit->relationLoaded('caseStudy') && $this->visit->caseStudy) {
            return $this->visit->caseStudy->section;
        }
        return $this->visit?->caseStudy?->section;
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

