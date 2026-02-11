<?php

namespace App\Enums;

enum FormFieldType: string
{
    case Text = 'text';
    case Number = 'number';
    case Boolean = 'boolean';
    case Date = 'date';
    case Email = 'email';
    case Select = 'select';
    case Description = 'description';

    public function label(): string
    {
        return match ($this) {
            self::Text => __('Text'),
            self::Number => __('Number'),
            self::Boolean => __('Yes/No'),
            self::Date => __('Date'),
            self::Email => __('Email'),
            self::Select => __('Selection'),
            self::Description => __('Description'),
        };
    }
}
