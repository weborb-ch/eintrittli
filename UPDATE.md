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
