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
        'total_score',
        'category_breakdown',
        'content_area_breakdown',
        'is_passed',
        'tab_switch_count',
        'timezone',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_passed' => 'boolean',
        'total_score' => 'float',
        'category_breakdown' => 'array',
        'content_area_breakdown' => 'array',
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

    public function getFormattedStartTimeAttribute()
    {
        if (!$this->started_at) return 'N/A';
        $time = clone $this->started_at;
        if ($this->timezone) {
            $time->setTimezone($this->timezone);
        }
        return $time->format('M d, Y h:i A') . ($this->timezone ? ' (' . $this->timezone . ')' : '');
    }

    public function getFormattedEndTimeAttribute()
    {
        if (!$this->ended_at) return 'N/A';
        $time = clone $this->ended_at;
        if ($this->timezone) {
            $time->setTimezone($this->timezone);
        }
        return $time->format('M d, Y h:i A') . ($this->timezone ? ' (' . $this->timezone . ')' : '');
    }
}
