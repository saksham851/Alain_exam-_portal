<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamCategoryPassingScore extends Model
{
    protected $fillable = ['exam_id', 'exam_standard_category_id', 'passing_score'];

    public function exam() {
        return $this->belongsTo(Exam::class);
    }

    public function category() {
        return $this->belongsTo(ExamStandardCategory::class, 'exam_standard_category_id');
    }
}
