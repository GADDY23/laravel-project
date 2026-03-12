<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Schema;

class Curriculum extends Model
{
    protected $fillable = [
        'curriculum_code',
        'school_year_start',
        'course_type',
        'curriculum_type',
        'course_strand',
        'year_level',
        'description',
        'term_id',
        'prerequisite_subject_id',
    ];

    public function scopeActive($query)
    {
        return $query;
    }

    public function subjects(): BelongsToMany
    {
        $relation = $this->belongsToMany(Subject::class, 'curriculum_subject', 'curriculum_id', 'subject_id');

        if (Schema::hasTable('curriculum_subject')) {
            $pivotColumns = [];

            if (Schema::hasColumn('curriculum_subject', 'prerequisite')) {
                $pivotColumns[] = 'prerequisite';
            }
            if (Schema::hasColumn('curriculum_subject', 'year_level')) {
                $pivotColumns[] = 'year_level';
            }
            if (Schema::hasColumn('curriculum_subject', 'period_number')) {
                $pivotColumns[] = 'period_number';
            }
            if (Schema::hasColumn('curriculum_subject', 'period_label')) {
                $pivotColumns[] = 'period_label';
            }

            if (!empty($pivotColumns)) {
                $relation->withPivot($pivotColumns);
            }
        }

        return $relation;
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function prerequisiteSubject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'prerequisite_subject_id');
    }
}
