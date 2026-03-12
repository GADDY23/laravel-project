<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseStrand extends Model
{
    public const TYPE_SHS = 'shs';
    public const TYPE_COLLEGE = 'college';

    public const YEAR_LEVELS = [
        self::TYPE_SHS => ['grade_11', 'grade_12'],
        self::TYPE_COLLEGE => ['1st_year', '2nd_year', '3rd_year', '4th_year'],
    ];

    protected $fillable = [
        'name',
        'description',
        'type',
    ];

    public static function yearLevelsForType(?string $type): array
    {
        return self::YEAR_LEVELS[$type] ?? [];
    }
}
