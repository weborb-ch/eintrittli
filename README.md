<p align="center">
    <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://github.com/weborb-ch/eintrittli/blob/14b048a360f0f9327706ae28a186cecee3d46e2d/public/assets/banner_white.png?raw=true">
        <source media="(prefers-color-scheme: light)" srcset="https://github.com/weborb-ch/eintrittli/blob/14b048a360f0f9327706ae28a186cecee3d46e2d/public/assets/banner_black.png?raw=true">
        <img alt="Eintrittli - Einfaches Anlass-Registrierungssystem" src="https://github.com/weborb-ch/eintrittli/blob/14b048a360f0f9327706ae28a186cecee3d46e2d/public/assets/banner_white.png?raw=true" width="400">
    </picture>
</p>

# Eintrittli

Ein einfaches Anlass-Registrierungssystem mit konfigurierbaren Formularen und CSV Exports. Kein Bezahlungssystem und kein Login für eine Registrierung. Moderne Admin-Konsole für die Verwaltung und live Ansicht der neuen Registrierungen.

## Funktionen

- 📝 Konfigurierbare Formulare
- 🎉 Konfigurierbare Anlässe mit Start- und Enddatum und Formular
- 📱 QR-Code / Link für Registrierungen
- 🔴 Live Ansicht der Registrierungen
- 📊 CSV Export der Registrierungen

## Selber Hosten

Ein Beispiel kann in [docker-compose.yml](docker-compose.yml) gefunden werden.

Die Anwendung läuft auf Port 8000.

### Umgebungsvariablen

| Variable | Erforderlich | Beschreibung |
|----------|----------|-------------|
| `APP_KEY` | Ja | Base64 Schlüssel |
| `APP_URL` | Ja | Öffentlicher URL der Applikation |
| `DB_HOST` | Ja | PostgreSQL Hostname |
| `DB_PORT` | Ja | PostgreSQL Port (default: 5432) |
| `DB_DATABASE` | Ja | PostgreSQL Datenbank |
| `DB_USERNAME` | Ja | PostgreSQL Nutzer |
| `DB_PASSWORD` | Ja | PostgreSQL Passwort|

## Demo-Instanz

Eine Demo-Instanz setzt `IS_DEMO=true` (deaktiviert das Bearbeiten des Profils
und der Demo-Benutzer) und wird jeden Sonntag um 03:00 UTC per Laravel Scheduler
zurückgesetzt: `demo:reset` löscht die Datenbank und seedet den `DemoSeeder`.
Der Zeitplan steht in [routes/console.php](routes/console.php); ohne
`IS_DEMO=true` wird die Aufgabe übersprungen.

### Scheduler auf Laravel Cloud einrichten

1. In der Umgebung `IS_DEMO=true` als Umgebungsvariable setzen.
2. Im Infrastructure-Canvas auf den **App compute cluster** klicken, den
   **Scheduler**-Toggle aktivieren und speichern.
3. Die Umgebung neu deployen. Danach läuft `schedule:run` jede Minute.
4. Falls `php artisan demo:reset --force` noch in den Deployment-Befehlen der
   Umgebung steht: entfernen. Sonst wird die Demo bei jedem Deployment geleert.

Hinweise:

- **Scale to Zero**: Eine schlafende Umgebung wacht für geplante Aufgaben
  automatisch auf. Laravel Cloud liest die Zeitpläne beim Deployment aus
  `php artisan schedule:list` — Änderungen am Zeitplan greifen erst nach dem
  nächsten Deployment.
- Nach dem Aufwachen bleibt die Umgebung für die Dauer des Sleep-Timeouts wach;
  der Reset muss in diesem Fenster fertig werden.
