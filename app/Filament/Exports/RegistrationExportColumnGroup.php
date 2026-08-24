<?php

namespace App\Filament\Exports;

use Illuminate\Support\Collection;

/**
 * A labelled set of export columns, shown as one block in the column picker.
 */
class RegistrationExportColumnGroup
{
    public const GeneralKey = 'general';

    /**
     * @param  Collection<string, RegistrationExportColumn>  $columns
     */
    public function __construct(
        public string $key,
        public string $label,
        public Collection $columns,
    ) {}

    /**
     * @return list<string>
     */
    public function keys(): array
    {
        return $this->columns->keys()->all();
    }

    /**
     * @return array<string, string>
     */
    public function options(): array
    {
        return $this->columns
            ->map(fn (RegistrationExportColumn $column): string => $column->label)
            ->all();
    }
}
