<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'code',
        'year_level',
        'course_strand',
        'lec_unit',
        'lab_unit',
        'required_room_type',
        'status',
        'description',
    ];

    protected $casts = [
        'lec_unit' => 'decimal:2',
        'lab_unit' => 'decimal:2',
    ];

    public function getLecUnitAttribute($value): int
    {
        if ($value !== null) {
            return (int) round((float) $value);
        }

        if (array_key_exists('hours', $this->attributes) && $this->attributes['hours'] !== null) {
            return (int) $this->attributes['hours'];
        }

        if (array_key_exists('unit', $this->attributes) && $this->attributes['unit'] !== null) {
            return (int) round((float) $this->attributes['unit']);
        }

        return 0;
    }

    public function getLabUnitAttribute($value): int
    {
        if ($value !== null) {
            return (int) round((float) $value);
        }

        return 0;
    }

    public function scopeActive($query)
    {
        if (!Schema::hasColumn('subjects', 'status')) {
            return $query;
        }

        return $query->where('status', 'active');
    }

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
        $relation = $this->belongsToMany(Curriculum::class, 'curriculum_subject', 'subject_id', 'curriculum_id');

        if (Schema::hasTable('curriculum_subject') && Schema::hasColumn('curriculum_subject', 'prerequisite')) {
            $relation->withPivot('prerequisite');
        }

        return $relation;
    }
}
