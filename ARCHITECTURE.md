# Architecture Guide

> For AI agents and developers working with this Laravel starter project.
> 31 PHP classes | 234 view files | 20 test files | 15 migrations

---

## Quick Orientation

This is a **Laravel 12 skeleton starter project** designed to be cloned as the foundation for new web applications. It ships with authentication, an admin panel, role-based access control, AI integration scaffolding, and a full Livewire + Flux UI frontend — all pre-wired and tested.

**Tech stack:** Laravel 12, Livewire 4, Flux UI Pro 2, Filament 5, Tailwind CSS 4, Pest 4, Fortify, Sanctum, Spatie Permission, Spatie Data, Spatie Settings, Socialite (Google OAuth), Ziggy, Intervention Image, Laravel AI SDK.

**Served by:** Laravel Herd at `https://laravel.test`.

**Key architectural decision:** Livewire components render forms; Fortify handles authentication logic server-side. There is no JavaScript framework — Alpine.js (bundled with Livewire/Flux) handles all client-side interactivity.

---

## Directory Map

```
├── app/
│   ├── Actions/Fortify/          # Auth action classes (create user, reset password, etc.)
│   ├── Concerns/                 # Shared validation traits (password, profile rules)
│   ├── Console/Commands/         # Artisan commands (debug views, test email)
│   ├── DebugViews/               # MySQL debug view framework (base class)
│   ├── Filament/Resources/       # Admin panel resources (Users, Roles, Permissions)
│   │   ├── Roles/
│   │   ├── Permissions/
│   │   └── Users/                # Full CRUD with separate Schema/Table config classes
│   ├── Http/Controllers/Auth/    # GoogleController (OAuth callback + 2FA handling)
│   ├── Livewire/Actions/         # Invokable actions (Logout)
│   ├── Mail/                     # Mailable classes (TestMail)
│   ├── Notifications/            # Notification classes (WelcomeNotification)
│   ├── Models/                   # Eloquent models (User only)
│   ├── Providers/                # Service providers (App, Fortify, AdminPanel)
│   ├── Traits/                   # Helper traits (HasEnums for DB comments)
│   └── Vendor/ArchTech/Enums/    # Vendored enum utilities (Strings trait)
│
├── bootstrap/
│   ├── app.php                   # Laravel 12 streamlined bootstrap (routes, middleware, Sentry exception reporting)
│   └── providers.php             # Registers: AppServiceProvider, AdminPanelProvider, FortifyServiceProvider
│
├── config/                       # 31 config files — notable customizations:
│   ├── auth.php                  #   Session guard, Eloquent provider
│   ├── fortify.php               #   Registration, password reset, email verify, 2FA enabled
│   ├── horizon.php               #   Queue dashboard config (published)
│   ├── livewire.php              #   Component paths: layouts::, pages:: namespaces
│   ├── permission.php            #   Spatie roles/permissions
│   ├── sentry.php                #   Error tracking config (published)
│   └── ai.php                    #   Multi-provider AI config (OpenAI, Gemini, Cohere)
│
├── database/
│   ├── factories/                # UserFactory with states: unverified, inactive, admin, withGoogleId, withTwoFactor
│   ├── migrations/               # 15 migrations (users, 2FA, permissions, AI conversations, email logs, imports/exports, notifications)
│   └── seeders/                  # DatabaseSeeder → RoleAndPermissionSeeder + 3 users (admin, editor, regular)
│
├── lang/                         # Localization (PHP 8.0–8.5 version directories + DB engine directories)
│
├── resources/
│   ├── css/
│   │   ├── app.css               # Main stylesheet — imports Tailwind, Flux, and partials below
│   │   ├── colors.css            # Theme color tokens (zinc palette, accent colors, dark mode overrides)
│   │   ├── components.css        # Flux component overrides (field layout, label, focus rings, ghost button contrast)
│   │   └── typography.css        # Font stack (Instrument Sans)
│   ├── js/
│   │   └── app.js                # Minimal — Alpine.js loaded via Flux
│   └── views/
│       ├── components/           # Blade components (app-logo, auth-header, action-message, user-menu, etc.)
│       ├── errors/               # Error pages (4xx, 5xx with minimal layout)
│       ├── flux/                 # Custom Flux component overrides (icons, navlist group)
│       ├── layouts/              # Layout hierarchy (see Layout System below)
│       ├── pages/                # Page views organized by domain
│       │   ├── auth/             # Login⚡, Register⚡, forgot-password, reset-password, verify-email, 2FA challenge
│       │   └── settings/         # Profile⚡, Password⚡, Appearance⚡, Two-Factor⚡, Delete-User⚡, Recovery-Codes⚡
│       ├── partials/             # head.blade.php (meta, fonts, Vite), settings-heading
│       ├── vendor/               # Published vendor views (Filament, Livewire, mail, pagination)
│       ├── dashboard.blade.php   # Authenticated dashboard (placeholder cards)
│       ├── mcp/authorize.blade.php # MCP OAuth authorization page
│       └── welcome.blade.php     # Public landing page
│
├── routes/
│   ├── web.php                   # Public + auth routes, Google OAuth, media library, includes settings.php
│   ├── settings.php              # Settings routes (auth group with nested verified group)
│   ├── console.php               # Scheduled commands (media cleanup)
│   └── ai.php                    # AI/MCP route configuration (scaffolded)
│
├── stubs/                        # Customized artisan make: stubs (AI agent, tool, debug-view, Filament resource stubs)
│
├── tests/
│   ├── Feature/
│   │   ├── Auth/                 # 7 test files: login, register, password reset, email verify, 2FA, password confirm, Google OAuth
│   │   ├── Filament/             # 3 test files: UserResource, RoleResource, PermissionResource
│   │   ├── Settings/             # 3 test files: profile update, password update, 2FA settings
│   │   ├── DashboardTest.php
│   │   ├── WelcomeTest.php
│   │   └── SendTestEmailTest.php
│   └── Unit/
│       └── ExampleTest.php       # Placeholder
│
├── .claude/skills/               # AI agent skills (Pennant, Flux UI, Livewire, Pest, Tailwind, AI SDK, Fortify)
├── .mcp.json                     # MCP server config (laravel-boost + herd)
├── boost.json                    # Laravel Boost config (skills, features, focused packages)
├── CLAUDE.md                     # AI agent instructions (generated by Boost, extensive)
├── pint.json                     # Code formatter (Laravel preset)
├── phpunit.xml                   # Test config (in-memory SQLite, sync queue, array cache)
└── vite.config.js                # Vite + Tailwind CSS plugin, hot reload
```

