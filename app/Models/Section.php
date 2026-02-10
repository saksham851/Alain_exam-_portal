<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'exam_id',
        'title',
        'content',
        'order_no',
        'status',
        'cloned_from_id',
        'exam_standard_category_id',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function examStandardCategory()
    {
        return $this->belongsTo(ExamStandardCategory::class, 'exam_standard_category_id');
    }

    public function caseStudies()
    {
        return $this->hasMany(CaseStudy::class, 'section_id');
    }

    public function clonedFrom()
    {
        return $this->belongsTo(Section::class, 'cloned_from_id');
    }

    public function questions()
    {
        return $this->hasManyThrough(Question::class, CaseStudy::class);
    }
}
