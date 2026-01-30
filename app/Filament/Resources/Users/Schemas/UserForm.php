<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('User Details'))
                    ->schema([
                        TextInput::make('username')
                            ->label(__('Username'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label(__('Password'))
                            ->password()
                            ->required(fn ($record) => $record === null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->maxLength(255),
                        Select::make('role')
                            ->label(__('Role'))
                            ->options(collect(UserRole::cases())->mapWithKeys(fn (UserRole $role) => [$role->value => $role->label()]))
                            ->required()
                            ->default(UserRole::Member->value),
                    ]),
                Section::make(__('Role Information'))
                    ->schema([
                        Placeholder::make('role_info')
                            ->label('')
                            ->content(new HtmlString(self::getRoleDescriptionsHtml())),
                    ]),
            ]);
    }

    private static function getRoleDescriptionsHtml(): string
    {
        $descriptions = [];
        foreach (UserRole::cases() as $role) {
            $descriptions[] = '<div class="mb-2"><strong>'.$role->label().':</strong> '.$role->description().'</div>';
        }

        return implode('', $descriptions);
    }
}
