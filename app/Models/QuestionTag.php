<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionTag extends Model
{
    protected $table = 'question_tags';

    protected $fillable = [
        'question_id',
        'score_category_id',
        'content_area_id',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function scoreCategory()
    {
        return $this->belongsTo(ScoreCategory::class, 'score_category_id');
    }

    public function contentArea()
    {
        return $this->belongsTo(ContentArea::class, 'content_area_id');
    }
}
