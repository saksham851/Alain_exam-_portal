<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamStandardContentArea extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'percentage',
        'order_no',
    ];

    protected $casts = [
        'percentage' => 'integer',
        'order_no' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(ExamStandardCategory::class, 'category_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'content_area_id');
    }
}
