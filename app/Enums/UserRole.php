<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::Admin => __('Admin'),
            self::Member => __('Member'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Admin => __('Full access to all features including user management and CSV export.'),
            self::Member => __('Read access to all data. Full access to registrations except CSV export.'),
        };
    }
}
