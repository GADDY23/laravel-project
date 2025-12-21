<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'code',
        'year_level',
        'semester',
        'course_strand',
        'hours',
        'required_room_type',
        'description',
    ];

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'teacher_subject', 'subject_id', 'teacher_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function curricula(): BelongsToMany
    {
        return $this->belongsToMany(Curriculum::class, 'curriculum_subject', 'subject_id', 'curriculum_id');
    }
}
