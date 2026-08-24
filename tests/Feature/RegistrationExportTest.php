<?php

use App\Enums\ExportFormat;
use App\Enums\FormFieldType;
use App\Enums\UserRole;
use App\Filament\Exports\RegistrationExportColumnGroup;
use App\Filament\Exports\RegistrationExporter;
use App\Filament\Resources\Registrations\Pages\ListRegistrations;
use App\Models\Event;
use App\Models\Form;
use App\Models\FormField;
use App\Models\Registration;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use Symfony\Component\HttpFoundation\StreamedResponse;

uses(RefreshDatabase::class);

function conferenceForm(): Form
{
    $form = Form::factory()->create(['name' => 'Konferenz']);

    FormField::factory()->for($form)->create(['name' => 'Vorname', 'sort_order' => 0]);
    FormField::factory()->for($form)->boolean()->create(['name' => 'Newsletter', 'sort_order' => 1]);
    FormField::factory()->for($form)->date()->create(['name' => 'Geburtsdatum', 'sort_order' => 2]);
    FormField::factory()->for($form)->description('Hinweis')->create(['sort_order' => 3]);

    return $form;
}

function workshopForm(): Form
{
    $form = Form::factory()->create(['name' => 'Workshop']);

    FormField::factory()->for($form)->select(['Anfänger', 'Profi'])->create(['name' => 'Level', 'sort_order' => 0]);

    return $form;
}

function streamedContent(StreamedResponse $response): string
{
    ob_start();
    $response->sendContent();

    return (string) ob_get_clean();
}

/**
 * @return list<list<string>>
 */
function csvRows(string $content): array
{
    $content = str_starts_with($content, "\xEF\xBB\xBF") ? substr($content, 3) : $content;

    return array_map(
        fn (string $line): array => str_getcsv($line, ',', '"', ''),
        array_filter(explode("\n", trim($content))),
    );
}

/**
 * Builds the grouped `columns` modal state that selects exactly the given column keys.
 *
 * @param  list<string>  $keys
 * @return array<string, list<string>>
 */
function selectColumns(array $keys): array
{
    return RegistrationExporter::resolveColumnGroups()
        ->mapWithKeys(fn (RegistrationExportColumnGroup $group): array => [
            $group->key => array_values(array_intersect($group->keys(), $keys)),
        ])
        ->all();
}

function downloadedContent(Testable $component): string
{
    return (string) base64_decode((string) data_get($component->effects, 'download.content'), true);
}

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
    $this->actingAs($this->admin);
});

it('offers only the columns of the forms it is scoped to', function () {
    $conferenceForm = conferenceForm();
    workshopForm();

    $columns = RegistrationExporter::resolveColumns([$conferenceForm->getKey()]);

    expect($columns->keys()->all())->toBe([
        'confirmation_code',
        'event',
        'form',
        'created_at',
        'notes',
        'field.Vorname',
        'field.Newsletter',
        'field.Geburtsdatum',
    ]);
});

it('offers the columns of every form when nothing narrows them down', function () {
    conferenceForm();
    workshopForm();

    $columns = RegistrationExporter::resolveColumns();

    expect($columns->keys())->toContain('field.Vorname', 'field.Level');
});

it('offers a form without any registrations', function () {
    $emptyForm = workshopForm();
    $usedForm = conferenceForm();

    $event = Event::factory()->for($usedForm)->create();
    Registration::factory()->for($event)->create(['data' => []]);

    Event::factory()->for($emptyForm)->create(['name' => 'Workshop ohne Anmeldungen']);

    Livewire::test(ListRegistrations::class)
        ->mountAction('export')
        ->assertSchemaComponentExists('columns.form_'.$emptyForm->getKey());

    Livewire::test(ListRegistrations::class)
        ->filterTable('form', [$emptyForm->getKey()])
        ->mountAction('export')
        ->assertSchemaComponentExists('columns.form_'.$emptyForm->getKey())
        ->assertSchemaComponentDoesNotExist('columns.form_'.$usedForm->getKey());
});