---

## Layout System

Layouts compose through Blade component inheritance:

```
Authenticated pages:
  <x-layouts::app>                    → app/sidebar.blade.php
    └── <flux:sidebar> + <flux:main>      (sidebar nav + header + content slot)

Auth pages (login, register, reset):
  #[Layout('layouts::auth')]          → auth.blade.php → auth/simple.blade.php
    └── centered card with logo           (also: auth/card.blade.php, auth/split.blade.php variants)

Public pages:
  <x-layouts::guest>                  → guest.blade.php
    └── guest/header + content + guest/footer
```

**All layouts** include `partials/head.blade.php` (meta tags, Bunny Fonts, Vite assets, `@fluxAppearance`).

**Dark mode** is the default — set via `class="dark"` on `<html>`. Users can toggle via the appearance settings page.

---

## Authentication Architecture

```
┌─────────────┐    POST     ┌──────────────────┐
│ Volt Component│ ────────→  │ Fortify Routes    │
│ (renders form)│            │ (handles logic)   │
└─────────────┘             └──────────────────┘
                                     │
                            ┌────────┴────────┐
                            │ Action Classes   │
                            │ (app/Actions/)   │
                            └─────────────────┘
```

- **Volt/Livewire pages** render forms with `wire:model` bindings
- **Forms POST** to named Fortify routes (`login.store`, `register.store`, `password.request`, etc.)
- **Fortify Action classes** implement contracts for user creation, password reset, profile updates
- **Validation traits** in `app/Concerns/` are shared across Actions and Livewire settings components
- **Google OAuth** is handled by `GoogleController` — creates/links users, respects 2FA

