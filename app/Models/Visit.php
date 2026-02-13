<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = [
        'case_study_id',
        'title',
        'description',
        'order_no',
        'status',
    ];

    public function caseStudy()
    {
        return $this->belongsTo(CaseStudy::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
