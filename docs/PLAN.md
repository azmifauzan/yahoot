# Yahoot — Implementation Plan (Gap Analysis)

**Date:** 2026-05-24  
**Completed:** 2026-05-24  
**Source:** Gap analysis between PRD and current codebase

All items below have been implemented. See CURRENT-STATUS.md for full details.

---

## Priority 1 — Admin Panel (Phase 5, Core)

The admin panel is completely absent. Zero controllers, zero pages, zero routes.

### 1.1 Admin Routes

Add to `routes/web.php` under `EnsureUserIsAdmin` middleware:

```
GET  /admin                     → Admin\DashboardController@index
GET  /admin/users               → Admin\UserController@index
GET  /admin/users/{user}        → Admin\UserController@show
PUT  /admin/users/{user}        → Admin\UserController@update   (toggle admin, deactivate)
DELETE /admin/users/{user}      → Admin\UserController@destroy
GET  /admin/quizzes             → Admin\QuizController@index
GET  /admin/quizzes/{quiz}      → Admin\QuizController@show
DELETE /admin/quizzes/{quiz}    → Admin\QuizController@destroy
POST /admin/quizzes/{quiz}/restore → Admin\QuizController@restore
GET  /admin/games               → Admin\GameController@index
GET  /admin/games/{session}     → Admin\GameController@show
DELETE /admin/games/{session}   → Admin\GameController@destroy
GET  /admin/settings            → Admin\SettingController@index
PUT  /admin/settings            → Admin\SettingController@update
```

### 1.2 Backend — Controllers to Create

**`app/Http/Controllers/Admin/DashboardController.php`**
- Stats: total users, total quizzes (active/draft/deleted), total game sessions (today/month/total), total players (registered/guest)
- Activity chart data: game sessions per day, last 30 days
- Recent game sessions list

**`app/Http/Controllers/Admin/UserController.php`**
- `index`: paginated list with search + filter, columns: avatar, name, email, quizzes created, games played, registered at, status
- `show`: user profile + quiz list + game history
- `update`: toggle is_admin, deactivate (add `is_active` column or soft delete)
- `destroy`: delete user

**`app/Http/Controllers/Admin/QuizController.php`**
- `index`: all quizzes (all users), filter: public/private/published/draft/trashed
- `show`: quiz detail with questions
- `destroy`: force delete
- `restore`: restore soft-deleted

**`app/Http/Controllers/Admin/GameController.php`**
- `index`: all game sessions, filter: status + date range
- `show`: game session detail with players + answers
- `destroy`: delete game session

**`app/Http/Controllers/Admin/SettingController.php`**
- `index`/`update`: app name, default language, toggle registration, email verification, guest play, maintenance mode
- Store settings in config or a `settings` table (recommend `app_settings` table with key/value)

### 1.3 Frontend — Pages to Create

```
resources/js/Pages/Admin/
├── Dashboard.vue          # Stats cards + activity chart + recent games
├── Users/
│   ├── Index.vue          # Paginated table with search/filter + actions
│   └── Show.vue           # User profile + quiz list + game history
├── Quizzes/
│   ├── Index.vue          # All quizzes table with filter
│   └── Show.vue           # Quiz detail with questions list
├── Games/
│   ├── Index.vue          # All game sessions table
│   └── Show.vue           # Game session detail with player answers
└── Settings.vue           # System settings form
```

### 1.4 Admin Dashboard Stats Queries

```php
// Example stats needed in DashboardController
User::count()                                    // total users
Quiz::withTrashed()->count()                     // total quizzes
Quiz::count()                                    // active
Quiz::onlyTrashed()->count()                     // deleted
GameSession::whereDate('created_at', today())->count()  // today
GameSession::whereMonth('created_at', now())->count()   // this month
GamePlayer::whereNull('user_id')->count()        // guest players

// Activity chart (last 30 days)
GameSession::selectRaw('DATE(created_at) as date, COUNT(*) as count')
    ->where('created_at', '>=', now()->subDays(30))
    ->groupBy('date')
    ->orderBy('date')
    ->get()
```

### 1.5 Admin Tests to Create

```
tests/Feature/Admin/
├── AdminDashboardTest.php
├── AdminUserManagementTest.php
├── AdminQuizManagementTest.php
└── AdminGameManagementTest.php
```

Each test must cover:
- Admin can access (200)
- Non-admin gets 403
- Guest gets redirect to login

---

## Priority 2 — Player Final Stats (Phase 4, Feature Gap)

PRD §6.3.11 requires player personal stats at game end. Current `Player/Game.vue` `finished` state only shows rank + score + back button. Missing:

- "Jawaban benar: X/Y" (correct answers out of total)
- "Streak terpanjang" (longest streak)
- "Rata-rata waktu menjawab" (average time per answer)

### Fix

The backend already computes these in `GameSessionController@results` (`playerStats`). Need to expose them via WebSocket on `GameEnded` event or via a dedicated API call.

**Option A (recommended):** Add player-specific stats to `GameEnded` event payload:

```php
// In GameEnded event, include per-player stats
'playerStats' => $this->scoringService->getPlayerStats($gameSession->id)
```

**Option B:** Add API endpoint `GET /api/games/{session}/my-stats?player_id=X` that player polls after game ends.

