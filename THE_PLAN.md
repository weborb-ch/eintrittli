# Eintrittli v1 Implementation Plan

## Tech Stack
- **Laravel 12.48.1** + **Filament 5.1.1** + **Livewire 4.1.0**
- **PostgreSQL** database
- **Pest 4** for testing
- **Tailwind CSS 4** for styling

---

## Database Schema

### Models & Relationships

```
User (existing)
  └── hasMany → Event

Form
  ├── hasMany → FormField
  └── hasMany → Event

FormField
  └── belongsTo → Form

Event
  ├── belongsTo → Form
  └── hasMany → Registration

Registration
  └── belongsTo → Event
```

### Tables

**forms**
| Column | Type | Notes |
|--------|------|-------|
| id | bigint | PK |
| name | string | |
| description | text | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

**form_fields**
| Column | Type | Notes |
|--------|------|-------|
| id | bigint | PK |
| form_id | bigint | FK |
| type | enum | text, number, boolean, date, email, select |
| label | string | |
| name | string | snake_case field key |
| options | json | nullable, for select fields |
| is_required | boolean | default true |
| sort_order | int | |

**events**
| Column | Type | Notes |
|--------|------|-------|
| id | bigint | PK |
| form_id | bigint | FK, nullable |
| name | string | |
| slug | string | unique, URL-safe, unguessable (UUID-based) |
| registration_opens_at | datetime | nullable |
| registration_closes_at | datetime | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

**registrations**
| Column | Type | Notes |
|--------|------|-------|
| id | bigint | PK |
| event_id | bigint | FK |
| data | json | form submission data |
| confirmation_code | string | unique, for receipt |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Phase 1: Models & Migrations

1. Create migrations for `forms`, `form_fields`, `events`, `registrations`
2. Create Eloquent models with relationships
3. Add `FilamentUser` contract to User model

---

## Phase 2: Filament Admin - Form Builder

**FormResource**
- List: name, field count, event count
- Create/Edit: name, description
- **Relation Manager** for FormFields (inline repeater or table)
- **Lock editing** if any linked event has `registration_opens_at <= now() && registration_closes_at >= now()`

**FormField Types** (enum):
- `text` → TextInput
- `number` → TextInput (numeric)
- `boolean` → Toggle/Checkbox
- `date` → DatePicker
- `email` → TextInput (email)
- `select` → Select (with options JSON)

---

## Phase 3: Filament Admin - Event Resource

**EventResource**
- List: name, form, registration window, registration count, QR code action
- Create/Edit: name, select form, registration_opens_at, registration_closes_at
- **Auto-generate slug** on create using `Str::uuid()->toString()` (unguessable)
- **QR Code Action**: Generate QR code pointing to `/register/{slug}` using `simplesoftwareio/simple-qrcode` or inline SVG

**Security**: Slug is UUID-based → not enumerable, not guessable.

---

## Phase 4: Filament Admin - Registrations

**RegistrationResource**
- List: event filter, confirmation_code, created_at, data preview
- **Live polling** via Filament's `poll('5s')` on table
- **Export Action**: CSV export using Filament's built-in export or `maatwebsite/excel`
- **CRUD with confirmation modal** for delete

---

## Phase 5: Public Registration Page

**Route**: `GET /register/{event:slug}` → Livewire component

**Logic**:
1. Check if event exists and registration is open (`opens_at <= now <= closes_at`)
2. If closed/not started → show message
3. Render dynamic form based on `event->form->fields`
4. On submit → validate, create Registration with JSON data, generate confirmation_code
5. Show confirmation/receipt screen with code

**Security**:
- CSRF protection (automatic in Livewire)
- Rate limiting on submission
- Input validation based on field types
- XSS protection (Blade escaping)

---

## Phase 6: Docker Compose

```yaml
services:
  app:
    build: .
    ports: ["8000:8000"]
    depends_on: [db]
    environment:
      - DB_CONNECTION=pgsql
      - DB_HOST=db
  db:
    image: postgres:16-alpine
    volumes: [pgdata:/var/lib/postgresql/data]
    environment:
      - POSTGRES_DB=eintrittli
      - POSTGRES_USER=eintrittli
      - POSTGRES_PASSWORD=${DB_PASSWORD}
volumes:
  pgdata:
```

Plus a `Dockerfile` with PHP 8.5, Composer, Node for asset building.

---

## Implementation Order

| Step | Task | Estimated Effort |
|------|------|------------------|
| 1 | Migrations + Models | 30 min |
| 2 | FormResource + FormFieldResource | 45 min |
| 3 | EventResource + QR Code | 30 min |
| 4 | RegistrationResource + Export + Live | 45 min |
| 5 | Public Registration Livewire Page | 60 min |
| 6 | Docker Setup | 30 min |
| 7 | Tests (Feature tests for registration flow) | 45 min |

**Total: ~5 hours**

---

## Security Checklist

- UUID-based slugs (unguessable URLs)
- CSRF on all forms
- Rate limiting on public registration
- Input validation per field type
- Filament auth guards admin routes
- No mass assignment vulnerabilities (use `$fillable`)
- Escape all output (Blade default)
