<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamCategory extends Model
{
    protected $fillable = [
        'name',
        'certification_type',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get all exams in this category
     */
    public function exams()
    {
        return $this->hasMany(Exam::class, 'category_id');
    }
}
