<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Term extends Model
{
    protected $fillable = [
        'term_code',
        'academic_year',
        'semester',
        'status',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public static function academicYearOptions(): array
    {
        $startYear = 2025;
        $endYear = 2035;
        $options = [];

        for ($year = $startYear; $year <= $endYear; $year++) {
            $nextYear = $year + 1;
            $options[] = "SY {$year}-{$nextYear}";
        }

        return $options;
    }

    public static function semesterOptions(): array
    {
        return [
            '1st Semester',
            '2nd Semester',
        ];
    }

    public function scopeActive($query)
    {
        if (Schema::hasColumn('terms', 'status')) {
            $query->where('status', 'active');

            if (Schema::hasColumn('terms', 'is_enabled')) {
                $query->where('is_enabled', true);
            }

            return $query;
        }

        if (Schema::hasColumn('terms', 'is_active')) {
            return $query->where('is_active', true);
        }

        return $query->where('status', 'active');
    }

    public function scopeEnabled($query)
    {
        if (Schema::hasColumn('terms', 'is_enabled')) {
            return $query->where('is_enabled', true);
        }

        return $query;
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
