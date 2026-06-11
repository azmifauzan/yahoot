# User Panel Sidebar Revamp Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the top-nav `AppLayout` with a left sidebar (collapsible on desktop, off-canvas drawer on mobile) for all authenticated pages, with a `fullscreen` mode (no sidebar) used by the Quiz Editor.

**Architecture:** A new `useSidebar` composable holds collapse/drawer state. A new `SidebarNav` component renders nav link lists (icon + label, collapsed-aware). `AppLayout.vue` is rewritten to render either the sidebar shell (default) or a bare header+content shell (`fullscreen` prop). Admin nav links move into the sidebar's "Admin Panel" section, gated on `auth.user.is_admin`.

**Tech Stack:** Vue 3 `<script setup>`, Inertia v2, Tailwind CSS, vue-i18n. No JS test framework in this repo — verification is `npm run build` + manual browser check via `composer run dev`.

---

## Reference: spec

Full design rationale: `docs/superpowers/specs/2026-06-11-user-panel-sidebar-design.md`

**Correction vs spec:** the spec says 8 admin pages have an inline sub-nav to remove. On inspection, only `Pages/Admin/Dashboard.vue` has the `<nav>` with Users/Quizzes/Games/Settings links (lines 39-44). The other 7 admin pages only have a single "← Back to X" breadcrumb link, which stays as-is (page-level navigation, not redundant with the sidebar). Only `Admin/Dashboard.vue` needs cleanup.

---

### Task 1: `useSidebar` composable

**Files:**
- Create: `resources/js/Composables/useSidebar.js`

- [ ] **Step 1: Create the composable**

```js
import { ref, watch } from 'vue';

const collapsed = ref(localStorage.getItem('sidebar_collapsed') === 'true');
const mobileOpen = ref(false);

watch(collapsed, (value) => {
    localStorage.setItem('sidebar_collapsed', value ? 'true' : 'false');
});

export function useSidebar() {
    function toggleCollapsed() {
        collapsed.value = !collapsed.value;
    }

    function toggleMobile() {
        mobileOpen.value = !mobileOpen.value;
    }

    function closeMobile() {
        mobileOpen.value = false;
    }

    return {
        collapsed,
        mobileOpen,
        toggleCollapsed,
        toggleMobile,
        closeMobile,
    };
}
```

This follows the same singleton-ref pattern as `resources/js/Composables/useTheme.js` (module-level refs shared across all components that call `useSidebar()`).

- [ ] **Step 2: Commit**

```bash
git add resources/js/Composables/useSidebar.js
git commit -m "feat: add useSidebar composable for sidebar collapse/drawer state"
```

---

### Task 2: `iconOnly` prop on `ApplicationMark`

**Files:**
- Modify: `resources/js/Components/ApplicationMark.vue`

- [ ] **Step 1: Add the prop and conditional text**

Replace the full file content with:

```vue
<script setup>
defineProps({
    iconOnly: { type: Boolean, default: false },
});
</script>

<template>
    <div class="flex items-center gap-0.5">
        <svg viewBox="0 0 32 32" class="h-8 w-8" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="32" height="32" rx="8" class="fill-gray-900 dark:fill-white"/>
            <text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" class="fill-white dark:fill-gray-900" font-size="18" font-weight="bold" font-family="Inter, sans-serif">Y</text>
        </svg>
        <span v-if="!iconOnly" class="text-lg font-bold text-gray-900 dark:text-white">ahoot</span>
    </div>
</template>
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/Components/ApplicationMark.vue
git commit -m "feat: add iconOnly prop to ApplicationMark for collapsed sidebar"
```

---

### Task 3: `SidebarNav` component

**Files:**
- Create: `resources/js/Components/Navigation/SidebarNav.vue`

- [ ] **Step 1: Create the component**

```vue
<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: { type: Array, required: true },
    collapsed: { type: Boolean, default: false },
    label: { type: String, default: '' },
});

defineEmits(['navigate']);

const icons = {
    home: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10',
    user: 'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z',
    key: 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z',
    shield: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    users: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
    document: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    bolt: 'M13 10V3L4 14h7v7l9-11h-7z',
    cog: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
};
</script>

<template>
    <div>
        <p
            v-if="label && !collapsed"
            class="mb-1 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500"
        >
            {{ label }}
        </p>
        <div class="space-y-1">
            <Link
                v-for="link in links"
                :key="link.href"
                :href="link.href"
                @click="$emit('navigate')"
                :title="collapsed ? link.label : undefined"
                :class="[
                    'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                    collapsed ? 'justify-center' : '',
                    link.active
                        ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-primary-400',
                ]"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="icons[link.icon]" />
                </svg>
                <span v-if="!collapsed">{{ link.label }}</span>
            </Link>
        </div>
    </div>
</template>
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/Components/Navigation/SidebarNav.vue
git commit -m "feat: add SidebarNav component for sidebar link lists"
```