### Middleware layers

| Middleware         | Purpose                                    | Applied to                     |
|--------------------|--------------------------------------------|--------------------------------|
| `guest`            | Redirect authenticated users away          | Login, Register, Google OAuth  |
| `auth`             | Require authentication                     | Dashboard, Settings            |
| `verified`         | Require email verification                 | Password, Appearance, 2FA      |
| `password.confirm` | Require recent password entry              | 2FA settings (when enabled)    |

---

## Admin Panel (Filament)

Located at `/admin`. Access requires the `admin` role (checked via `canAccessPanel()` on the User model).

### Resource patterns

| Resource    | Type           | Files                              |
|-------------|----------------|------------------------------------|
| Users       | Full CRUD      | Resource, Form, Table, List/Create/Edit pages, RolesRelationManager |
| Roles       | Modal (simple) | Resource + ManageRoles page        |
| Permissions | Modal (simple) | Resource + ManagePermissions page  |

**Convention:** Full resources separate form/table config into dedicated static classes (`UserForm::configure()`, `UsersTable::configure()`) stored alongside the resource.

---

## Database Schema

**Users** — Core user table with `name`, `email`, `password`, `active` (boolean), `google_id` (nullable), 2FA columns, timestamps.

**Spatie Permission** — `roles`, `permissions`, and three pivot tables (`model_has_roles`, `model_has_permissions`, `role_has_permissions`).

**AI Conversations** — `agent_conversations` + `agent_conversation_messages` for AI agent chat history.

**Support tables** — `sessions` (DB-backed), `cache`, `jobs`/`job_batches`/`failed_jobs`, `notifications`, `imports`/`exports`/`failed_import_rows`, `temporary_uploads`, `filament_email_log`.

### Seeded data
- 12 permissions: `view|create|edit|delete` for `users`, `roles`, `permissions`
- 3 roles: `admin` (all permissions), `editor` (view/edit users + view roles/permissions), `viewer` (view only)
- 3 users: `test@example.com` (admin), `editor@example.com` (editor), `regular@example.com` (no role)
- Default password for all seeded users: `password`

---

## Testing Strategy

- **Framework:** Pest 4 (all tests are Feature tests except one Unit placeholder)
- **Database:** In-memory SQLite with `RefreshDatabase` (configured globally in `tests/Pest.php`)
- **Factories:** `UserFactory` with states: `unverified()`, `inactive()`, `admin()`, `withGoogleId()`, `withTwoFactor()`
- **Mocking:** `Notification::fake()`, `Event::fake()`, `Mail::fake()`, `Socialite::fake()`
- **Livewire:** Uses `Livewire::test()` (Pest Livewire plugin is NOT installed)
- **Filament:** Tests authenticate as admin, then use `Livewire::test()` on resource pages
- **Run command:** `art test --compact` or `--filter=testName`
- **Formatting:** Run `vendor/bin/pint --dirty --format agent` before finalizing PHP changes

### Test coverage map

| Domain          | Tests | Covers                                              |
|-----------------|-------|-----------------------------------------------------|
| Auth            | 7 files | Login, register, password reset, email verify, 2FA, password confirm, Google OAuth |
| Settings        | 3 files | Profile update, password update, 2FA management     |
| Filament Admin  | 3 files | User CRUD, roles, permissions, access control        |
| General         | 3 files | Dashboard access, welcome page, test email command   |

---

## Development Workflow

