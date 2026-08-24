<?php

namespace App\Filament\Exports;

use App\Enums\FormFieldType;
use App\Models\FormField;
use App\Models\Registration;
use Carbon\Carbon;
use Closure;

/**
 * A single selectable column of a registration export.
 */
class RegistrationExportColumn
{
    /**
     * @param  Closure(Registration): string  $getValueUsing
     */
    private function __construct(
        public string $key,
        public string $label,
        private Closure $getValueUsing,
    ) {}

    /**
     * @param  Closure(Registration): string  $getValueUsing
     */
    public static function make(string $key, string $label, Closure $getValueUsing): self
    {
        return new self($key, $label, $getValueUsing);
    }

    /**
     * Builds the column that reads a single form field out of the registration data.
     */
    public static function formField(FormField $field): self
    {
        $name = (string) $field->name;
        $type = $field->type;

        return new self(
            'field.'.$name,
            $name,
            fn (Registration $registration): string => self::formatFieldValue($registration->data[$name] ?? null, $type),
        );
    }

    public function getValue(Registration $registration): string
    {
        return ($this->getValueUsing)($registration);
    }

    /**
     * Renders a raw form field value the same way across the table and the export.
     */
    public static function formatFieldValue(mixed $value, FormFieldType $type): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return match ($type) {
            FormFieldType::Boolean => $value ? __('Yes') : __('No'),
            FormFieldType::Date => self::formatDate($value),
            default => (string) $value,
        };
    }

    private static function formatDate(mixed $value): string
    {
        try {
            return Carbon::parse($value)->format('d.m.Y');
        } catch (\Exception) {
            return (string) $value;
        }
    }
}
