<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'category_id',
        'exam_code',
        'name',
        'description',
        'duration_minutes',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ExamCategory::class, 'category_id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function studentExams()
    {
        return $this->hasMany(StudentExam::class);
    }
    
    // Accessor for duration (returns duration_minutes)
    public function getDurationAttribute()
    {
        return $this->duration_minutes;
    }
}
