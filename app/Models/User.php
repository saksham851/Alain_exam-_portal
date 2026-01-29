<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';
 
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role',
        'first_name',
        'last_name',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'profile_picture',
        'is_blocked',
        'status',
        'remember_token',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the user's full name
     */
    public function getNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_blocked' => 'boolean',
            'status' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    // Relationships
    public function studentExams()
    {
        return $this->hasMany(StudentExam::class, 'student_id');
    }

    public function examAttempts()
    {
        return $this->hasManyThrough(ExamAttempt::class, StudentExam::class, 'student_id', 'student_exam_id');
    }

    /**
     * Get the user's most recent exam attempt
     */
    public function getLastExamAttemptAttribute()
    {
        return $this->examAttempts()
            ->whereNotNull('started_at')
            ->orderBy('started_at', 'desc')
            ->first();
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a manager
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Check if user is a student
     */
    public function isStudent(): bool
    {
        return $this->role === 'student';
    }
}