it('keeps a form whose only field is a description block', function () {
    $descriptionOnlyForm = Form::factory()->create(['name' => 'Nur Hinweistext']);
    FormField::factory()->for($descriptionOnlyForm)->description('Teilnahmebedingungen')->create(['sort_order' => 0]);

    $usedForm = conferenceForm();
    Registration::factory()->for(Event::factory()->for($usedForm))->create(['data' => []]);

    $groups = RegistrationExporter::resolveColumnGroups();

    expect($groups->keys())->toContain('form_'.$descriptionOnlyForm->getKey())
        ->and($groups->get('form_'.$descriptionOnlyForm->getKey())->columns)->toBeEmpty();

    Livewire::test(ListRegistrations::class)
        ->filterTable('form', [$descriptionOnlyForm->getKey()])
        ->mountAction('export')
        ->assertSchemaComponentDoesNotExist('columns.form_'.$descriptionOnlyForm->getKey())
        ->assertSchemaComponentExists('emptyColumns.form_'.$descriptionOnlyForm->getKey());
});

it('offers a form that has no events or registrations at all', function () {
    $unusedForm = workshopForm();
    $usedForm = conferenceForm();

    Registration::factory()->for(Event::factory()->for($usedForm))->create(['data' => []]);

    Livewire::test(ListRegistrations::class)
        ->mountAction('export')
        ->assertSchemaComponentExists('columns.form_'.$unusedForm->getKey());
});

it('labels the general group "Generell" in german', function () {
    conferenceForm();

    expect(RegistrationExporter::resolveColumnGroups()->get('general')->label)->toBe(__('General'))
        ->and(__('General'))->toBe(app()->getLocale() === 'de' ? 'Generell' : 'General');
});

it('exports the selected columns as csv in the order they are defined', function () {
    $event = Event::factory()->for(conferenceForm())->create(['name' => 'Konferenz 2026']);

    Registration::factory()->for($event)->create([
        'confirmation_code' => 'ABC12345',
        'data' => [
            'Vorname' => 'Alice',
            'Newsletter' => true,
            'Geburtsdatum' => '1990-03-07',
        ],
    ]);

    $columns = RegistrationExporter::resolveColumns()
        ->only(['field.Geburtsdatum', 'confirmation_code', 'field.Newsletter']);

    $content = streamedContent(RegistrationExporter::stream(
        Registration::query(),
        $columns,
        'export',
        ExportFormat::Csv,
    ));

    expect($content)->toStartWith("\xEF\xBB\xBF")
        ->and(csvRows($content))->toBe([
            [__('Confirmation code'), 'Newsletter', 'Geburtsdatum'],
            ['ABC12345', __('Yes'), '07.03.1990'],
        ]);
});

it('keeps the defined column order and leaves missing values empty', function () {
    $event = Event::factory()->for(conferenceForm())->create();

    Registration::factory()->for($event)->create([
        'confirmation_code' => 'ABC12345',
        'data' => ['Vorname' => 'Alice'],
    ]);

    $columns = RegistrationExporter::resolveColumns()
        ->only(['field.Vorname', 'field.Newsletter', 'notes']);

    expect(csvRows(streamedContent(RegistrationExporter::stream(
        Registration::query(),
        $columns,
        'export',
        ExportFormat::Csv,
    ))))->toBe([
        [__('Notes'), 'Vorname', 'Newsletter'],
        ['', 'Alice', ''],
    ]);
});

it('honours the chosen csv separator', function () {
    $event = Event::factory()->for(conferenceForm())->create();

    Registration::factory()->for($event)->create([
        'confirmation_code' => 'ABC12345',
        'data' => ['Vorname' => 'Alice'],
    ]);

    $columns = RegistrationExporter::resolveColumns()
        ->only(['confirmation_code', 'field.Vorname']);

    $content = streamedContent(RegistrationExporter::stream(
        Registration::query(),
        $columns,
        'export',
        ExportFormat::Csv,
        ';',
    ));

    expect($content)->toContain('ABC12345;Alice');
});

it('exports a readable xlsx spreadsheet', function () {
    $event = Event::factory()->for(conferenceForm())->create(['name' => 'Konferenz 2026']);

    Registration::factory()->for($event)->create([
        'confirmation_code' => 'ABC12345',
        'data' => ['Vorname' => 'Alice'],
    ]);

    $columns = RegistrationExporter::resolveColumns()
        ->only(['confirmation_code', 'event', 'field.Vorname']);

    $content = streamedContent(RegistrationExporter::stream(
        Registration::query(),
        $columns,
        'export',
        ExportFormat::Xlsx,
    ));

    $path = tempnam(sys_get_temp_dir(), 'export').'.xlsx';
    file_put_contents($path, $content);

    $reader = new XlsxReader;
    $reader->open($path);

    $rows = [];

    foreach ($reader->getSheetIterator() as $sheet) {
        foreach ($sheet->getRowIterator() as $row) {
            $rows[] = $row->toArray();
        }
    }

    $reader->close();
    unlink($path);

    expect($rows)->toBe([
        [__('Confirmation code'), __('Event'), 'Vorname'],
        ['ABC12345', 'Konferenz 2026', 'Alice'],
    ]);
});

