<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property Collection<int, FormField> $fields
 */
class Form extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function isInUse(): bool
    {
        return $this->events()
            ->where('registration_opens_at', '<=', now())
            ->where('registration_closes_at', '>=', now())
            ->exists();
    }
}
