<?php

namespace App\Filament\Resources\Forms\Pages;

use App\Filament\Resources\Forms\FormResource;
use App\Models\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateForm extends CreateRecord
{
    protected static string $resource = FormResource::class;

    protected function fillForm(): void
    {
        $duplicateId = request()->integer('duplicate') ?: null;

        if (! $duplicateId) {
            parent::fillForm();

            return;
        }

        $form = Form::with('fields')->find($duplicateId);

        if (! $form) {
            parent::fillForm();

            return;
        }

        $this->form->fill([
            'name' => $form->name.' ('.__('Copy').')',
            'description' => $form->description,
            'fields' => $form->fields->map(fn ($field) => [
                'type' => $field->type->value,
                'name' => $field->name,
                'options' => $field->options,
                'content' => $field->content,
                'is_required' => $field->is_required,
                'must_be_true' => $field->must_be_true,
            ])->toArray(),
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
