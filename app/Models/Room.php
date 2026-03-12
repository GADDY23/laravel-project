<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Room extends Model
{
    public const BUILDINGS = [
        'Aclc Main' => 'Aclc Main',
        'Aclc SHS' => 'Aclc SHS',
    ];

    protected $fillable = [
        'name',
        'capacity',
        'building',
        'floor',
        'room_type',
        'status',
        'description',
    ];

    public function scopeAvailable($query)
    {
        if (!Schema::hasColumn('rooms', 'status')) {
            return $query;
        }

        return $query->where('status', 'available');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
