<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Das :attribute Feld muss akzeptiert werden.',
    'accepted_if' => 'Das :attribute Feld muss akzeptiert werden, wenn :other den Wert :value hat.',
    'active_url' => 'Das :attribute Feld muss eine gültige URL sein.',
    'after' => 'Das :attribute Feld muss ein Datum nach :date sein.',
    'after_or_equal' => 'Das Feld :attribute muss ein Datum sein, das nach :date liegt oder gleich ist.',
    'alpha' => 'Das Feld :attribute darf nur Buchstaben enthalten.',
    'alpha_dash' => 'Das Feld :attribute darf nur Buchstaben, Zahlen, Bindestriche und Unterstriche enthalten.',
    'alpha_num' => 'Das Feld :attribute darf nur Buchstaben und Zahlen enthalten.',
    'any_of' => 'Das Feld :attribute ist ungültig.',
    'array' => 'Das Feld :attribute muss ein Array sein.',
    'ascii' => 'Das Feld :attribute darf nur einstellige alphanumerische Zeichen und Symbole enthalten.',
    'before' => 'Das Feld :attribute muss ein Datum vor :date sein.',
    'before_or_equal' => 'Das Feld :attribute muss ein Datum vor :date oder gleich sein.',
    'between' => [
        'array' => 'Das Feld :attribute muss zwischen :min und :max Elemente enthalten.',
        'file' => 'Das Feld :attribute muss zwischen :min und :max Kilobyte gross sein.',
        'numeric' => 'Das Feld :attribute muss zwischen :min und :max liegen.',
        'string' => 'Das Feld :attribute muss zwischen :min und :max Zeichen lang sein.',
    ],
    'boolean' => 'Das Feld :attribute muss wahr oder falsch sein.',
    'can' => 'Das Feld :attribute enthält einen nicht autorisierten Wert.',
    'confirmed' => 'Die Bestätigung des Feldes :attribute stimmt nicht überein.',
    'contains' => 'Im Feld :attribute fehlt ein erforderlicher Wert.',
    'current_password' => 'Das Passwort ist falsch.',
    'date' => 'Das Feld :attribute muss ein gültiges Datum enthalten.',
    'date_equals' => 'Das Feld :attribute muss ein Datum sein, das mit :date übereinstimmt.',
    'date_format' => 'Das Feld :attribute muss dem Format :format entsprechen.',
    'decimal' => 'Das Feld :attribute muss :decimal Dezimalstellen haben.',
    'declined' => 'Das Feld :attribute muss abgelehnt werden.',
    'declined_if' => 'Das Feld :attribute muss abgelehnt werden, wenn :other gleich :value ist.',
    'different' => 'Das Feld :attribute und :other müssen unterschiedlich sein.',
    'digits' => 'Das Feld :attribute muss :digits Stellen haben.',
    'digits_between' => 'Das Feld :attribute muss zwischen :min und :max Stellen haben.',
    'dimensions' => 'Das Feld :attribute hat ungültige Bildabmessungen.',
    'distinct' => 'Das Feld :attribute hat einen doppelten Wert.',
    'doesnt_contain' => 'Das Feld :attribute darf keinen der folgenden Werte enthalten: :values.',
    'doesnt_end_with' => 'Das Feld :attribute darf nicht mit einem der folgenden Werte enden: :values.',
    'doesnt_start_with' => 'Das Feld :attribute darf nicht mit einem der folgenden Werte beginnen: :values.',
    'email' => 'Das Feld :attribute muss eine gültige E-Mail-Adresse sein.',
    'encoding' => 'Das Feld :attribute muss in :encoding kodiert sein.',
    'ends_with' => 'Das Feld :attribute darf nicht mit einem der folgenden Zeichen enden: :values.',
    'enum' => 'Das ausgewählte :attribute ist ungültig.',
    'exists' => 'Das ausgewählte :attribute ist ungültig.',
    'extensions' => 'Das Feld :attribute muss eine der folgenden Erweiterungen haben: :values.',
    'file' => 'Das Feld :attribute muss eine Datei sein.',
    'filled' => 'Das Feld :attribute muss einen Wert haben.',
    'gt' => [
        'array' => 'Das Feld :attribute muss mehr als :value Elemente enthalten.',
        'file' => 'Das Feld :attribute muss grösser als :value Kilobyte sein.',
        'numeric' => 'Das Feld :attribute muss grösser als :value sein.',
        'string' => 'Das Feld :attribute muss mehr als :value Zeichen enthalten.',
    ],
    'gte' => [
        'array' => 'Das Feld :attribute muss :value Elemente oder mehr enthalten.',
        'file' => 'Das Feld :attribute muss grösser oder gleich :value Kilobyte sein.',
        'numeric' => 'Das Feld :attribute muss grösser oder gleich :value sein.',
        'string' => 'Das Feld :attribute muss grösser oder gleich :value Zeichen sein.',
    ],
    'hex_color' => 'Das Feld :attribute muss eine gültige Hexadezimalfarbe sein.',
    'image' => 'Das Feld :attribute muss ein Bild sein.',
    'in' => 'Das ausgewählte :attribute ist ungültig.',
    'in_array' => 'Das Feld :attribute muss in :other vorhanden sein.',
    'in_array_keys' => 'Das Feld :attribute muss mindestens einen der folgenden Schlüssel enthalten: :values.',
    'integer' => 'Das Feld :attribute muss eine ganze Zahl sein.',
    'ip' => 'Das Feld :attribute muss eine gültige IP-Adresse sein.',
    'ipv4' => 'Das Feld :attribute muss eine gültige IPv4-Adresse sein.',
    'ipv6' => 'Das Feld :attribute muss eine gültige IPv6-Adresse sein.',
    'json' => 'Das Feld :attribute muss eine gültige JSON-Zeichenkette sein.',
    'list' => 'Das Feld :attribute muss eine Liste sein.',
    'lowercase' => 'Das Feld :attribute muss aus Kleinbuchstaben bestehen.',
    'lt' => [
        'array' => 'Das Feld :attribute muss weniger als :value Elemente enthalten.',
        'file' => 'Das Feld :attribute muss kleiner als :value Kilobyte sein.',
        'numeric' => 'Das Feld :attribute muss kleiner als :value sein.',
        'string' => 'Das Feld :attribute muss weniger als :value Zeichen enthalten.',
    ],
    'lte' => [
        'array' => 'Das Feld :attribute darf nicht mehr als :value Elemente enthalten.',
        'file' => 'Das Feld :attribute muss kleiner oder gleich :value Kilobyte sein.',
        'numeric' => 'Das Feld :attribute muss kleiner oder gleich :value sein.',
        'string' => 'Das Feld :attribute darf höchstens :value Zeichen lang sein.',
    ],
    'mac_address' => 'Das Feld :attribute muss eine gültige MAC-Adresse sein.',
    'max' => [
        'array' => 'Das Feld :attribute darf nicht mehr als :max Elemente enthalten.',
        'file' => 'Das Feld :attribute darf nicht grösser als :max Kilobyte sein.',
        'numeric' => 'Das Feld :attribute darf nicht grösser als :max sein.',
        'string' => 'Das Feld :attribute darf nicht mehr als :max Zeichen enthalten.',
    ],
    'max_digits' => 'Das Feld :attribute darf nicht mehr als :max Ziffern enthalten.' ,
    'mimes' => 'Das Feld :attribute muss eine Datei vom Typ :values sein.',
    'mimetypes' => 'Das Feld :attribute muss eine Datei vom Typ :values sein.',
    'min' => [
        'array' => 'Das Feld :attribute muss mindestens :min Elemente enthalten.',
        'file' => 'Das Feld :attribute muss mindestens :min Kilobyte gross sein.',
        'numeric' => 'Das Feld :attribute muss mindestens :min gross sein.',
        'string' => 'Das Feld :attribute muss mindestens :min Zeichen lang sein.',
    ],
    'min_digits' => 'Das Feld :attribute muss mindestens :min Ziffern enthalten.',
    'missing' => 'Das Feld :attribute muss fehlen.',
    'missing_if' => 'Das Feld :attribute muss fehlen, wenn :other den Wert :value hat.',
    'missing_unless' => 'Das Feld :attribute muss fehlen, es sei denn, :other hat den Wert :value.',
    'missing_with' => 'Das Feld :attribute muss fehlen, wenn :values vorhanden ist.',
    'missing_with_all' => 'Das Feld :attribute muss fehlen, wenn :values vorhanden sind.',
    'multiple_of' => 'Das Feld :attribute muss ein Vielfaches von :value sein.',
    'not_in' => 'Das ausgewählte :attribute ist ungültig.',
    'not_regex' => 'Das Format des Feldes :attribute ist ungültig.',
    'numeric' => 'Das Feld :attribute muss eine Zahl sein.',
    'password' => [
        'letters' => 'Das Feld :attribute muss mindestens einen Buchstaben enthalten.',
        'mixed' => 'Das Feld :attribute muss mindestens einen Grossbuchstaben und einen Kleinbuchstaben enthalten.',
        'numbers' => 'Das Feld :attribute muss mindestens eine Zahl enthalten.',
        'symbols' => 'Das Feld :attribute muss mindestens ein Symbol enthalten.',
        'uncompromised' => 'Das angegebene :attribute ist in einem Datenleck aufgetaucht. Bitte wählen Sie ein anderes :attribute.',
    ],
    'present' => 'Das Feld :attribute muss vorhanden sein.',
    'present_if' => 'Das Feld :attribute muss vorhanden sein, wenn :other den Wert :value hat.',
    'present_unless' => 'Das Feld :attribute muss vorhanden sein, es sei denn, :other hat den Wert :value.',
    'present_with' => 'Das Feld :attribute muss vorhanden sein, wenn :values vorhanden ist.',
    'present_with_all' => 'Das Feld :attribute muss vorhanden sein, wenn :values vorhanden sind.',
    'prohibited' => 'Das Feld :attribute ist verboten.',
    'prohibited_if' => 'Das Feld :attribute ist verboten, wenn :other den Wert :value hat.',
    'prohibited_if_accepted' => 'Das Feld :attribute ist verboten, wenn :other akzeptiert wird.',
    'prohibited_if_declined' => 'Das Feld :attribute ist verboten, wenn :other abgelehnt wird.',
    'prohibited_unless' => 'Das Feld :attribute ist verboten, es sei denn, :other ist in :values enthalten.',
    'prohibits' => 'Das Feld :attribute verbietet das Vorhandensein von :other.',
    'regex' => 'Das Format des Feldes :attribute ist ungültig.',
    'required' => 'Das Feld :attribute ist erforderlich.',
    'required_array_keys' => 'Das Feld :attribute muss Einträge für :values enthalten.',
    'required_if' => 'Das Feld :attribute ist erforderlich, wenn :other :value ist.',
    'required_if_accepted' => 'Das Feld :attribute ist erforderlich, wenn :other akzeptiert wird.',
    'required_if_declined' => 'Das Feld :attribute ist erforderlich, wenn :other abgelehnt wird.',
    'required_unless' => 'Das Feld :attribute ist erforderlich, es sei denn, :other ist in :values enthalten.',
    'required_with' => 'Das Feld :attribute ist erforderlich, wenn :values vorhanden ist.',
    'required_with_all' => 'Das Feld :attribute ist erforderlich, wenn :values vorhanden ist.',
    'required_without' => 'Das Feld :attribute ist erforderlich, wenn :values nicht vorhanden ist.',
    'required_without_all' => 'Das Feld :attribute ist erforderlich, wenn keines der :values vorhanden ist.',
    'same' => 'Das Feld :attribute muss mit :other übereinstimmen.',
    'size' => [
        'array' => 'Das Feld :attribute muss :size Elemente enthalten.' ,
        'file' => 'Das Feld :attribute muss :size Kilobyte gross sein.',
        'numeric' => 'Das Feld :attribute muss :size sein.',
        'string' => 'Das Feld :attribute muss :size Zeichen lang sein.',
    ],
    'starts_with' => 'Das Feld :attribute muss mit einem der folgenden Zeichen beginnen: :values.',
    'string' => 'Das Feld :attribute muss eine Zeichenfolge sein.',
    'timezone' => 'Das Feld :attribute muss eine gültige Zeitzone sein.',
    'unique' => 'Das Attribut :attribute ist bereits vergeben.',
    'uploaded' => 'Das Attribut :attribute konnte nicht hochgeladen werden.',
    'uppercase' => 'Das Feld :attribute muss in Grossbuchstaben geschrieben sein.',
    'url' => 'Das Feld :attribute muss eine gültige URL sein.',
    'ulid' => 'Das Feld :attribute muss eine gültige ULID sein.',
    'uuid' => 'Das Feld :attribute muss eine gültige UUID sein.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