it('falls back to the default file name when the given one is unusable', function () {
    expect(RegistrationExporter::sanitizeFileName('reports/2026: "final"'))->toBe('reports2026 final')
        ->and(RegistrationExporter::sanitizeFileName('  '))->toBe(RegistrationExporter::defaultFileName())
        ->and(RegistrationExporter::sanitizeFileName(null))->toBe(RegistrationExporter::defaultFileName());
});

it('preselects every available column and shows the separator only for csv', function () {
    $form = conferenceForm();
    $event = Event::factory()->for($form)->create();

    Registration::factory()->for($event)->create(['data' => []]);

    Livewire::test(ListRegistrations::class)
        ->mountAction('export')
        ->assertSchemaStateSet([
            'fileName' => RegistrationExporter::defaultFileName(),
            'format' => ExportFormat::Csv->value,
            'csvDelimiter' => ',',
            'columns' => [
                'general' => ['confirmation_code', 'event', 'form', 'created_at', 'notes'],
                'form_'.$form->getKey() => ['field.Vorname', 'field.Newsletter', 'field.Geburtsdatum'],
            ],
        ])
        ->assertSchemaComponentVisible('csvDelimiter')
        ->fillForm(['format' => ExportFormat::Xlsx->value])
        ->assertSchemaComponentHidden('csvDelimiter');
});

it('groups the columns by form and drops forms the filter excluded', function () {
    $conferenceForm = conferenceForm();
    $workshopForm = workshopForm();

    $conference = Event::factory()->for($conferenceForm)->create(['name' => 'Konferenz 2026']);
    Event::factory()->for($workshopForm)->create(['name' => 'Workshop 2026']);

    $groups = RegistrationExporter::resolveColumnGroups();

    expect($groups->keys()->all())->toBe([
        'general',
        'form_'.$conferenceForm->getKey(),
        'form_'.$workshopForm->getKey(),
    ])
        ->and($groups->get('form_'.$conferenceForm->getKey())->label)->toBe('Konferenz')
        ->and($groups->get('form_'.$workshopForm->getKey())->keys())->toBe(['field.Level']);

    Livewire::test(ListRegistrations::class)
        ->filterTable('event', [$conference->getKey()])
        ->mountAction('export')
        ->assertSchemaComponentExists('columns.form_'.$conferenceForm->getKey())
        ->assertSchemaComponentDoesNotExist('columns.form_'.$workshopForm->getKey());
});

it('lists a shared field in every form that has it while exporting one column', function () {
    $conferenceForm = conferenceForm();
    $workshopForm = workshopForm();

    FormField::factory()->for($workshopForm)->create(['name' => 'Vorname', 'sort_order' => 1]);

    $conference = Event::factory()->for($conferenceForm)->create();
    $workshop = Event::factory()->for($workshopForm)->create();

    Registration::factory()->for($conference)->create([
        'confirmation_code' => 'CONF0001',
        'data' => ['Vorname' => 'Alice'],
    ]);
    Registration::factory()->for($workshop)->create([
        'confirmation_code' => 'WORK0001',
        'data' => ['Vorname' => 'Bob'],
    ]);

    $groups = RegistrationExporter::resolveColumnGroups();

    expect($groups->get('form_'.$conferenceForm->getKey())->keys())->toContain('field.Vorname')
        ->and($groups->get('form_'.$workshopForm->getKey())->keys())->toContain('field.Vorname')
        ->and(RegistrationExporter::resolveColumns()->keys()->filter(
            fn (string $key): bool => $key === 'field.Vorname',
        )->count())->toBe(1);

    $component = Livewire::test(ListRegistrations::class)
        ->callAction('export', [
            'fileName' => 'export',
            'format' => ExportFormat::Csv->value,
            'csvDelimiter' => ',',
            'columns' => selectColumns(['confirmation_code', 'field.Vorname']),
        ])
        ->assertFileDownloaded('export.csv');

    expect(csvRows(downloadedContent($component)))->toBe([
        [__('Confirmation code'), 'Vorname'],
        ['CONF0001', 'Alice'],
        ['WORK0001', 'Bob'],
    ]);
});

