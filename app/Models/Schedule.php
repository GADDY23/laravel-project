<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Schedule extends Model
{
    protected $fillable = [
        'teacher_id',
        'subject_id',
        'section_id',
        'room_id',
        'term_id',
        'day',
        'time_start',
        'time_end',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'time_start' => 'datetime:H:i',
        'time_end' => 'datetime:H:i',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    // Check if time overlaps with another schedule
    public function overlapsWith($otherSchedule): bool
    {
        $thisStart = Carbon::parse($this->time_start);
        $thisEnd = Carbon::parse($this->time_end);
        $otherStart = Carbon::parse($otherSchedule->time_start);
        $otherEnd = Carbon::parse($otherSchedule->time_end);

        return $thisStart->lt($otherEnd) && $thisEnd->gt($otherStart);
    }
}