```bash
comp run setup    # First-time: install, .env, key, migrate, npm install, build
comp run dev      # Concurrent: server + queue + pail logs + vite dev
comp run test     # Clear config → lint check → run tests
comp run lint     # Pint formatter (parallel mode)
npm run build         # Production CSS/JS build
```

---

## Key Conventions to Follow

1. **File creation:** Use `art make:*` commands with `--no-interaction`. Check `list-artisan-commands` for options.
2. **Validation:** Extract to `Concerns/` traits or Form Request classes — never inline in controllers.
3. **Passwords:** Rely on the User model's `hashed` cast — do not call `Hash::make()` manually.
4. **New models:** Always create a factory and seeder alongside the model.
5. **Routes:** Use named routes and `route()` helper for URL generation.
6. **Config access:** Always `config('key')`, never `env('KEY')` outside config files.
7. **Database:** Prefer `Model::query()` over `DB::`, use eager loading to prevent N+1.
8. **Components:** Check existing Flux UI components before building custom ones.
9. **Dark mode:** All new UI must support dark mode via Tailwind's `dark:` variant.
10. **Tests:** Every change needs a test. Run only affected tests: `art test --compact --filter=TestName`.
11. **Formatting:** Run `vendor/bin/pint --dirty --format agent` after modifying PHP files.
12. **Documentation search:** Use `search-docs` MCP tool before writing code involving Laravel ecosystem packages.

---

## Filament Resource Patterns

When creating a new Filament resource, choose between two patterns:

### Full Resource (for primary domain models)

Use when the model needs dedicated list/create/edit pages and relation managers. Extract form and table config into separate classes.

**Directory structure:**
```
app/Filament/Resources/
└── ModelNames/                         # Plural directory name
    ├── ModelNameResource.php           # Main resource class
    ├── Schemas/
    │   └── ModelNameForm.php           # Static configure(Schema $schema) method
    ├── Tables/
    │   └── ModelNamesTable.php         # Static configure(Table $table) method (plural)
    ├── Pages/
    │   ├── ListModelNames.php
    │   ├── CreateModelName.php
    │   └── EditModelName.php
    └── RelationManagers/
        └── RelatedModelRelationManager.php
```

**Resource delegates to config classes:**
```php
public static function form(Schema $schema): Schema
{
    return ModelNameForm::configure($schema);
}

public static function table(Table $table): Table
{
    return ModelNamesTable::configure($table);
}
```

Reference stubs: `stubs/filament/ResourceFormSchema.stub`, `stubs/filament/ResourceTableConfig.stub`

See `app/Filament/Resources/Users/` for the complete working example.

### Modal Resource (for lookup/config tables)

Use for simple models that can be managed in a single page with modal dialogs (roles, permissions, categories, tags).

**Directory structure:**
```
app/Filament/Resources/
└── ModelNames/
    ├── ModelNameResource.php           # Form and table defined inline
    └── Pages/
        └── ManageModelNames.php        # Extends ManageRecords
```

See `app/Filament/Resources/Roles/` and `app/Filament/Resources/Permissions/` for examples.

---

## Pre-configured Infrastructure

### Horizon Queue Dashboard
Available at `/horizon`. Access gated to users with the `admin` role (configured in `AppServiceProvider`). Config published at `config/horizon.php`.

### Sentry Error Tracking
Config published at `config/sentry.php`. Exception reporting wired in `bootstrap/app.php`. Set `SENTRY_LARAVEL_DSN` in `.env` to activate.

### Spatie Health Checks
Registered in `AppServiceProvider::configureHealthChecks()`:
- **DatabaseCheck** — DB connectivity
- **CacheCheck** — cache read/write
- **UsedDiskSpaceCheck** — warn at 80%, fail at 90%
- **ScheduleCheck** — scheduler heartbeat
- **OptimizedAppCheck** — production optimization
- **DebugModeCheck** — debug mode off in production

### Notifications
Database notifications table migrated. Sample `WelcomeNotification` at `app/Notifications/WelcomeNotification.php` demonstrates `mail` + `database` channels.
