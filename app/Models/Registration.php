<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Registration extends Model
{
    protected $fillable = [
        'event_id',
        'data',
        'confirmation_code',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Registration $registration) {
            if (empty($registration->confirmation_code)) {
                $registration->confirmation_code = strtoupper(Str::random(8));
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
