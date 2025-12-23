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
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function caseStudies()
    {
        return $this->hasMany(CaseStudy::class, 'section_id');
    }
}