---

### Task 4: i18n keys

**Files:**
- Modify: `resources/js/locales/en.json:7-15`
- Modify: `resources/js/locales/id.json:7-15`

- [ ] **Step 1: Add keys to `en.json`**

Current `nav` block:

```json
    "nav": {
        "home": "Home",
        "dashboard": "Dashboard",
        "login": "Login",
        "register": "Register",
        "logout": "Logout",
        "profile": "Profile",
        "language": "Language"
    },
```

Replace with:

```json
    "nav": {
        "home": "Home",
        "dashboard": "Dashboard",
        "login": "Login",
        "register": "Register",
        "logout": "Logout",
        "profile": "Profile",
        "language": "Language",
        "my_quizzes": "My Quizzes",
        "api_tokens": "API Tokens",
        "menu": "Menu",
        "admin_panel": "Admin Panel",
        "collapse": "Collapse",
        "expand": "Expand"
    },
```

- [ ] **Step 2: Add keys to `id.json`**

Current `nav` block:

```json
    "nav": {
        "home": "Beranda",
        "dashboard": "Dashboard",
        "login": "Masuk",
        "register": "Daftar",
        "logout": "Keluar",
        "profile": "Profil",
        "language": "Bahasa"
    },
```

Replace with:

```json
    "nav": {
        "home": "Beranda",
        "dashboard": "Dashboard",
        "login": "Masuk",
        "register": "Daftar",
        "logout": "Keluar",
        "profile": "Profil",
        "language": "Bahasa",
        "my_quizzes": "Kuis Saya",
        "api_tokens": "Token API",
        "menu": "Menu",
        "admin_panel": "Panel Admin",
        "collapse": "Ciutkan",
        "expand": "Perluas"
    },
```

- [ ] **Step 3: Commit**

```bash
git add resources/js/locales/en.json resources/js/locales/id.json
git commit -m "i18n: add sidebar nav translation keys"
```

---

### Task 5: Rewrite `AppLayout.vue`

**Files:**
- Modify: `resources/js/Layouts/AppLayout.vue` (full rewrite)

- [ ] **Step 1: Replace the entire file**

