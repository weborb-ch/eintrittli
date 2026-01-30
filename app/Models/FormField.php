<?php

namespace App\Models;

use App\Enums\FormFieldType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property FormFieldType $type
 * @property array<int, string>|null $options
 * @property bool $is_required
 * @property string $name
 * @property int $sort_order
 */
class FormField extends Model
{
    protected $fillable = [
        'form_id',
        'type',
        'name',
        'options',
        'is_required',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => FormFieldType::class,
            'options' => 'array',
            'is_required' => 'boolean',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
