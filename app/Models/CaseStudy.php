<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseStudy extends Model
{
    protected $fillable = [
        'section_id',
        'title',
        'content',
        'order_no',
        'status',
        'cloned_from_id',
        'cloned_from_section_id',
        'cloned_at',
    ];

    protected $casts = [
        'status' => 'boolean',
        'cloned_at' => 'datetime',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function questions()
    {
        return $this->hasManyThrough(Question::class, Visit::class);
    }

    // Clone tracking relationships
    public function clonedFrom()
    {
        return $this->belongsTo(CaseStudy::class, 'cloned_from_id');
    }

    public function clonedFromSection()
    {
        return $this->belongsTo(Section::class, 'cloned_from_section_id');
    }

    public function clones()
    {
        return $this->hasMany(CaseStudy::class, 'cloned_from_id');
    }
}