Then update `Player/Game.vue` `finished` state to display:
```html
<p>{{ myFinalRank.correct_answers }} / {{ myFinalRank.total_questions }} correct</p>
<p>Longest streak: {{ myFinalRank.longest_streak }}</p>
<p>Avg time: {{ (myFinalRank.avg_time / 1000).toFixed(1) }}s</p>
```

---

## Priority 3 — "Play Again" Button (Phase 4, Minor Gap)

PRD §6.3.10 requires "Main Lagi" (restart same quiz) button on the final leaderboard.

Current `Host/Results.vue` has Download CSV + Finish. Missing "Play Again".

### Fix

Add to `Host/Results.vue` actions:
```html
<button @click="playAgain">🔄 Main Lagi</button>
```

```js
function playAgain() {
    router.post(route('game.store', quiz.id));
}
```

Also add to `Host/Game.vue` `finished` state actions (currently only has Download CSV + Finish dashboard button).

---

## Priority 4 — Missing Shared Components (Tech Debt)

PRD §9 specifies these as reusable components. Currently inline in pages. Extracting would improve maintainability and testability. Not blocking, but recommended before launch.

| Component to Create | Extract From | Purpose |
|--------------------|--------------|---------|
| `Components/Game/TimerBar.vue` | Host/Game.vue + Player/Game.vue | Reusable color-shifting progress bar |
| `Components/Game/CountdownOverlay.vue` | Both game pages | 3-2-1-START! fullscreen overlay |
| `Components/Game/StreakBadge.vue` | Player/Game.vue | 🔥 streak counter badge |
| `Components/Game/ScoreAnimation.vue` | Player/Game.vue | Animated +points reveal |
| `Components/Game/GameCodeInput.vue` | Player/Join.vue | 6-digit code input |
| `Layouts/GameLayout.vue` | — | Fullscreen layout wrapper (no navbar) |

---

## Priority 5 — Sound Effects (Optional, Phase 4)

PRD §6.3.6/§6.3.10 mentions sound effects (benar: "ding!", musik kemenangan). Marked optional in PRD Phase 4.

Create `resources/js/Composables/useSound.js`:
```js
export function useSound() {
    function play(name) { /* load + play audio */ }
    return { play }
}
```

Sounds needed: correct answer, wrong answer, countdown tick, game start, podium reveal, lobby background music.

---

## Priority 6 — GameSessionPolicy (Security Gap)

PRD §14 lists `GameSessionPolicy` as required. Currently authorization is done with inline `if ($session->host_id !== auth()->id()) abort(403)` checks in `GameSessionController`.

Create `app/Policies/GameSessionPolicy.php` and register it. Move authorization logic from controller to policy methods (`view`, `start`, `next`, `reveal`, `end`, `results`, `export`).

---

## Priority 7 — Performance & Responsive (Phase 4)

### Responsive Testing (not done)
- Test all game views on 375px (iPhone SE) and 768px (tablet)
- Host game: min 768px recommended per PRD — add `md:` breakpoint warnings or redirect on small screens
- Player game: must work on 375px (mobile-first)

### Performance (not done)
- Eager loading already in place (good)
- Redis game session caching — `GameSession` state could be cached during active game
- Vite code splitting is default, but verify bundle sizes
- Add `webp` format to image upload pipeline (currently accepts jpg/png/gif/webp but doesn't convert)

---

## Priority 8 — E2E & Integration Tests (Phase 5)

No E2E tests exist. Recommended before launch:

```
tests/Feature/Game/
├── FullGameFlowTest.php        # Host creates session → player joins → game plays through → ends
├── AdminAccessControlTest.php  # All admin routes return 403 for non-admin
```

Tools: Pest feature tests with full HTTP simulation (no browser needed for most). For real WebSocket E2E, would need Dusk or Playwright (out of scope for v1).

---

## Priority 9 — Deployment Checklist (Phase 5)

Required before launch:
- [ ] Run `php artisan test --compact` — all green
- [ ] `npm run build` — no errors
- [ ] Docker Compose prod config verified
- [ ] `.env.example` matches all required env vars
- [ ] Reverb WebSocket accessible on port 8080
- [ ] S3 bucket `yahoot` writable
- [ ] SSL via Let's Encrypt active
- [ ] `php artisan config:cache && route:cache && view:cache` in deploy script

---

## Implementation Order

| Order | Item | Effort | Impact |
|-------|------|--------|--------|
| 1 | Admin Panel (§1.1–1.4) | Large (3-5 days) | Required for launch |
| 2 | Player final stats (§2) | Small (2-4h) | PRD compliance |
| 3 | Play Again button (§3) | Tiny (30min) | PRD compliance |
| 4 | GameSessionPolicy (§6) | Small (2h) | Security hygiene |
| 5 | Admin tests (§1.5) | Medium (1 day) | Test coverage |
| 6 | E2E tests (§8) | Medium (1 day) | Launch confidence |
| 7 | Shared components (§4) | Medium (1 day) | Maintainability |
| 8 | Responsive testing (§7) | Small (4h) | UX |
| 9 | Sound effects (§5) | Small (4h) | Optional |
| 10 | Performance (§7) | Small (4h) | Production readiness |
