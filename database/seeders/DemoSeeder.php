<?php

namespace Database\Seeders;

use App\Enums\FormFieldType;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Form;
use App\Models\FormField;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $forms = $this->seedForms();
        $events = $this->seedEvents($forms);
        $this->seedRegistrations($events);
    }

    private function seedUsers(): void
    {
        User::create([
            'username' => 'admin',
            'password' => 'admin',
            'role' => UserRole::Admin,
        ]);

        User::create([
            'username' => 'mitglied',
            'password' => 'mitglied',
            'role' => UserRole::Member,
        ]);
    }

    /**
     * @return array<string, Form>
     */
    private function seedForms(): array
    {
        // Form 1: Conference Registration (showcases all field types)
        $conferenceForm = Form::create([
            'name' => 'Konferenz-Anmeldung',
            'description' => 'Vollständiges Anmeldeformular für Konferenzen mit allen Feldtypen.',
        ]);

        $this->createFormFields($conferenceForm, [
            ['type' => FormFieldType::Text, 'name' => 'Vorname', 'is_required' => true],
            ['type' => FormFieldType::Text, 'name' => 'Nachname', 'is_required' => true],
            ['type' => FormFieldType::Email, 'name' => 'E-Mail', 'is_required' => true],
            ['type' => FormFieldType::Date, 'name' => 'Geburtsdatum', 'is_required' => false],
            ['type' => FormFieldType::Select, 'name' => 'T-Shirt Grösse', 'is_required' => true, 'options' => ['XS', 'S', 'M', 'L', 'XL', 'XXL']],
            ['type' => FormFieldType::Select, 'name' => 'Verpflegung', 'is_required' => true, 'options' => ['Fleisch', 'Vegetarisch', 'Vegan', 'Keine Präferenz']],
            ['type' => FormFieldType::Number, 'name' => 'Anzahl Begleitpersonen', 'is_required' => false],
            ['type' => FormFieldType::Boolean, 'name' => 'Newsletter abonnieren', 'is_required' => false],
        ]);

        // Form 2: Workshop Registration (simple)
        $workshopForm = Form::create([
            'name' => 'Workshop-Anmeldung',
            'description' => 'Einfaches Formular für Workshop-Anmeldungen.',
        ]);

        $this->createFormFields($workshopForm, [
            ['type' => FormFieldType::Text, 'name' => 'Name', 'is_required' => true],
            ['type' => FormFieldType::Email, 'name' => 'E-Mail', 'is_required' => true],
            ['type' => FormFieldType::Select, 'name' => 'Erfahrungslevel', 'is_required' => true, 'options' => ['Anfänger', 'Fortgeschritten', 'Experte']],
            ['type' => FormFieldType::Boolean, 'name' => 'Eigener Laptop vorhanden', 'is_required' => true],
        ]);

        // Form 3: Sports Event (with number fields)
        $sportsForm = Form::create([
            'name' => 'Sportveranstaltung',
            'description' => 'Anmeldeformular für Sportveranstaltungen mit Teilnehmerdaten.',
        ]);

        $this->createFormFields($sportsForm, [
            ['type' => FormFieldType::Text, 'name' => 'Vollständiger Name', 'is_required' => true],
            ['type' => FormFieldType::Email, 'name' => 'E-Mail', 'is_required' => true],
            ['type' => FormFieldType::Date, 'name' => 'Geburtsdatum', 'is_required' => true],
            ['type' => FormFieldType::Select, 'name' => 'Kategorie', 'is_required' => true, 'options' => ['U18', 'Erwachsene', 'Senioren']],
            ['type' => FormFieldType::Number, 'name' => 'Startnummer (falls vorhanden)', 'is_required' => false],
            ['type' => FormFieldType::Boolean, 'name' => 'Haftungsausschluss akzeptiert', 'is_required' => true],
        ]);

        // Form 4: Minimal Contact Form
        $contactForm = Form::create([
            'name' => 'Kontaktformular',
            'description' => 'Minimales Kontaktformular.',
        ]);

        $this->createFormFields($contactForm, [
            ['type' => FormFieldType::Text, 'name' => 'Name', 'is_required' => true],
            ['type' => FormFieldType::Email, 'name' => 'E-Mail', 'is_required' => true],
        ]);

        return [
            'conference' => $conferenceForm,
            'workshop' => $workshopForm,
            'sports' => $sportsForm,
            'contact' => $contactForm,
        ];
    }

    /**
     * @param  array<int, array{type: FormFieldType, name: string, is_required: bool, options?: array<int, string>}>  $fields
     */
    private function createFormFields(Form $form, array $fields): void
    {
        foreach ($fields as $index => $field) {
            FormField::create([
                'form_id' => $form->id,
                'type' => $field['type'],
                'name' => $field['name'],
                'is_required' => $field['is_required'],
                'options' => $field['options'] ?? null,
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * @param  array<string, Form>  $forms
     * @return array<string, Event>
     */
    private function seedEvents(array $forms): array
    {
        $now = now();

        // Event 1: Active conference (registration open)
        $techConference = Event::create([
            'name' => 'Tech Summit 2026',
            'form_id' => $forms['conference']->id,
            'code' => 'tech26',
            'registration_opens_at' => $now->copy()->subDays(7),
            'registration_closes_at' => $now->copy()->addDays(30),
        ]);

        // Event 2: Workshop starting soon
        $phpWorkshop = Event::create([
            'name' => 'PHP Workshop für Einsteiger',
            'form_id' => $forms['workshop']->id,
            'code' => 'php101',
            'registration_opens_at' => $now->copy()->subDays(14),
            'registration_closes_at' => $now->copy()->addDays(7),
        ]);

        // Event 3: Sports event with many registrations
        $marathon = Event::create([
            'name' => 'Stadtlauf 2026',
            'form_id' => $forms['sports']->id,
            'code' => 'run26',
            'registration_opens_at' => $now->copy()->subMonth(),
            'registration_closes_at' => $now->copy()->addDays(60),
        ]);

        // Event 4: Future event (registration not yet open)
        $futureEvent = Event::create([
            'name' => 'Sommerfest 2026',
            'form_id' => $forms['conference']->id,
            'code' => 'summer',
            'registration_opens_at' => $now->copy()->addDays(30),
            'registration_closes_at' => $now->copy()->addDays(90),
        ]);

        // Event 5: Past event (registration closed)
        $pastEvent = Event::create([
            'name' => 'Winterkonferenz 2025',
            'form_id' => $forms['conference']->id,
            'code' => 'winter',
            'registration_opens_at' => $now->copy()->subMonths(3),
            'registration_closes_at' => $now->copy()->subMonth(),
        ]);

        // Event 7: Event with no time restrictions
        $openEvent = Event::create([
            'name' => 'Offene Mitgliederversammlung',
            'form_id' => $forms['contact']->id,
            'code' => 'mitgli',
            'registration_opens_at' => null,
            'registration_closes_at' => null,
        ]);

        return [
            'tech_conference' => $techConference,
            'php_workshop' => $phpWorkshop,
            'marathon' => $marathon,
            'future_event' => $futureEvent,
            'past_event' => $pastEvent,
            'open_event' => $openEvent,
        ];
    }

    /**
     * @param  array<string, Event>  $events
     */
    private function seedRegistrations(array $events): void
    {
        $this->seedConferenceRegistrations($events['tech_conference']);
        $this->seedWorkshopRegistrations($events['php_workshop']);
        $this->seedMarathonRegistrations($events['marathon']);
        $this->seedPastEventRegistrations($events['past_event']);
        $this->seedOpenEventRegistrations($events['open_event']);
    }

    private function seedConferenceRegistrations(Event $event): void
    {
        $registrations = [
            [
                'Vorname' => 'Anna',
                'Nachname' => 'Müller',
                'E-Mail' => 'anna.mueller@example.com',
                'Geburtsdatum' => '1990-05-15',
                'T-Shirt Grösse' => 'M',
                'Verpflegung' => 'Vegetarisch',
                'Anzahl Begleitpersonen' => 1,
                'Newsletter abonnieren' => true,
            ],
            [
                'Vorname' => 'Max',
                'Nachname' => 'Schmidt',
                'E-Mail' => 'max.schmidt@example.com',
                'Geburtsdatum' => '1985-11-22',
                'T-Shirt Grösse' => 'L',
                'Verpflegung' => 'Fleisch',
                'Anzahl Begleitpersonen' => 0,
                'Newsletter abonnieren' => false,
            ],
            [
                'Vorname' => 'Lisa',
                'Nachname' => 'Weber',
                'E-Mail' => 'lisa.weber@example.com',
                'Geburtsdatum' => '1995-03-08',
                'T-Shirt Grösse' => 'S',
                'Verpflegung' => 'Vegan',
                'Anzahl Begleitpersonen' => 2,
                'Newsletter abonnieren' => true,
            ],
            [
                'Vorname' => 'Thomas',
                'Nachname' => 'Brunner',
                'E-Mail' => 'thomas.brunner@example.com',
                'Geburtsdatum' => '1978-07-30',
                'T-Shirt Grösse' => 'XL',
                'Verpflegung' => 'Keine Präferenz',
                'Anzahl Begleitpersonen' => 1,
                'Newsletter abonnieren' => true,
            ],
            [
                'Vorname' => 'Sarah',
                'Nachname' => 'Keller',
                'E-Mail' => 'sarah.keller@example.com',
                'Geburtsdatum' => '1992-12-01',
                'T-Shirt Grösse' => 'XS',
                'Verpflegung' => 'Vegetarisch',
                'Anzahl Begleitpersonen' => 0,
                'Newsletter abonnieren' => false,
            ],
        ];

        foreach ($registrations as $data) {
            Registration::create([
                'event_id' => $event->id,
                'data' => $data,
                'notes' => fake()->optional(0.3)->sentence(),
            ]);
        }
    }

    private function seedWorkshopRegistrations(Event $event): void
    {
        $registrations = [
            [
                'Name' => 'Peter Meier',
                'E-Mail' => 'peter.meier@example.com',
                'Erfahrungslevel' => 'Anfänger',
                'Eigener Laptop vorhanden' => true,
            ],
            [
                'Name' => 'Julia Huber',
                'E-Mail' => 'julia.huber@example.com',
                'Erfahrungslevel' => 'Fortgeschritten',
                'Eigener Laptop vorhanden' => true,
            ],
            [
                'Name' => 'Michael Steiner',
                'E-Mail' => 'michael.steiner@example.com',
                'Erfahrungslevel' => 'Anfänger',
                'Eigener Laptop vorhanden' => false,
            ],
        ];

        foreach ($registrations as $data) {
            Registration::create([
                'event_id' => $event->id,
                'data' => $data,
            ]);
        }
    }

    private function seedMarathonRegistrations(Event $event): void
    {
        $participants = [
            ['name' => 'Felix Bauer', 'email' => 'felix.bauer@example.com', 'birth' => '2008-04-12', 'category' => 'U18'],
            ['name' => 'Claudia Roth', 'email' => 'claudia.roth@example.com', 'birth' => '1988-09-25', 'category' => 'Erwachsene'],
            ['name' => 'Hans Zimmermann', 'email' => 'hans.zimmermann@example.com', 'birth' => '1965-02-14', 'category' => 'Senioren'],
            ['name' => 'Nina Fischer', 'email' => 'nina.fischer@example.com', 'birth' => '1992-06-30', 'category' => 'Erwachsene'],
            ['name' => 'David Hofmann', 'email' => 'david.hofmann@example.com', 'birth' => '1980-11-08', 'category' => 'Erwachsene'],
            ['name' => 'Emma Schneider', 'email' => 'emma.schneider@example.com', 'birth' => '2007-01-20', 'category' => 'U18'],
            ['name' => 'Robert Wagner', 'email' => 'robert.wagner@example.com', 'birth' => '1958-08-05', 'category' => 'Senioren'],
            ['name' => 'Laura Berger', 'email' => 'laura.berger@example.com', 'birth' => '1995-12-18', 'category' => 'Erwachsene'],
            ['name' => 'Stefan Moser', 'email' => 'stefan.moser@example.com', 'birth' => '1975-04-22', 'category' => 'Erwachsene'],
            ['name' => 'Martina Frei', 'email' => 'martina.frei@example.com', 'birth' => '1990-07-11', 'category' => 'Erwachsene'],
            ['name' => 'Andreas Keller', 'email' => 'andreas.keller@example.com', 'birth' => '1982-03-29', 'category' => 'Erwachsene'],
            ['name' => 'Sandra Lehmann', 'email' => 'sandra.lehmann@example.com', 'birth' => '1998-10-03', 'category' => 'Erwachsene'],
        ];

        foreach ($participants as $index => $p) {
            Registration::create([
                'event_id' => $event->id,
                'data' => [
                    'Vollständiger Name' => $p['name'],
                    'E-Mail' => $p['email'],
                    'Geburtsdatum' => $p['birth'],
                    'Kategorie' => $p['category'],
                    'Startnummer (falls vorhanden)' => $index < 5 ? 100 + $index : null,
                    'Haftungsausschluss akzeptiert' => true,
                ],
            ]);
        }
    }

    private function seedPastEventRegistrations(Event $event): void
    {
        $registrations = [
            [
                'Vorname' => 'Christine',
                'Nachname' => 'Gerber',
                'E-Mail' => 'christine.gerber@example.com',
                'Geburtsdatum' => '1987-06-14',
                'T-Shirt Grösse' => 'M',
                'Verpflegung' => 'Vegetarisch',
                'Anzahl Begleitpersonen' => 0,
                'Newsletter abonnieren' => true,
            ],
            [
                'Vorname' => 'Markus',
                'Nachname' => 'Wyss',
                'E-Mail' => 'markus.wyss@example.com',
                'Geburtsdatum' => '1979-09-28',
                'T-Shirt Grösse' => 'L',
                'Verpflegung' => 'Fleisch',
                'Anzahl Begleitpersonen' => 1,
                'Newsletter abonnieren' => false,
            ],
        ];

        foreach ($registrations as $data) {
            Registration::create([
                'event_id' => $event->id,
                'data' => $data,
            ]);
        }
    }

    private function seedOpenEventRegistrations(Event $event): void
    {
        $registrations = [
            ['Name' => 'Ursula Marti', 'E-Mail' => 'ursula.marti@example.com'],
            ['Name' => 'Beat Schwarz', 'E-Mail' => 'beat.schwarz@example.com'],
            ['Name' => 'Monika Egger', 'E-Mail' => 'monika.egger@example.com'],
        ];

        foreach ($registrations as $data) {
            Registration::create([
                'event_id' => $event->id,
                'data' => $data,
            ]);
        }
    }
}
