<?php

namespace App\Forms\Components;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Joaopaulolndev\FilamentEditProfile\Livewire\EditProfileForm;

class ExtendedEditProfileForm extends EditProfileForm
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill($this->user->only('username')); // @phpstan-ignore property.notFound
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('filament-edit-profile::default.profile_information'))
                    ->aside()
                    ->description(__('filament-edit-profile::default.profile_information_description'))
                    ->schema([
                        TextInput::make('username')
                            ->label(__('Username'))
                            ->required()
                            ->rules(['unique:users']),
                    ]),
            ])
            ->statePath('data');
    }
}
