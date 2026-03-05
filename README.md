# Yahoot

Platform kuis interaktif real-time yang terinspirasi dari Kahoot. Gratis, open source, tanpa batasan.

## Tentang

Yahoot memungkinkan kreator membuat kuis dan memainkannya bersama pemain secara real-time. Pemain bergabung menggunakan kode game 6 digit, memilih avatar, dan berkompetisi menjawab pertanyaan untuk meraih skor tertinggi.

## Fitur Utama

- **Quiz Creator** — Buat dan kelola kuis dengan editor visual intuitif
- **2 Tipe Pertanyaan** — Multiple Choice (4 pilihan) & True/False
- **Real-time Gameplay** — Bermain bersama secara real-time via WebSocket
- **Guest Play** — Pemain tidak perlu registrasi untuk bermain
- **Scoring & Streak** — Sistem poin berbasis kecepatan + bonus streak
- **Scoreboard & Leaderboard** — Peringkat per pertanyaan + podium akhir
- **Animasi** — Countdown, confetti, podium rise, dan lainnya
- **24 Avatar** — Hewan, Robot, Monster, dan Abstrak (SVG)
- **Dwibahasa** — Indonesia (default) & English
- **Gratis & Open Source** — Tanpa batasan fitur, pemain, atau kuis

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 12, PHP 8.4 |
| Frontend | Vue 3, Inertia.js v2 |
| Styling | Tailwind CSS |
| Real-time | Laravel Reverb (WebSocket) |
| Auth | Laravel Fortify + Jetstream + Sanctum |
| Testing | Pest 4 |
| Database | MySQL / PostgreSQL / SQLite |

## Persyaratan

- PHP >= 8.2
- Composer
- Node.js >= 18
- npm

## Instalasi

```bash
# Clone repository
git clone https://github.com/your-username/yahoot.git
cd yahoot

# Install dependencies & setup
composer setup
```

Perintah `composer setup` akan menjalankan:
1. `composer install`
2. Copy `.env.example` ke `.env`
3. Generate application key
4. Jalankan migrasi database
5. `npm install`
6. `npm run build`

## Development

```bash
# Jalankan development server (Laravel + Vite)
composer run dev
```

## Testing

```bash
# Jalankan semua test
php artisan test

# Jalankan test spesifik
php artisan test --filter=NamaTest
```

## Struktur Project

```
app/
├── Http/Controllers/    # Controller (Quiz, Question, Game, Player)
├── Http/Requests/       # Form Request validation
├── Models/              # Eloquent models
├── Events/              # Broadcasting events (WebSocket)
├── Services/            # Business logic (Game, Scoring)
├── Enums/               # PHP Enums (GameStatus, QuestionType, dll)
└── Policies/            # Authorization policies

resources/js/
├── Pages/               # Halaman Inertia (Landing, Dashboard, Game)
├── Components/          # Vue components (Avatar, Game, Quiz, UI)
├── Layouts/             # Layout components
├── composables/         # Vue composables (useGame, useTimer)
└── locales/             # File terjemahan (id.json, en.json)

database/
├── migrations/          # Database migrations
├── factories/           # Model factories
└── seeders/             # Database seeders
```

## Dokumentasi

Lihat [docs/PRD.md](docs/PRD.md) untuk Product Requirements Document lengkap.

## Lisensi

Yahoot adalah software open source berlisensi [MIT](https://opensource.org/licenses/MIT).
