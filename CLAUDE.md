# Yahoot

Real-time quiz platform (Kahoot-inspired). Laravel 12 + Vue 3 + Inertia v2 + Reverb WebSocket. Production: https://yahoot.web.id

## Commands

| Command | Purpose |
|---------|---------|
| `composer run dev` | All dev servers: Laravel + queue + Pail logs + Vite |
| `php artisan test --compact` | Run tests |
| `php artisan test --compact --filter=TestName` | Run specific test |
| `npm run build` | Build frontend assets |
| `vendor/bin/pint --dirty --format agent` | Format PHP (run after any PHP change) |

## Tech Stack

| Layer | Tech | Version |
|-------|------|---------|
| Backend | Laravel | 12 |
| PHP | PHP | 8.4 |
| Frontend | Vue 3 + Inertia | v2 |
| Styling | Tailwind CSS | 3.4 |
| Build | Vite | 7.x |
| Real-time | Laravel Reverb | WebSocket |
| Auth | Jetstream + Sanctum + Fortify | — |
| DB | PostgreSQL | 17 |
| Cache/Queue | Redis + Database driver | — |
| Testing | Pest | 4 |
| i18n | vue-i18n | 11.x |

## Architecture

### Domain Model

```
User → Quiz → Question → Answer
Quiz → GameSession → GamePlayer → PlayerAnswer
```

### Key Directories

```
app/
├── Http/Controllers/         # Quiz, Game, Player controllers
├── Http/Controllers/Admin/   # DashboardController, UserController, QuizController, GameController, SettingController
├── Http/Requests/            # Form Request validation classes
├── Models/                   # User, Quiz, Question, Answer, GameSession, GamePlayer, PlayerAnswer, AppSetting
├── Events/                   # Broadcasting events for Reverb WebSocket
├── Services/                 # GameCodeService, ScoringService, RevealService
├── Enums/                    # GameStatus, QuestionType, etc.
└── Policies/                 # QuizPolicy, GameSessionPolicy

resources/js/
├── Pages/              # Inertia pages (Landing, Dashboard, Game, Admin)
├── Pages/Admin/        # Admin panel pages (Dashboard, Users, Quizzes, Games, Settings)
├── Components/         # Vue components (Avatar, Game, Quiz, UI)
├── Layouts/            # Layout components
├── composables/        # useGame, useTimer, etc.
└── locales/            # id.json, en.json (Indonesian default)

routes/
├── web.php             # Inertia routes — public: landing/play; auth: dashboard/quiz CRUD; admin: /admin/*
├── api.php             # REST API — stateless: join/answer/status (for mobile)
└── channels.php        # WebSocket channel authorization (Reverb)

tests/
├── Unit/               # ScoringServiceTest (pure math, no DB)
└── Feature/            # All other tests including Admin/*, Game/, Policies/
```

### Laravel 12 Structure

- Middleware/exceptions registered in `bootstrap/app.php` (not `Kernel.php`)
- Service providers in `bootstrap/providers.php`
- Console commands in `app/Console/Commands/` — auto-registered, no manual registration needed

## Conventions

### Backend

- **Validation**: Form Requests always — never inline in controllers
- **DB**: Eloquent only, no `DB::` raw queries; eager-load to prevent N+1
- **URLs**: Named routes + `route()` helper
- **Config**: `config('key')` not `env()` outside config files
- **Heavy ops**: Queued jobs with `ShouldQueue`
- **PHP style**: Constructor property promotion, explicit return types, PHPDoc for arrays

### Frontend

- Path alias `@/*` → `resources/js/*`
- i18n: Indonesian (`id`) default, English fallback
- Inertia `Inertia::render()` for all server-side routing — no Blade views except `app.blade.php`
- Deferred Inertia props → add skeleton/pulse loading state

### Testing

- Pest 4 — feature tests by default, unit only when appropriate
- Factories + states for model setup — never manual attribute assignment
- Activate `pest-testing` skill for all test work
- Every change needs a test

## Game Flow

1. Host creates quiz → starts GameSession → 6-digit code + QR code
2. Players join `/play/{code}` (no auth required, choose avatar)
3. Reverb WebSocket syncs: question reveal, countdown timer, answer collection, scores
4. Scoring: speed-based points + streak bonus
5. Per-question leaderboard → final podium with confetti

## 2 Question Types

- **Multiple Choice** — 4 options
- **True/False** — 2 options

## Docker Services (dev/prod)

| Service | Port |
|---------|------|
| nginx | 8000 |
| app (PHP-FPM) | 9000 |
| postgres | 5432 |
| redis | 6379 |
| reverb (WebSocket) | 8080 |
| queue worker | — |
| scheduler | — |

## External Services

| Service | Purpose |
|---------|---------|
| IDCloudHost S3 (`https://is3.cloudhost.id`) | File storage (avatars, quiz images) |
| Brevo SMTP | Transactional email |

## Laravel Boost (MCP)

See `AGENTS.md` for full Boost/MCP tool usage rules. Key tools:
- `search-docs` — version-specific Laravel/ecosystem docs (use before making code changes)
- `tinker` — debug PHP/Eloquent
- `database-query` — read-only DB inspection
- `database-schema` — table structure before migrations
- `browser-logs` — frontend errors
- `list-artisan-commands` — available Artisan command options
- `get-absolute-url` — correct scheme/domain for any project URL
