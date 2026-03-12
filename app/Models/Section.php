<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Section extends Model
{
    protected $fillable = [
        'name',
        'course_strand',
        'year_level',
        'capacity',
        'term_id',
        'curriculum_id',
        'status',
        'adviser_id',
    ];

    public function scopeActive($query)
    {
        if (!Schema::hasColumn('sections', 'status')) {
            return $query;
        }

        return $query->where('status', 'active');
    }

    public function adviser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adviser_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'section', 'name')->where('role', 'student');
    }
}
