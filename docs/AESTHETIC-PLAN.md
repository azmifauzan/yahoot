# Yahoot — Aesthetic Improvement Plan

**Date:** 2026-06-04
**Scope:** Visual polish only (no new features). Make the app look like a playful quiz platform, not a generic grayscale SaaS.

---

## Audit Findings

### Core problem: brand palette is defined but never used

`tailwind.config.js:23-43` defines a full brand palette that **zero components use**:

```js
primary: { 50…950 }   // #6C5CE7 purple — 0 occurrences in resources/js
accent:  { red, blue, yellow, green }  // 0 occurrences
```

Instead, three conflicting ad-hoc palettes coexist:

| Palette | Where | Count |
|---------|-------|-------|
| Grayscale (`gray-900`/`white`) | Landing, Dashboard, Admin, AppLayout | ~97 across 37 files |
| Raw `indigo/purple/pink` | Player/Join, Host/Game, Player/Game, Quiz Editor | 46 across 17 files |
| Brand `primary`/`accent` (config) | — | **0** |

Result: marketing + creator surfaces (Landing, Dashboard, Admin) feel cold/corporate grayscale; game surfaces (Join, Host, Player) are colorful but use raw `indigo-600` not the brand token. No single source of truth for color.

### Secondary findings

- **Single font.** `tailwind.config.js:21` registers only Inter. No display/heading font — a quiz app reads flat without playful type.
- **Dead code.** `Landing.vue:162-188` — entire Features section commented out, but `features` array still declared (`:23-28`).
- **Plain logo.** `Landing.vue:52-56` — logo is literally a "Y" glyph in a rounded box. No character/motion.
- **Flat hero.** `Landing.vue:104` hero is plain white, no gradient/glow/decorative shapes despite vibrant brand colors available.
- **Grayscale quiz placeholders.** `Dashboard.vue:78-85` — quiz cover fallbacks are all `from-gray-*` gradients. Misses chance to inject brand color into the most-viewed creator surface.
- **Animation library underused.** `app.css` has 15+ keyframes (ripple, bounce-in, pulse-glow, etc.) + `prefers-reduced-motion` guard. Need to verify they're wired (e.g. `ripple-effect` on buttons) vs. defined-but-idle.

---

## Plan

### Priority 1 — Unify on brand palette (highest impact, mechanical)

Make `primary`/`accent` the single source of truth.

1. **Extend palette** in `tailwind.config.js` so `accent` colors have full 50–950 scales (currently only single hex each) — needed for hover/bg/border variants.
2. **Migrate raw colors → tokens:**
   - `indigo-500/600/700` → `primary-500/600/700` (46 occurrences, 17 files).
   - Keep semantic colors (`green` = success/correct, `red` = danger/wrong) but map to `accent` where it's decorative.
3. **Replace grayscale CTAs with brand.** Landing + Dashboard primary buttons `bg-gray-900` → `bg-primary-600` (keep gray for neutral/secondary actions only).
4. **Brand the Dashboard placeholders.** `Dashboard.vue:78-85` gray gradients → rotating brand/accent gradients (purple→blue, pink→yellow, etc.).

**Effort:** Medium (mostly find-replace + review). **Impact:** High — instant brand cohesion across all surfaces.

### Priority 2 — Display font for headings (high impact, small)

- Add a playful rounded display font (Fredoka / Baloo 2 / Poppins) via Bunny Fonts or self-host.
- `tailwind.config.js`: add `fontFamily.display`. Apply to `h1`/`h2`/hero/podium/score numbers. Keep Inter for body.
- Self-host or preconnect for performance.

**Effort:** Small. **Impact:** High — single biggest "feel" change.

### Priority 3 — Hero revamp (high visibility)

`Landing.vue` hero:
- Gradient/mesh background using brand colors (`from-primary-50 via-white to-accent-blue/10`), tasteful in dark mode too.
- Decorative blurred blobs behind floating avatars (purple/pink glow).
- Glassmorphism join-code card (extend existing `backdrop-blur` from nav `:48`).
- More expressive animated logo (subtle wiggle/bounce on the "Y").

**Effort:** Small-Medium. **Impact:** High — first impression.

### Priority 4 — Clean dead code

- `Landing.vue:162-188` — either re-enable Features section (translations exist) or delete it + the `features` array (`:23-28`).

**Effort:** Tiny.

### Priority 5 — Micro-interactions (library exists, just wire it)