```vue
<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import LanguageSwitcher from '@/Components/UI/LanguageSwitcher.vue';
import ThemeSwitcher from '@/Components/UI/ThemeSwitcher.vue';
import SidebarNav from '@/Components/Navigation/SidebarNav.vue';
import { useSidebar } from '@/Composables/useSidebar';

const { t } = useI18n();
const page = usePage();

defineProps({
    title: String,
    fullscreen: { type: Boolean, default: false },
});

const { collapsed, mobileOpen, toggleCollapsed, toggleMobile, closeMobile } = useSidebar();

const menuLinks = computed(() => {
    const links = [
        { href: route('dashboard'), label: t('nav.my_quizzes'), icon: 'home', active: route().current('dashboard') },
        { href: route('profile.show'), label: t('nav.profile'), icon: 'user', active: route().current('profile.show') },
    ];

    if (page.props.jetstream.hasApiFeatures) {
        links.push({ href: route('api-tokens.index'), label: t('nav.api_tokens'), icon: 'key', active: route().current('api-tokens.index') });
    }

    return links;
});

const adminLinks = computed(() => {
    if (!page.props.auth.user?.is_admin) {
        return [];
    }

    return [
        { href: route('admin.dashboard'), label: t('admin.dashboard'), icon: 'shield', active: route().current('admin.dashboard') },
        { href: route('admin.users.index'), label: t('admin.users'), icon: 'users', active: route().current('admin.users.*') },
        { href: route('admin.quizzes.index'), label: t('admin.quizzes'), icon: 'document', active: route().current('admin.quizzes.*') },
        { href: route('admin.games.index'), label: t('admin.games'), icon: 'bolt', active: route().current('admin.games.*') },
        { href: route('admin.settings.index'), label: t('admin.settings'), icon: 'cog', active: route().current('admin.settings.*') },
    ];
});

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-white dark:bg-gray-950">
            <!-- Fullscreen mode: header only, no sidebar -->
            <template v-if="fullscreen">
                <header v-if="$slots.header" class="bg-white shadow-sm dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <main class="animate-page-enter">
                    <slot />
                </main>
            </template>

            <!-- Sidebar layout -->
            <div v-else class="flex">
                <!-- Mobile backdrop -->
                <div
                    v-if="mobileOpen"
                    class="fixed inset-0 z-40 bg-black/50 lg:hidden"
                    @click="closeMobile"
                />

                <!-- Sidebar -->
                <aside
                    :class="[
                        'fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-gray-200 bg-white transition-transform duration-200 dark:border-gray-800 dark:bg-gray-900',
                        'lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 lg:transition-[width]',
                        collapsed ? 'lg:w-20' : 'lg:w-64',
                        mobileOpen ? 'translate-x-0' : '-translate-x-full',
                    ]"
                >
                    <!-- Logo + collapse toggle -->
                    <div class="flex h-16 shrink-0 items-center justify-between border-b border-gray-100 px-4 dark:border-gray-800">
                        <Link :href="route('dashboard')" class="flex items-center overflow-hidden">
                            <ApplicationMark :icon-only="collapsed" />
                        </Link>
                        <button
                            @click="toggleCollapsed"
                            class="hidden h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 lg:flex dark:hover:bg-gray-800 dark:hover:text-gray-300"
                            :title="collapsed ? t('nav.expand') : t('nav.collapse')"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path v-if="collapsed" stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    </div>

                    <!-- Nav -->
                    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4">
                        <SidebarNav :links="menuLinks" :collapsed="collapsed" :label="t('nav.menu')" @navigate="closeMobile" />

                        <div v-if="adminLinks.length">
                            <div class="my-3 border-t border-gray-100 dark:border-gray-800" />
                            <SidebarNav :links="adminLinks" :collapsed="collapsed" :label="t('nav.admin_panel')" @navigate="closeMobile" />
                        </div>
                    </nav>

                    <!-- User card + switchers + logout -->
                    <div class="shrink-0 border-t border-gray-100 p-3 dark:border-gray-800">
                        <div :class="['flex items-center gap-3 rounded-lg px-2 py-2', collapsed ? 'justify-center' : '']">
                            <img
                                v-if="page.props.jetstream.managesProfilePhotos"
                                class="h-9 w-9 shrink-0 rounded-full object-cover"
                                :src="page.props.auth.user.profile_photo_url"
                                :alt="page.props.auth.user.name"
                            />
                            <div v-else class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-bold text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
                                {{ page.props.auth.user.name.charAt(0).toUpperCase() }}
                            </div>
                            <div v-if="!collapsed" class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-800 dark:text-gray-200">{{ page.props.auth.user.name }}</p>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ page.props.auth.user.email }}</p>
                            </div>
                        </div>

                        <div :class="['mt-2 flex items-center gap-2', collapsed ? 'flex-col px-0' : 'px-2']">
                            <LanguageSwitcher v-if="!collapsed" />
                            <ThemeSwitcher />
                        </div>

                        <form @submit.prevent="logout" class="mt-2">
                            <button
                                type="submit"
                                :title="t('nav.logout')"
                                :class="[
                                    'flex w-full items-center gap-3 rounded-lg px-2 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50 hover:text-red-600 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-red-400',
                                    collapsed ? 'justify-center' : '',
                                ]"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span v-if="!collapsed">{{ t('nav.logout') }}</span>
                            </button>
                        </form>
                    </div>
                </aside>

                <!-- Main column -->
                <div class="flex min-w-0 flex-1 flex-col">
                    <!-- Mobile topbar -->
                    <div class="sticky top-0 z-30 flex h-14 items-center gap-3 border-b border-gray-200 bg-white/80 px-4 backdrop-blur-lg lg:hidden dark:border-gray-800 dark:bg-gray-950/80">
                        <button
                            @click="toggleMobile"
                            class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <ApplicationMark />
                    </div>

                    <!-- Page Heading -->
                    <header v-if="$slots.header" class="bg-white shadow-sm dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800">
                        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                            <slot name="header" />
                        </div>
                    </header>

                    <!-- Page Content -->
                    <main class="flex-1 animate-page-enter">
                        <slot />
                    </main>
                </div>
            </div>
        </div>
    </div>
</template>
```

Notes on this rewrite:
- The mobile drawer uses `fixed` + `-translate-x-full`/`translate-x-0`; at `lg:` it switches to `lg:sticky lg:translate-x-0` so it docks in the flex flow instead of overlaying.
- `collapsed` only changes width at `lg:` (`lg:w-20`/`lg:w-64`) — on mobile the drawer is always `w-64`.
- The old top navbar (`<nav>` with Dashboard link + Dropdown menu) is fully removed; equivalent links now live in the sidebar.
- `Dropdown.vue` and `DropdownLink.vue` imports are dropped from this file (no longer used here) — do not delete those component files, they may be used elsewhere (check before any future cleanup).

- [ ] **Step 2: Commit**

```bash
git add resources/js/Layouts/AppLayout.vue
git commit -m "feat: rewrite AppLayout with sidebar navigation and fullscreen mode"
```

---

### Task 6: Fullscreen mode for Quiz Editor

