# PHP Updates

## 1. Update Composer packages

```bash
composer update
```

This also runs automatically:

- `php artisan filament:upgrade` — refreshes Filament assets
- `php artisan vendor:publish --tag=laravel-assets --force` — publishes Laravel assets

## 2. Update Laravel Boost (AI guidelines & skills)

```bash
php artisan boost:update
```

Optional — discover new package-specific skills:

```bash
php artisan boost:update --discover
```

## 3. Run migrations

If new migrations were included in the update:

```bash
php artisan migrate
```

## 4. Verify

```bash
php artisan test
```

## Filament Blueprint (optional, requires a Filament license)

`filament/blueprint` gives AI agents detailed Filament v5 planning guidelines. It
is **proprietary**, served only from `packages.filamentphp.com` behind license
credentials, so it must never be added to `composer.json` or `composer.lock` —
this repository is public, and a licensed package in the lock file breaks
`composer install` for every contributor and for CI.

Install it outside the repository instead, once per machine:

```bash
mkdir -p ~/.local/filament-blueprint && cd ~/.local/filament-blueprint

cat > composer.json <<'JSON'
{
    "require": { "filament/blueprint": "^2.2" },
    "replace": { "filament/support": "*", "laravel/boost": "*" },
    "repositories": {
        "filament": { "type": "composer", "url": "https://packages.filamentphp.com/composer" }
    }
}
JSON

composer config --auth http-basic.packages.filamentphp.com "EMAIL" "LICENSE_KEY"
composer update
```

The `replace` block keeps the install to the one package instead of pulling a
second copy of the framework — Blueprint ships documentation, not code.

Point your agent at the guidelines with a **gitignored** skill file at
`.claude/skills/filament-blueprint/SKILL.md` (the path is already listed in
`.gitignore`) that references
`~/.local/filament-blueprint/vendor/filament/blueprint/resources/markdown/planning/overview.md`.

To refresh the guidelines later, run `composer update` in that directory.
