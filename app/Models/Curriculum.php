<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Curriculum extends Model
{
    protected $fillable = [
        'code',
        'name',
        'strand_program',
        'year_level',
        'semester',
    ];

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'curriculum_subject', 'curriculum_id', 'subject_id');
    }
}