**Files:**
- Modify: `resources/js/Pages/Quiz/Editor.vue:345`

- [ ] **Step 1: Add `:fullscreen="true"` to the `AppLayout` tag**

Current (line 345):

```vue
    <AppLayout :title="isNew ? t('dashboard.create_quiz') : quizForm.title || t('quiz.untitled')">
```

Replace with:

```vue
    <AppLayout :title="isNew ? t('dashboard.create_quiz') : quizForm.title || t('quiz.untitled')" :fullscreen="true">
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/Pages/Quiz/Editor.vue
git commit -m "feat: render Quiz Editor fullscreen without sidebar"
```

---

### Task 7: Remove inline admin sub-nav from `Admin/Dashboard.vue`

**Files:**
- Modify: `resources/js/Pages/Admin/Dashboard.vue:36-46`

- [ ] **Step 1: Simplify the header slot**

Current (lines 36-46):

```vue
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-display text-xl font-bold text-gray-800 dark:text-gray-100">{{ t('admin.dashboard') }}</h2>
                <nav class="flex items-center gap-4 text-sm">
                    <Link :href="route('admin.users.index')" class="text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400">{{ t('admin.users') }}</Link>
                    <Link :href="route('admin.quizzes.index')" class="text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400">{{ t('admin.quizzes') }}</Link>
                    <Link :href="route('admin.games.index')" class="text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400">{{ t('admin.games') }}</Link>
                    <Link :href="route('admin.settings.index')" class="text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400">{{ t('admin.settings') }}</Link>
                </nav>
            </div>
        </template>
```

Replace with:

```vue
        <template #header>
            <h2 class="font-display text-xl font-bold text-gray-800 dark:text-gray-100">{{ t('admin.dashboard') }}</h2>
        </template>
```

- [ ] **Step 2: Check if `Link` is still used elsewhere in this file**

```bash
grep -n "Link" resources/js/Pages/Admin/Dashboard.vue
```

If the only remaining match is the `import { ... Link ... } from '@inertiajs/vue3'` line itself, remove `Link` from that import to avoid an unused-import lint warning. If `Link` is still used in the template body (e.g. for stat cards or recent games list), leave the import as-is.

- [ ] **Step 3: Commit**

```bash
git add resources/js/Pages/Admin/Dashboard.vue
git commit -m "refactor: remove redundant admin sub-nav now that sidebar covers it"
```

---

### Task 8: Build and manual verification

**Files:** none (verification only)

- [ ] **Step 1: Build frontend assets**

```bash
npm run build
```

Expected: build completes with no errors (catches Vue template/syntax issues across all modified files).

- [ ] **Step 2: Start dev servers**

```bash
composer run dev
```

- [ ] **Step 3: Manual checks in browser** (login as a regular user, then as an admin user — `is_admin = true`)

Desktop (`>= lg`, e.g. 1280px wide):
- Sidebar visible on `/dashboard`, `/profile`, `/api-tokens` (if enabled) with "My Quizzes" / "Profile" / "API Tokens" links; current page highlighted (`bg-primary-50`/`text-primary-700`)
- Collapse toggle (chevron in sidebar header) shrinks sidebar to icon-only (`w-20`); reload page — collapsed state persists (localStorage)
- Collapsed sidebar: hovering a link shows a tooltip (native `title`) with its label
- Logged in as admin: "Admin Panel" section visible below a divider, with Admin Dashboard / Users / Quizzes / Games / Settings links; visiting `/admin` highlights "Admin Dashboard", `/admin/users` highlights "Users", etc.
- Logged in as non-admin: "Admin Panel" section is absent entirely
- `/admin` page header shows only the "Admin Panel" title, no duplicate inline nav links
- Bottom of sidebar: avatar/initial + name/email, language + theme switchers, logout button — logout works

Mobile (`< lg`, e.g. 375px wide via browser devtools):
- Sidebar hidden by default; slim topbar shows hamburger + logo
- Tapping hamburger slides sidebar in from left with a dark backdrop
- Tapping backdrop or any nav link closes the drawer
- Sidebar shows full labels (not collapsed) regardless of desktop collapse state

Quiz Editor (`/quizzes/create` and editing an existing quiz):
- No sidebar, no mobile topbar — fullscreen
- Existing header bar (back button, title input, save/publish buttons) still works
- Question list / editor / properties panels render and function as before

Dark mode (toggle via theme switcher):
- Sidebar, drawer, backdrop, collapsed state, admin section all render correctly in dark mode

- [ ] **Step 4: Run PHP formatter for any incidental whitespace changes (no PHP files expected to change)**

```bash
vendor/bin/pint --dirty --format agent
```

Expected: "No changes" or no output (this plan touches no PHP files; this step is a safety net only).
