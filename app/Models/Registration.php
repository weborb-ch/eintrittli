<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $event_id
 * @property array<string, mixed> $data
 * @property string $confirmation_code
 * @property string $registration_group_id
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read Event $event
 */
class Registration extends Model
{
    protected $fillable = [
        'event_id',
        'data',
        'confirmation_code',
        'registration_group_id',
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
