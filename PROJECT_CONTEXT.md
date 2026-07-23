# Project Context: LudoTest

## Overview
LudoTest is a board game database and review toy app built with Laravel 12 and React (Inertia). It fetches and synchronizes game data from external providers, starting with BoardGameGeek (BGG). The `react` branch is the mainline; `main` is stale. This app is designed for Toy Libraries and their umbrella association to share informations about their feedback on each game specifically linked to a toy library environment. Futur iterations will allow specific users to add their own games, allow moderations from the admins. Extend the model to include game shops and game clubs. An additionnal goal of this project is to synthesize the main remarks from different users writing in different languages to a single paragraph available in all of these languages.

## Access model
- **Login-only:** All web routes use `auth` middleware. Guests are redirected to login.
- **No email verification:** Fortify email verification is disabled for this toy deployment.
- **Admin role:** Assign the `admin` role via `role_user` (e.g. `php artisan tinker` → attach admin role to a user). There is no `/admin-test` route in production.

## Key Technologies
- **Backend:** Laravel 12, PHP 8.2+
- **Frontend:** React, TypeScript, Inertia.js, Tailwind, Shadcn/UI
- **Database:** SQLite (default for Docker/local), pgsql on prod
- **External APIs:** BoardGameGeek (BGG), optional `stub` demo provider

## Core Architecture
### Multi-Source System
- `App\Contracts\GameProviderInterface`: Contract for external data providers.
- `App\Providers\GameSources\BggProvider`: BGG fetch/search.
- `App\Providers\GameSources\StubProvider`: Minimal demo provider (`stub` slug).
- `App\Services\GameService`: Provider resolution, unified search, `ensureGameInDb`, persistence.

### Search badges
- `is_local` on API results means the game exists in the **app database** (cached/synced), not the user's personal library.
- `in_user_library` / `list_types` reflect the authenticated user's owned/wishlist entries.

### User library
- `library_entries` table: `user_id`, `game_id`, `list_type` (`owned` | `wishlist`).
- API: `GET/POST /api/library`, `DELETE /api/library/{id}`.

### Background sync
- `App\Jobs\SyncGameFromSource`: Re-fetches a single `GameSource`.
- `php artisan games:sync-stale`: Syncs sources with `last_sync_at` older than 7 days.
- Scheduled daily via `routes/console.php` — run `php artisan schedule:run` in production (cron).

## Data Model
- `Game`, `Source`, `GameSource`, `Language`, `LanguageMapping`, `GameRole`
- `TranslationKey` / `Translation` for multi-language text
- `Comment`, `Answer`, `Question` for social features
- `Role` / `role_user` for authorization
- `LibraryEntry` for per-user collections

## Deployment (Docker)
Files in repo root: `Dockerfile`, `docker/entrypoint.sh`, `docker-compose.yml`, optional `docker-compose.immich.yml`.

- `docker compose up` is the **production** stack: app + nginx + postgres + redis + queue.
- Secrets only: `LUDOTEST_APP_KEY`, `LUDOTEST_DB_PASSWORD` (see `.env.docker.example`).
- Optional: `docker-compose.immich.yml` reuses Immich Postgres/Valkey (separate `ludotest` DB).
- Local development: `composer dev` on the host (not Docker).
- Multi-stage build: Composer + npm build, PHP-FPM + nginx runtime.
- Extensions: `pdo_pgsql`, `curl`, `gd`, etc.
- **Entrypoint**: waits for DB when `WAIT_FOR_DB=true`, runs migrations when `RUN_MIGRATIONS=true`, optional cron.
- Production `DatabaseSeeder` seeds roles, languages, sources only. Test users (`test0@example.com` / `password`) are created by `LocalDevelopmentSeeder` when `APP_ENV=local`.

## API rate limits
- Search: 30 requests/minute per user or IP.
- Registration: 5 requests/minute per IP.

## Key Files
- `app/Services/GameService.php`
- `app/Http/Controllers/GameController.php`
- `app/Http/Resources/SearchResultResource.php`
- `resources/js/pages/search.tsx`, `welcome.tsx`, `dashboard.tsx`
- `routes/api.php`, `routes/web.php`

## Pending / future
- Real translation API (replace mock `lt_translate`)
- Advanced search filter UI
- True server-side pagination for external providers