it('keeps a shared field in step across the form groups', function () {
    $conferenceForm = conferenceForm();
    $workshopForm = workshopForm();

    FormField::factory()->for($workshopForm)->create(['name' => 'Vorname', 'sort_order' => 1]);

    Registration::factory()->for(Event::factory()->for($conferenceForm))->create(['data' => []]);
    Registration::factory()->for(Event::factory()->for($workshopForm))->create(['data' => []]);

    $conferenceKey = 'columns.form_'.$conferenceForm->getKey();
    $workshopKey = 'columns.form_'.$workshopForm->getKey();

    Livewire::test(ListRegistrations::class)
        ->mountAction('export')
        ->assertSchemaComponentStateSet($workshopKey, ['field.Level', 'field.Vorname'])
        ->fillForm([
            $conferenceKey => ['field.Newsletter', 'field.Geburtsdatum'],
        ])
        ->assertSchemaComponentStateSet($workshopKey, ['field.Level']);
});

it('rejects a submission with no column selected at all', function () {
    $form = conferenceForm();

    Registration::factory()->for(Event::factory()->for($form))->create(['data' => []]);

    Livewire::test(ListRegistrations::class)
        ->callAction('export', [
            'fileName' => 'export',
            'format' => ExportFormat::Csv->value,
            'csvDelimiter' => ',',
            'columns' => [
                'general' => [],
                'form_'.$form->getKey() => [],
            ],
        ])
        ->assertHasActionErrors(['columns.general'])
        ->assertNoFileDownloaded();
});

it('downloads the export with the chosen file name and format', function () {
    $event = Event::factory()->for(conferenceForm())->create();

    Registration::factory()->for($event)->create([
        'confirmation_code' => 'ABC12345',
        'data' => ['Vorname' => 'Alice'],
    ]);

    Livewire::test(ListRegistrations::class)
        ->callAction('export', [
            'fileName' => 'Anmeldungen: Konferenz',
            'format' => ExportFormat::Xlsx->value,
            'columns' => selectColumns(['confirmation_code']),
        ])
        ->assertFileDownloaded('Anmeldungen Konferenz.xlsx')
        ->assertNotified(trans_choice(':count registration exported|:count registrations exported', 1, ['count' => 1]));
});

it('only exports the columns that were selected', function () {
    $event = Event::factory()->for(conferenceForm())->create();

    Registration::factory()->for($event)->create([
        'confirmation_code' => 'ABC12345',
        'data' => ['Vorname' => 'Alice'],
    ]);

    $component = Livewire::test(ListRegistrations::class)
        ->callAction('export', [
            'fileName' => 'export',
            'format' => ExportFormat::Csv->value,
            'csvDelimiter' => ',',
            'columns' => selectColumns(['confirmation_code', 'field.Vorname']),
        ])
        ->assertFileDownloaded('export.csv');

    expect(downloadedContent($component))->toStartWith("\xEF\xBB\xBF")
        ->and(csvRows(downloadedContent($component)))->toBe([
            [__('Confirmation code'), 'Vorname'],
            ['ABC12345', 'Alice'],
        ]);
});

it('respects the table filters when exporting', function () {
    $conference = Event::factory()->for(conferenceForm())->create(['name' => 'Konferenz 2026']);
    $workshop = Event::factory()->for(workshopForm())->create(['name' => 'Workshop 2026']);

    Registration::factory()->for($conference)->create(['confirmation_code' => 'CONF0001', 'data' => []]);
    Registration::factory()->for($workshop)->create(['confirmation_code' => 'WORK0001', 'data' => []]);

    $component = Livewire::test(ListRegistrations::class)
        ->filterTable('event', [$conference->getKey()])
        ->callAction('export', [
            'fileName' => 'export',
            'format' => ExportFormat::Csv->value,
            'csvDelimiter' => ',',
            'columns' => selectColumns(['confirmation_code']),
        ])
        ->assertFileDownloaded('export.csv');

    expect(csvRows(downloadedContent($component)))->toBe([
        [__('Confirmation code')],
        ['CONF0001'],
    ]);
});

