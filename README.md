# Eintrittli

A simple event registration system built with Laravel and Filament.

## Deployment

The application runs on port 8000 and is designed to be placed behind a reverse proxy (e.g., Caddy, Nginx, Traefik).

### Quick Start

1. Create a `.env` file with required variables:

```bash
APP_KEY=base64:... # Generate with: docker run --rm -it ghcr.io/weborb-ch/eintrittli php artisan key:generate --show
APP_URL=https://your-domain.com
DB_PASSWORD=your-secure-password
```

2. Start the application:

```bash
docker compose up -d
```

3. Configure your reverse proxy to forward traffic to `127.0.0.1:8000`.

### Reverse Proxy Examples

**Caddy** (recommended):
```
your-domain.com {
    reverse_proxy 127.0.0.1:8000
}
```

**Nginx**:
```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### Environment Variables

| Variable | Required | Description |
|----------|----------|-------------|
| `APP_KEY` | Yes | Laravel application key |
| `APP_URL` | Yes | Public URL of the application |
| `DB_PASSWORD` | Yes | PostgreSQL password |

### Building Locally

To build the image locally instead of using the published one:

```bash
docker compose build
# Or uncomment the build line in docker-compose.yml
```

## Development

```bash
composer setup   # Install dependencies and setup
composer dev     # Start development server
composer test    # Run tests
```

## Todo

- [x] Remove email from user completely and use username for everything
- [x] Use Filament components instead of native ones everywhere
- [x] Change register URL to the event slug + a small generated code
- [x] Fix csv export errors
- [ ] Add registered date to confirmation screen
