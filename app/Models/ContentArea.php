<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentArea extends Model
{
    protected $table = 'content_areas';

    protected $fillable = [
        'score_category_id',
        'name',
        'max_points',
        'percentage',
        'order_no',
    ];

    protected $casts = [
        'max_points' => 'integer',
        'percentage' => 'integer',
        'order_no' => 'integer',
    ];

    public function scoreCategory()
    {
        return $this->belongsTo(ScoreCategory::class, 'score_category_id');
    }

    public function questionTags()
    {
        return $this->hasMany(QuestionTag::class, 'content_area_id');
    }
}
