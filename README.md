# Yahoot

Platform kuis interaktif real-time yang terinspirasi dari Kahoot. Gratis, open source, tanpa batasan.

**Production:** [https://yahoot.my.id](https://yahoot.my.id)

## Tentang

Yahoot memungkinkan kreator membuat kuis dan memainkannya bersama pemain secara real-time. Pemain bergabung menggunakan kode game 6 digit, memilih avatar, dan berkompetisi menjawab pertanyaan untuk meraih skor tertinggi.

## Fitur Utama

- **Quiz Creator** — Buat dan kelola kuis dengan editor visual intuitif
- **2 Tipe Pertanyaan** — Multiple Choice (4 pilihan) & True/False
- **Real-time Gameplay** — Bermain bersama secara real-time via WebSocket
- **Guest Play** — Pemain tidak perlu registrasi untuk bermain
- **Scoring & Streak** — Sistem poin berbasis kecepatan + bonus streak
- **Scoreboard & Leaderboard** — Peringkat per pertanyaan + podium akhir
- **Admin Panel** — Dashboard admin untuk manajemen user, kuis, dan game
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
| Database | PostgreSQL 17 |
| File Storage | S3-compatible (IDCloudHost) |
| Email | Brevo (SMTP) |
| Container | Docker + Docker Compose |
| Reverse Proxy | Nginx |

## Persyaratan

- PHP >= 8.2
- Composer
- Node.js >= 18
- npm
- PostgreSQL 17 (atau via Docker)

## Instalasi

```bash
# Clone repository
git clone https://github.com/azmifauzan/yahoot.git
cd yahoot

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Jalankan migrasi & seeder
php artisan migrate --seed

# Build frontend
npm run build
```

## Development

```bash
# Jalankan development server (Laravel + Vite)
composer run dev
```

## Docker Deployment

```bash
# Build dan jalankan semua services
docker compose up -d --build

# Jalankan migrasi
docker compose exec app php artisan migrate --force --seed

# Cache config untuk production
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

### Docker Services

| Service | Deskripsi | Port |
|---------|-----------|------|
| `app` | Laravel PHP-FPM | 9000 |
| `nginx` | Web server | 8000 |
| `postgres` | Database | 5432 |
| `redis` | Cache & queue | 6379 |
| `reverb` | WebSocket server | 8080 |
| `queue` | Queue worker | — |
| `scheduler` | Task scheduler | — |

### Production (Nginx Reverse Proxy)

Gunakan `docker/nginx/yahoot.my.id.conf` sebagai konfigurasi nginx reverse proxy di host server. Setup SSL dengan Certbot:

```bash
sudo certbot --nginx -d yahoot.my.id
```

## Environment Variables

| Variable | Deskripsi | Default |
|----------|-----------|---------|
| `DB_CONNECTION` | Database driver | `pgsql` |
| `FILESYSTEM_DISK` | Storage driver | `s3` |
| `AWS_ENDPOINT` | S3 endpoint | `https://is3.cloudhost.id` |
| `AWS_BUCKET` | S3 bucket name | `yahoot` |
| `MAIL_MAILER` | Mail driver | `smtp` |
| `MAIL_HOST` | SMTP host | `smtp-relay.brevo.com` |
| `BROADCAST_CONNECTION` | Broadcasting driver | `reverb` |

Lihat `.env.example` untuk konfigurasi lengkap.

## Testing

```bash
# Jalankan semua test
php artisan test

# Jalankan test spesifik
php artisan test --filter=NamaTest

# Jalankan test dengan compact output
php artisan test --compact
```

## Struktur Project

```
app/
├── Http/Controllers/    # Controller (Quiz, Game, Admin, dll)
├── Http/Middleware/      # Middleware (SetLocale, EnsureUserIsAdmin)
├── Http/Requests/       # Form Request validation
├── Models/              # Eloquent models
├── Events/              # Broadcasting events (WebSocket)
├── Services/            # Business logic (Game, Scoring)
├── Enums/               # PHP Enums (GameStatus, QuestionType, dll)
└── Policies/            # Authorization policies

resources/js/
├── Pages/               # Halaman Inertia (Landing, Dashboard, Game, Admin)
├── Components/          # Vue components (Avatar, Game, Quiz, UI)
├── Layouts/             # Layout components
├── composables/         # Vue composables (useGame, useTimer)
└── locales/             # File terjemahan (id.json, en.json)

database/
├── migrations/          # Database migrations
├── factories/           # Model factories
└── seeders/             # Database seeders

docker/
├── nginx/               # Nginx config (container + reverse proxy)
└── php/                 # PHP config (php.ini, opcache)
```

## Dokumentasi

Lihat [docs/PRD.md](docs/PRD.md) untuk Product Requirements Document lengkap.

## Lisensi

Yahoot adalah software open source berlisensi [MIT](https://opensource.org/licenses/MIT).
