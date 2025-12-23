<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseStudy extends Model
{
    protected $fillable = [
        'section_id',
        'title',
        'content',
        'order_no',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'case_study_id');
    }
}
