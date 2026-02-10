<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoreCategory extends Model
{
    protected $table = 'score_categories';

    protected $fillable = [
        'exam_standard_id',
        'name',
        'category_number',
    ];

    protected $casts = [
        'category_number' => 'integer',
    ];

    public function examStandard()
    {
        return $this->belongsTo(ExamStandard::class);
    }

    public function contentAreas()
    {
        return $this->hasMany(ContentArea::class, 'score_category_id')->orderBy('order_no');
    }
}
