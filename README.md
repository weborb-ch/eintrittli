<p align="center">
    <picture>
        <source media="(prefers-color-scheme: dark)" srcset="public/assets/logo-white.png">
        <source media="(prefers-color-scheme: light)" srcset="public/assets/logo-black.png">
        <img alt="Eintrittli - Einfaches Event-Registrierungssystem" src="public/assets/logo-white.png">
    </picture>
</p>

# Eintrittli

Ein einfaches Event-Registrierungssystem mit konfigurierbaren Formularen und CSV Exports. Kein Bezahlungssystem und kein Login für eine Registrierung. Moderne Admin-Konsole für die Verwaltung und live Ansicht der neuen Registrierungen.

## Funktionen

- 📝 Konfigurierbare Formulare
- 🎉 Konfigurierbare Events mit Start- und Enddatum und Formular
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
| `DB_HOST` | Ja | PostgreSQL Passwort|
| `DB_PORT` | Ja | PostgreSQL Passwort|
| `DB_DATABASE` | Ja | PostgreSQL Passwort|
| `DB_USERNAME` | Ja | PostgreSQL Passwort|
| `DB_PASSWORD` | Ja | PostgreSQL Passwort|
