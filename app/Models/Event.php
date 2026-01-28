<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $fillable = [
        'form_id',
        'name',
        'code',
        'registration_opens_at',
        'registration_closes_at',
    ];

    protected function casts(): array
    {
        return [
            'registration_opens_at' => 'datetime',
            'registration_closes_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            if (empty($event->code)) {
                $event->code = self::generateUniqueCode();
            }
        });
    }

    public static function generateUniqueCode(): string
    {
        do {
            $code = strtolower(Str::random(6));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function isRegistrationOpen(): bool
    {
        $now = now();

        if (
            $this->registration_opens_at && $now->lt($this->registration_opens_at) ||
            $this->registration_closes_at && $now->gt($this->registration_closes_at)
        ) {
            return false;
        }

        return true;
    }

    public function getRegistrationUrl(): string
    {
        return url("/r/{$this->code}");
    }
}
