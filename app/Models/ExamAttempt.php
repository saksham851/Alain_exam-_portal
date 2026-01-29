<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    protected $fillable = [
        'student_exam_id',
        'started_at',
        'ended_at',
        'status',
        'time_remaining',
        'ig_score',
        'dm_score',
        'total_score',
        'is_passed',
        'tab_switch_count',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_passed' => 'boolean',
        'ig_score' => 'float',
        'dm_score' => 'float',
        'total_score' => 'float',
    ];

    public function studentExam()
    {
        return $this->belongsTo(StudentExam::class);
    }

    public function answers()
    {
        return $this->hasMany(AttemptAnswer::class, 'attempt_id');
    }

    public function getFormattedDurationAttribute()
    {
        if (!$this->started_at || !$this->ended_at) {
            return 'N/A';
        }

        $diff = $this->started_at->diff($this->ended_at);
        if ($diff->h > 0) {
            return $diff->format('%hh %im');
        }
        
        return $diff->format('%im %ss');
    }
}