- Apply `ripple-effect` class (`app.css:76`) to primary buttons; verify `ripple-active` toggle logic exists.
- Card hover: `hover:scale-[1.02] hover:shadow-lg` consistently (Dashboard cards have `hover:shadow-md` only).
- Inertia page transitions between routes (fade/slide).
- Stagger entrance (`stagger-1…8` classes already defined) on Dashboard grid + lobby player list.

**Effort:** Small. **Impact:** Medium.

### Priority 6 — Consistency pass per surface

Audit + align each surface to the unified palette:
- **Admin** (`Admin/*`) — currently grayscale + scattered indigo; standardize on primary + neutral grays for data tables.
- **Quiz Editor** (`Quiz/Editor.vue`) — heaviest raw-indigo user (7); migrate to tokens.
- **Auth pages** (`Auth/*`) — Jetstream defaults; brand the buttons/links.
- **Game pages** (`Host/Game.vue`, `Player/Game.vue`) — verify `QuizTheme` enum theming coexists with brand tokens (theme overrides per-quiz, brand is chrome default).

**Effort:** Medium. **Impact:** Medium-High.

### Priority 7 — Empty states & loading polish

- `Dashboard.vue:174` empty state — replace plain gray icon circle with branded illustration (use existing avatar SVGs or a friendly graphic).
- Deferred Inertia props → branded skeleton pulses (CLAUDE.md already mandates skeletons for deferred props).

**Effort:** Small.

### Priority 8 — Dark mode QA

- `darkMode: 'class'` active. After palette migration, verify gradients/glows/glassmorphism hold up in dark (no muddy purples, sufficient contrast).

**Effort:** Small.

---

## Suggested Execution Order

| Order | Item | Effort | Impact |
|-------|------|--------|--------|
| 1 | Display font (P2) | S | High |
| 2 | Palette unification (P1) | M | High |
| 3 | Hero revamp (P3) | S-M | High |
| 4 | Dead code cleanup (P4) | XS | — |
| 5 | Micro-interactions (P5) | S | Med |
| 6 | Per-surface consistency (P6) | M | Med-High |
| 7 | Empty/loading polish (P7) | S | Med |
| 8 | Dark mode QA (P8) | S | Med |

Do P1+P2 together first — they define the design system every later step builds on.

---

## Progress (2026-06-04)

Branch `feat/aesthetic-revamp`.

**Done:**
- **P1 Display font** — Gabarito added (`app.blade.php` Bunny Fonts + `tailwind.config.js` `fontFamily.display`). Applied to landing/dashboard/join headings, logo, step numbers.
- **P2 Palette unification** — brand `primary` now the single source of truth:
  - Migrated all raw `indigo-*` → `primary-*` across Pages + Components (14 files via bulk replace) — only `ThemeSelector.vue` keeps indigo (deliberate quiz-theme palette).
  - Grayscale CTAs (`bg-gray-900`) → `primary-600` on Landing, Dashboard, AppLayout nav, shared `PrimaryButton`/`TextInput`/`Checkbox` (drives all auth/profile forms).
  - Dashboard quiz placeholder gradients gray → brand/accent mixes.
- **P3 Hero revamp** — decorative blurred brand/accent blobs, display headings, glassmorphism join card, animated logo, branded step badges + final CTA glow.
- **P4 Dead code** — re-enabled Features section (translations existed) with colored accent icons.
- **P5 (partial)** — card hover lift (`hover:-translate-y-1`) on Dashboard + feature cards; icon `group-hover:scale-110`.

**Done (round 2):**
- **P5** — page-transition feel via `animate-page-enter` keyframe on AppLayout `<main>` (re-fires each Inertia nav, reduced-motion guarded); stagger entrance on Dashboard grid (`animate-slide-in-up` + index delay). `ripple-effect` confirmed already wired on Player answer buttons; `animate-bounce-in` on lobby player list.
- **P6** — admin page headers (8 files) → `font-display`; indigo→primary already unified; categorical stat-card colors + quiz-theme palettes kept intentionally.
- **P7** — verified no deferred Inertia props exist → skeletons N/A; empty-state branded (primary-tinted icon).

**Pending:**
- P8 — dark-mode QA pass on new gradients/glassmorphism (needs live render; tokens already carry `dark:` variants).
- Optional: branded empty-state illustration (vs current icon), auth-card heading display font.

Build verified clean (`npm run build`). No PHP changed (no Pint/test impact — changes are presentational Tailwind classes only).