it('narrows rows and columns to the form chosen in the table filter', function () {
    $conferenceForm = conferenceForm();
    $workshopForm = workshopForm();

    $conference = Event::factory()->for($conferenceForm)->create(['name' => 'Konferenz 2026']);
    $workshop = Event::factory()->for($workshopForm)->create(['name' => 'Workshop 2026']);

    Registration::factory()->for($conference)->create([
        'confirmation_code' => 'CONF0001',
        'data' => ['Vorname' => 'Alice'],
    ]);
    Registration::factory()->for($workshop)->create([
        'confirmation_code' => 'WORK0001',
        'data' => ['Level' => 'Profi'],
    ]);

    Livewire::test(ListRegistrations::class)
        ->filterTable('form', [$workshopForm->getKey()])
        ->mountAction('export')
        ->assertSchemaComponentExists('columns.form_'.$workshopForm->getKey())
        ->assertSchemaComponentDoesNotExist('columns.form_'.$conferenceForm->getKey());

    $component = Livewire::test(ListRegistrations::class)
        ->filterTable('form', [$workshopForm->getKey()])
        ->callAction('export', [
            'fileName' => 'export',
            'format' => ExportFormat::Csv->value,
            'csvDelimiter' => ',',
            'columns' => [
                'general' => ['confirmation_code'],
                'form_'.$workshopForm->getKey() => ['field.Level'],
            ],
        ])
        ->assertFileDownloaded('export.csv');

    expect(csvRows(downloadedContent($component)))->toBe([
        [__('Confirmation code'), 'Level'],
        ['WORK0001', 'Profi'],
    ]);
});

it('respects the table search when exporting', function () {
    $event = Event::factory()->for(conferenceForm())->create();

    Registration::factory()->for($event)->create(['confirmation_code' => 'KEEP0001', 'data' => []]);
    Registration::factory()->for($event)->create(['confirmation_code' => 'SKIP0001', 'data' => []]);

    $component = Livewire::test(ListRegistrations::class)
        ->searchTable('KEEP0001')
        ->callAction('export', [
            'fileName' => 'export',
            'format' => ExportFormat::Csv->value,
            'csvDelimiter' => ',',
            'columns' => selectColumns(['confirmation_code']),
        ])
        ->assertFileDownloaded('export.csv');

    expect(csvRows(downloadedContent($component)))->toBe([
        [__('Confirmation code')],
        ['KEEP0001'],
    ]);
});

it('warns instead of downloading an empty file', function () {
    Livewire::test(ListRegistrations::class)
        ->callAction('export', [
            'fileName' => 'export',
            'format' => ExportFormat::Csv->value,
            'csvDelimiter' => ',',
            'columns' => selectColumns(['confirmation_code']),
        ])
        ->assertNotified(__('Nothing to export'))
        ->assertNoFileDownloaded();
});

it('exports only the selected registrations through the bulk action', function () {
    $event = Event::factory()->for(conferenceForm())->create();

    $selected = Registration::factory()->for($event)->create(['confirmation_code' => 'KEEP0001', 'data' => []]);
    Registration::factory()->for($event)->create(['confirmation_code' => 'SKIP0001', 'data' => []]);

    $component = Livewire::test(ListRegistrations::class)
        ->selectTableRecords([$selected->getKey()])
        ->callAction(TestAction::make('exportSelected')->table()->bulk(), [
            'fileName' => 'export',
            'format' => ExportFormat::Csv->value,
            'csvDelimiter' => ',',
            'columns' => selectColumns(['confirmation_code']),
        ])
        ->assertFileDownloaded('export.csv');

    expect(csvRows(downloadedContent($component)))->toBe([
        [__('Confirmation code')],
        ['KEEP0001'],
    ]);
});

it('hides the export actions from members', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::Member]));

    $event = Event::factory()->for(conferenceForm())->create();
    $registration = Registration::factory()->for($event)->create(['data' => []]);

    Livewire::test(ListRegistrations::class)
        ->assertActionHidden('export')
        ->selectTableRecords([$registration->getKey()])
        ->assertActionHidden(TestAction::make('exportSelected')->table()->bulk());
});

it('refuses to export for members even when the action is called directly', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::Member]));

    $event = Event::factory()->for(conferenceForm())->create();
    Registration::factory()->for($event)->create(['data' => []]);

    Livewire::test(ListRegistrations::class)
        ->call('mountAction', 'export', [], [])
        ->call('callMountedAction', [
            'fileName' => 'export',
            'format' => ExportFormat::Csv->value,
            'columns' => selectColumns(['confirmation_code']),
        ])
        ->assertNoFileDownloaded();
});

it('shows the export action to admins', function () {
    Livewire::test(ListRegistrations::class)
        ->assertActionVisible('export');
});

it('skips description fields when building columns', function () {
    Event::factory()->for(conferenceForm())->create();

    expect(RegistrationExporter::resolveColumns()->keys())
        ->not->toContain('field.')
        ->and(FormField::where('type', FormFieldType::Description)->count())->toBe(1);
});
