<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamStandard extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function categories()
    {
        return $this->hasMany(ScoreCategory::class);
    }

    public function category1()
    {
        return $this->hasOne(ScoreCategory::class)->where('category_number', 1);
    }

    public function category2()
    {
        return $this->hasOne(ScoreCategory::class)->where('category_number', 2);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}
