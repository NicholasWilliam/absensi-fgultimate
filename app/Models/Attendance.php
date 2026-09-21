<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'shift_id', 'type', 'photo_path',
    'latitude', 'longitude', 'distance_meters', 'within_radius',
    'status', 'server_time',
])]
class Attendance extends Model
{
    protected function casts(): array
    {
        return [
            'within_radius' => 'boolean',
            'server_time' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Shift, $this> */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
