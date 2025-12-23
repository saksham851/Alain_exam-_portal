<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentExam extends Model
{
    protected $fillable = [
        'student_id',
        'exam_id',
        'expiry_date',
        'attempts_allowed',
        'attempts_used',
        'source',
        'status',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'status' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function attempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }
}
