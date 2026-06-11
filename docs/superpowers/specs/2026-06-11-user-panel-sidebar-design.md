# User Panel Sidebar Revamp

## Goal

Replace the current top-nav `AppLayout` with a left sidebar navigation for all authenticated pages (user dashboard + admin panel). The Quiz Editor (create/edit) renders fullscreen, with the sidebar hidden entirely.

## Scope

- User area: Dashboard (My Quizzes), Profile, API Tokens
- Admin area: Admin Dashboard, Users, Quizzes, Games, Settings (shown only for `auth.user.is_admin`)
- Quiz Editor (`Pages/Quiz/Editor.vue`, both create and edit): fullscreen, no sidebar

## Architecture

### `Composables/useSidebar.js` (new)

Singleton ref pattern, same style as `useTheme.js`:

- `collapsed` (ref, persisted to `localStorage` key `sidebar_collapsed`) — desktop collapse state
- `mobileOpen` (ref, transient, not persisted) — mobile drawer state
- `toggleCollapsed()`, `toggleMobile()`, `closeMobile()`

### `Components/Navigation/SidebarNav.vue` (new)

Renders a list of nav sections. Each section: optional label + array of `{ href, label, icon, active }`. Collapsed mode: icon-only with `title` attribute tooltip. Active state uses `bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300` (matches current top-nav active style).

### `Components/ApplicationMark.vue`

Add `iconOnly` prop (default `false`) — when true, renders only the "Y" square SVG, hides the "ahoot" text span. Used in collapsed sidebar and mobile topbar.

### `Layouts/AppLayout.vue` (rewrite)

New prop: `fullscreen: { type: Boolean, default: false }`.

**`fullscreen = false`** (default, all normal pages):

- `<aside>` sidebar: `w-64` expanded / `w-20` collapsed (desktop, `lg:` breakpoint), `transition-[width]`, sticky full height
  - Top: `ApplicationMark` (full/icon-only based on collapsed) + collapse toggle button (chevron icon, desktop only, calls `useSidebar().toggleCollapsed()`)
  - "Menu" section (`SidebarNav`):
    - My Quizzes → `route('dashboard')`
    - Profile → `route('profile.show')`
    - API Tokens → `route('api-tokens.index')` (only if `$page.props.jetstream.hasApiFeatures`)
  - Divider + "Admin Panel" section (only if `$page.props.auth.user.is_admin`):
    - Admin Dashboard → `route('admin.dashboard')`
    - Users → `route('admin.users.index')`
    - Quizzes → `route('admin.quizzes.index')`
    - Games → `route('admin.games.index')`
    - Settings → `route('admin.settings.index')`
  - Bottom (sticky to bottom of sidebar):
    - User card: if `$page.props.jetstream.managesProfilePhotos`, show `auth.user.profile_photo_url`; otherwise a circular initial-letter avatar (first letter of name). Plus name/email; collapsed shows avatar only
    - Row: `LanguageSwitcher` + `ThemeSwitcher` (hidden when collapsed)
    - Logout button (form POST to `route('logout')`, same as current)
- Mobile (`<lg`): sidebar becomes off-canvas drawer (`-translate-x-full` ↔ `translate-x-0`, `transition-transform`), with backdrop overlay (`fixed inset-0 bg-black/50`) shown when `mobileOpen`. Closes on backdrop click or any nav link click.
- Slim mobile topbar (`lg:hidden`, `h-14`): hamburger button (toggles `mobileOpen`) + `ApplicationMark` (full).
- Main content area: existing `#header` slot rendered in a `<header>` (same `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4` wrapper as today) followed by `<main>` with `<slot />`.
- `Banner` component remains at the top, outside the sidebar/content split.

**`fullscreen = true`** (Quiz Editor):

- No `<aside>`, no mobile topbar.
- Just `Banner` + `<header>` (`#header` slot, same markup as non-fullscreen) + `<main><slot /></main>`, full width.
- `Pages/Quiz/Editor.vue`'s existing `h-[calc(100vh-4rem)]` height calc is unchanged since the header height is identical to today's top-nav layout.

### `Pages/Quiz/Editor.vue`

Add `:fullscreen="true"` to the `<AppLayout>` call. No other changes.

### Admin pages cleanup

Remove the inline `<nav class="flex items-center gap-4 text-sm">...</nav>` admin sub-nav block from the `#header` slot in:

- `Pages/Admin/Dashboard.vue`
- `Pages/Admin/Settings.vue`
- `Pages/Admin/Users/Index.vue`
- `Pages/Admin/Users/Show.vue`
- `Pages/Admin/Quizzes/Index.vue`
- `Pages/Admin/Quizzes/Show.vue`
- `Pages/Admin/Games/Index.vue`
- `Pages/Admin/Games/Show.vue`

Keep the page title (`<h2>`) in each header — only the `<nav>` of links is removed (now redundant with sidebar).

## i18n

Add to `resources/js/locales/en.json` and `id.json` under `nav`:

- `my_quizzes` ("My Quizzes" / "Kuis Saya" — reuse wording from `dashboard.title`)
- `api_tokens` ("API Tokens" / "Token API")
- `menu` ("Menu" / "Menu")
- `admin_panel` (reuse `admin.dashboard` value: "Admin Panel" / "Panel Admin")
- `collapse` ("Collapse" / "Ciutkan")
- `expand` ("Expand" / "Perluas")

Admin section nav labels reuse existing `admin.users`, `admin.quizzes`, `admin.games`, `admin.settings`, `admin.dashboard` keys.

## Testing / Verification

No JS test framework in this repo — this is a pure Vue/UI change. Verify manually via `composer run dev`:

- Desktop: sidebar visible, collapse/expand toggle works and persists across reload, active link highlight correct on dashboard/profile/admin pages
- Admin user: Admin Panel section visible with all 5 links; non-admin: section hidden
- Mobile (`<lg`): hamburger opens drawer, backdrop closes it, nav link closes it
- Quiz Editor (create + edit): fullscreen, no sidebar, existing header/save/publish controls still work
- Dark mode: sidebar, drawer, collapsed state all styled correctly
- `vendor/bin/pint --dirty --format agent` not needed (no PHP changes); run `npm run build` to confirm no build errors
