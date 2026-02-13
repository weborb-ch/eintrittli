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
