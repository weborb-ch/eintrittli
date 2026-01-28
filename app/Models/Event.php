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
        'slug',
        'short_code',
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
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->name).'-'.Str::random(6);
            }
            if (empty($event->short_code)) {
                $event->short_code = strtoupper(Str::random(6));
            }
        });
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
        return url("/r/{$this->slug}/{$this->short_code}");
    }
}
