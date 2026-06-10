# 📋 Product Requirements Document (PRD)

## **Yahoot — Platform Kuis Interaktif Real-Time**

**Versi:** 1.0  
**Tanggal:** 5 Maret 2026  
**Lisensi:** MIT (Free & Open Source)  
**Stack:** Laravel 12, Inertia.js v2, Vue 3, Tailwind CSS, Laravel Reverb (WebSocket)  
**Domain:** yahoot.web.id

---

## 1. Ringkasan Produk

**Yahoot** adalah platform kuis interaktif real-time yang terinspirasi dari Kahoot. Aplikasi ini memungkinkan kreator membuat kuis, membagikan game room kepada pemain, dan bermain bersama secara real-time dengan sistem scoring, leaderboard, serta animasi yang menarik.

Yahoot bersifat **free & open source** tanpa batasan fitur, jumlah pemain, atau jumlah kuis.

### Nilai Utama
- **Gratis & tanpa batasan** — semua fitur tersedia untuk semua pengguna
- **Real-time & interaktif** — pengalaman bermain kuis yang seru dan kompetitif
- **Minimalis tapi ceria** — desain bersih dengan warna-warna cerah dan animasi menyenangkan
- **Dwibahasa** — mendukung Bahasa Indonesia (default) dan English

---

## 2. Target Pengguna

| Persona | Deskripsi |
|---------|-----------|
| **Kreator** | Guru, dosen, trainer, event organizer yang membuat kuis |
| **Pemain** | Murid, peserta pelatihan, atau siapa saja yang bergabung ke game room |
| **Admin** | Pengelola platform (self-hosted) |

---

## 3. Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 12, PHP 8.4 |
| Frontend | Vue 3, Inertia.js v2 |
| Styling | Tailwind CSS 3.4 |
| Real-time | Laravel Reverb (WebSocket) |
| Animation | CSS Transitions/Keyframes + Vue Transition |
| Auth | Laravel Fortify + Jetstream |
| API Token | Laravel Sanctum |
| Database | PostgreSQL 17 |
| File Storage | S3-compatible (IDCloudHost Object Storage) |
| Email | SMTP via Brevo (Sendinblue) |
| Testing | Pest 4 |
| Routing (JS) | Ziggy v2 |
| Containerization | Docker + Docker Compose |
| Reverse Proxy | Nginx |
| Domain | yahoot.web.id |

---

## 4. Arsitektur Sistem

```
┌─────────────────────────────────────────────────────┐
│                    Browser (Vue 3)                   │
│  ┌──────────┐  ┌──────────┐  ┌───────────────────┐  │
│  │ Kreator  │  │  Pemain  │  │   Spectator View  │  │
│  │   SPA    │  │   SPA    │  │      (Host)       │  │
│  └────┬─────┘  └────┬─────┘  └────────┬──────────┘  │
│       │              │                 │             │
│       └──────────────┼─────────────────┘             │
│                      │ Inertia.js / WebSocket        │
└──────────────────────┼───────────────────────────────┘
                       │
┌──────────────────────┼───────────────────────────────┐
│              Laravel 12 Backend                      │
│  ┌───────────┐  ┌────────────┐  ┌────────────────┐  │
│  │Controllers│  │  Services  │  │  Broadcasting  │  │
│  │           │  │  (Game     │  │  (Reverb)      │  │
│  │  - Quiz   │  │  Engine)   │  │                │  │
│  │  - Game   │  │            │  │  - GameChannel │  │
│  │  - Player │  │  - Scoring │  │  - PlayerJoin  │  │
│  │  - Auth   │  │  - Timer   │  │  - Answer      │  │
│  └───────────┘  └────────────┘  └────────────────┘  │
│                                                      │
│  ┌─────────────────────────────────────────────────┐ │
│  │              Database (Eloquent ORM)            │ │
│  │  users, quizzes, questions, answers,            │ │
│  │  game_sessions, game_players, player_answers    │ │
│  └─────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────┘
```

---

## 5. Data Model

### 5.1 Entity Relationship

```
User 1──N Quiz
Quiz 1──N Question
Question 1──N Answer
User 1──N GameSession (sebagai host)
GameSession 1──N GamePlayer
GameSession N──1 Quiz
GamePlayer N──1 User (nullable, untuk guest)
GamePlayer 1──N PlayerAnswer
PlayerAnswer N──1 Question
PlayerAnswer N──1 Answer
```

### 5.2 Tabel Database

#### `users`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| name | string | Nama pengguna |
| email | string unique | Email |
| password | string | Password (hashed) |
| avatar | string nullable | Nama avatar yang dipilih |
| locale | string default 'id' | Bahasa pilihan (id/en) |
| is_admin | boolean default false | Admin flag |
| email_verified_at | timestamp nullable | |
| timestamps | | created_at, updated_at |

#### `quizzes`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| user_id | bigint FK | Kreator |
| title | string | Judul kuis |
| description | text nullable | Deskripsi kuis |
| cover_image | string nullable | Path gambar cover |
| visibility | enum('public','private') | Default 'private' |
| is_published | boolean | Default false |
| settings | json nullable | Pengaturan default kuis |
| timestamps | | |
| deleted_at | timestamp nullable | Soft delete |

#### `questions`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| quiz_id | bigint FK | |
| type | enum('multiple_choice','true_false') | Default 'multiple_choice' |
| question_text | string | Teks pertanyaan |
| image | string nullable | Path gambar pertanyaan |
| time_limit | integer | Detik (5, 10, 20, 30, 60, 90, 120) |
| points | enum('standard','double','none') | Tipe poin |
| order | integer | Urutan pertanyaan |
| timestamps | | |

#### `answers`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| question_id | bigint FK | |
| answer_text | string | Teks jawaban |
| is_correct | boolean | Default false |
| color | string | Warna jawaban (red/blue/yellow/green) |
| shape | string | Bentuk ikon (triangle/diamond/circle/square) |
| order | integer | Urutan jawaban |
| timestamps | | |

#### `game_sessions`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| quiz_id | bigint FK | |
| host_id | bigint FK → users | |
| game_code | string(6) unique | Kode 6 digit untuk join |
| status | enum | 'waiting', 'playing', 'reviewing', 'finished' |
| current_question_index | integer | Default 0 |
| question_started_at | timestamp nullable | Waktu mulai pertanyaan |
| settings | json nullable | Override settings |
| started_at | timestamp nullable | |
| finished_at | timestamp nullable | |
| timestamps | | |

#### `game_players`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| game_session_id | bigint FK | |
| user_id | bigint FK nullable | Null untuk guest |
| nickname | string | Nama tampilan |
| avatar | string | Nama avatar |
| score | integer | Default 0 |
| streak | integer | Default 0 (jawaban benar beruntun) |
| is_connected | boolean | Default true |
| finished_at | timestamp nullable | |
| timestamps | | |

#### `player_answers`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | Auto increment |
| game_player_id | bigint FK | |
| question_id | bigint FK | |
| answer_id | bigint FK nullable | Null jika tidak menjawab |
| is_correct | boolean | |
| time_taken | integer | Milidetik |
| points_earned | integer | Default 0 |
| streak_bonus | integer | Default 0 |
| timestamps | | |

---

## 6. Fitur Detail

### 6.1 Autentikasi & Profil

#### 6.1.1 Registrasi & Login
- Registrasi dengan email & password
- Login dengan email & password
- Lupa password & reset password
- Email verification (opsional, bisa dimatikan)
- Menggunakan Laravel Fortify & Jetstream yang sudah terinstall

#### 6.1.2 Profil Pengguna
- Edit nama, email, password
- **Pilih avatar** dari koleksi yang tersedia
- Pilih bahasa (Indonesia / English)
- Lihat statistik: total kuis dibuat, total game dimainkan

#### 6.1.3 Koleksi Avatar
Tersedia **24 avatar** yang dikelompokkan dalam 4 kategori:

| Kategori | Avatar |
|----------|--------|
| 🐾 Hewan | Kucing, Anjing, Panda, Kelinci, Rubah, Burung Hantu |
| 🤖 Robot | Robot Biru, Robot Merah, Robot Hijau, Robot Kuning, Robot Ungu, Robot Pink |
| 👾 Monster | Monster Lucu 1-6 (warna-warni, ekspresi friendly) |
| 🌟 Abstrak | Bintang, Bulan, Matahari, Awan, Pelangi, Petir |

Avatar menggunakan **SVG inline** yang dibuat langsung di komponen Vue, sehingga ringan dan scalable.

---

### 6.2 Quiz Creator (Kreator Kuis)

#### 6.2.1 Dashboard Kreator
- Daftar semua kuis milik user
- Filter: Semua, Draf, Dipublikasi
- Search berdasarkan judul
- Grid/List view toggle
- Setiap kartu kuis menampilkan:
  - Cover image (atau placeholder warna-warni)
  - Judul
  - Jumlah pertanyaan
  - Status (Draf / Dipublikasi)
  - Tanggal dibuat
  - Quick actions: Edit, Mainkan, Duplikat, Hapus

#### 6.2.2 Editor Kuis
**Header:**
- Input judul kuis (inline edit)
- Tombol: Simpan, Preview, Kembali

**Sidebar Kiri — Daftar Pertanyaan:**
- Thumbnail tiap pertanyaan (mini preview)
- Drag & drop untuk reorder
- Tombol "+ Tambah Pertanyaan"
- Nomor urut pertanyaan
- Indikator pertanyaan yang belum lengkap (tanda seru merah)

**Area Utama — Editor Pertanyaan:**
- Input teks pertanyaan (besar, center)
- Upload gambar pertanyaan (opsional, drag & drop)
- **Multiple Choice:** 4 slot jawaban dengan warna berbeda:
  - 🔴 Merah (▲ Segitiga)
  - 🔵 Biru (◆ Berlian)  
  - 🟡 Kuning (● Lingkaran)
  - 🟢 Hijau (■ Kotak)
  - Toggle jawaban benar (checkbox/radio per jawaban)
  - Minimal 2 jawaban harus diisi, maksimal 4
- **True / False:** 2 slot jawaban tetap:
  - 🔵 Biru (◆ Berlian) — "Benar" / "True"
  - 🔴 Merah (▲ Segitiga) — "Salah" / "False"
  - Kreator hanya memilih mana yang benar
  - Teks jawaban tidak bisa diedit (otomatis Benar/Salah)

**Sidebar Kanan — Properti Pertanyaan:**
- Tipe pertanyaan: Multiple Choice (default) / True or False
- Time limit: 5s, 10s, 20s, 30s, 60s, 90s, 120s (dropdown)
- Poin: Standard (1000), Double (2000), None (0)
- Answer options: Single select (default, untuk Multiple Choice)

#### 6.2.3 Validasi Kuis
Sebelum dipublikasikan, kuis harus memenuhi:
- Minimal 1 pertanyaan
- Setiap pertanyaan harus punya teks
- **Multiple Choice:** minimal 2 jawaban, maksimal 4, minimal 1 jawaban benar
- **True / False:** tepat 2 jawaban (otomatis), salah satu harus dipilih benar
- Time limit harus diset

#### 6.2.4 Duplikat Kuis
- Salin seluruh kuis beserta pertanyaan dan jawaban
- Judul otomatis ditambahi " (Salinan)" / " (Copy)"

---

### 6.3 Game Flow

#### 6.3.1 Overview Alur Game

```
[Kreator]                          [Pemain]
    │                                  │
    ├─ Klik "Mainkan" pada kuis        │
    │                                  │
    ├─ Masuk Lobby (tampil game code)  │
    │   ┌──────────────────────┐       │
    │   │  Game Code: 847291   │       │
    │   │  Menunggu pemain...  │       │
    │   └──────────────────────┘       │
    │                                  ├─ Buka yahoot.app
    │                                  ├─ Masukkan game code
    │                                  ├─ Pilih nickname & avatar
    │                                  ├─ Masuk lobby
    │                                  │
    │   (Nama pemain muncul di layar)  │
    │                                  │
    ├─ Klik "Mulai"                    │
    │                                  │
    │   ══════ LOOP PERTANYAAN ══════  │
    │   │                              │
    │   ├─ Countdown 3..2..1           ├─ Countdown 3..2..1
    │   │                              │
    │   ├─ Tampil pertanyaan +         │
    │   │  jawaban + timer             ├─ Tampil pilihan warna/shape
    │   │                              │  (tanpa teks pertanyaan)
    │   │                              │
    │   │                              ├─ Pemain memilih jawaban
    │   │                              │
    │   ├─ Timer habis / semua jawab   │
    │   │                              │
    │   ├─ Tampil jawaban benar +      ├─ Tampil benar/salah +
    │   │  statistik                   │  poin yang didapat
    │   │                              │
    │   ├─ Tampil scoreboard           ├─ Tampil posisi pemain
    │   │  (top 5)                     │
    │   │                              │
    │   ├─ Klik "Lanjut"              │
    │   │                              │
    │   ══════ END LOOP ═══════════    │
    │                                  │
    ├─ Tampil Podium/Leaderboard       ├─ Tampil posisi akhir
    │   (1st, 2nd, 3rd + semua)        │
    │                                  │
    ├─ Download hasil (CSV)            │
    └──────────────────────────────    └──────────────────────
```

#### 6.3.2 Lobby (Ruang Tunggu)

**Host View (Layar Proyektor):**
- Game code besar di tengah atas (**6 digit angka**)
- Instruksi: "Buka yahoot.app dan masukkan kode di atas"
- Area grid avatar pemain yang sudah bergabung
  - Animasi bounce saat pemain baru masuk
  - Tampilkan avatar + nickname
- Counter jumlah pemain
- Tombol "Mulai" (minimal 1 pemain)
- Musik latar lobby (opsional, bisa mute)

**Player View (Layar HP/Device):**
- Input game code (6 digit, auto-focus, large input)
- Setelah valid → Input nickname + pilih avatar
  - Grid avatar (4x6 grid, scroll)
  - Animasi selected: scale up + glow
- Setelah join → Tampilkan "Kamu sudah masuk! Menunggu host memulai..."
  - Avatar pemain beranimasi idle (floating/bouncing)
  - Nama pemain

#### 6.3.3 Countdown Sebelum Pertanyaan
- Fullscreen countdown: **3... 2... 1... START!**
- Animasi:
  - Angka zoom-in lalu fade-out
  - Warna background berubah tiap angka (merah → kuning → hijau)
  - "START!" dengan efek pop/bounce
- Durasi: ~4 detik

#### 6.3.4 Tampilan Pertanyaan — Host View
- **Atas:** Teks pertanyaan (font besar)
- **Tengah:** Gambar pertanyaan (jika ada)
- **Progress bar timer** di atas, mengecil dari kanan ke kiri
  - Warna berubah: hijau → kuning → merah (sisa < 25%)
- **Counter:** Jumlah pemain yang sudah menjawab / total
- **Bawah:**
  - **Multiple Choice:** 4 kotak jawaban berwarna dengan shape & teks (layout 2x2 grid)
  - **True / False:** 2 kotak jawaban besar — Biru (Benar) & Merah (Salah) (layout 1x2)

#### 6.3.5 Tampilan Pertanyaan — Player View
- **Atas:** Timer countdown (angka besar)
- **Bawah:**
  - **Multiple Choice:** 4 tombol besar berwarna dengan **shape saja** (tanpa teks jawaban)
    - ▲ Merah, ◆ Biru, ● Kuning, ■ Hijau
    - Layout 2x2 full-screen
  - **True / False:** 2 tombol besar — ◆ Biru (✓) & ▲ Merah (✗)
    - Layout 1x2 full-screen
  - Animasi press: scale down + ripple effect
- Setelah memilih:
  - Tombol yang dipilih highlight, yang lain fade
  - Tampilkan pesan "Jawaban terkirim!" + animasi ✓
  - Menunggu timer habis

#### 6.3.6 Reveal Jawaban — Host View
- Jawaban benar: highlight dengan ✓ dan efek glow
- Jawaban salah: dim/fade out
- Bar chart di bawah setiap jawaban menunjukkan berapa pemain memilih
- Animasi bars tumbuh dari bawah (staggered)
- Sound effect: benar (ding!) / reveal

#### 6.3.7 Reveal Jawaban — Player View
- **Jika benar:**
  - Layar hijau
  - Animasi confetti / bintang-bintang
  - Tampilkan: "Benar! +[poin]" 
  - Streak counter: "🔥 3 berturut-turut!"
- **Jika salah:**
  - Layar merah
  - Animasi shake ringan
  - Tampilkan: "Salah!" + jawaban yang benar
  - Streak reset
- **Jika tidak menjawab:**
  - Layar abu-abu
  - Tampilkan: "Waktu habis!"

#### 6.3.8 Scoreboard (Per Pertanyaan) — Host View
- Tampilkan **Top 5 pemain** setelah setiap pertanyaan
- Animasi:
  - Nama muncul satu per satu dari #5 ke #1 (reveal bertahap)
  - Bar skor tumbuh horizontal
  - Pemain yang naik peringkat: animasi slide up ↑ (hijau)
  - Pemain yang turun: animasi slide down ↓ (merah)
- Skor ditampilkan di samping nama
- Avatar di samping nama pemain
- Tombol "Lanjut" untuk host

#### 6.3.9 Scoreboard — Player View
- Posisi pemain saat ini (angka besar)
- Total skor
- Perubahan posisi (↑ naik 2, ↓ turun 1, atau ── tetap)
- Motivational message acak:
  - Posisi 1: "Kamu di puncak! 🏆"
  - Posisi 2-3: "Hampir di puncak! 💪"
  - Naik: "Kamu naik! Terus! 🚀"
  - Turun: "Jangan menyerah! 💥"
  - Tetap: "Stabil! 😎"

#### 6.3.10 Podium & Final Leaderboard — Host View
**Fase 1 — Podium (Animasi ~8 detik):**
- Background warna gradient festif
- Podium klasik 1-2-3:
  - Posisi 3 muncul duluan (kiri, podium pendek) — animasi naik dari bawah
  - Posisi 2 muncul (kanan, podium sedang) — animasi naik dari bawah
  - Posisi 1 muncul terakhir (tengah, podium tinggi) — animasi naik + bounce + confetti
- Setiap posisi menampilkan:
  - Avatar (besar)
  - Nickname
  - Skor
  - Crown/medal emoji (🥇🥈🥉)
- Confetti animation di seluruh layar saat juara 1 muncul
- Musik kemenangan (opsional)

**Fase 2 — Full Leaderboard:**
- Tabel lengkap semua pemain
- Kolom: Peringkat, Avatar, Nickname, Skor, Jawaban Benar, Rata-rata Waktu
- Bisa scroll
- Tombol "Download Hasil (CSV)"
- Tombol "Main Lagi" (restart quiz yang sama)
- Tombol "Selesai"

#### 6.3.11 Final Leaderboard — Player View
- Posisi akhir pemain (besar, center)
- Jika top 3: animasi spesial (confetti, trophy)
- Skor akhir
- Statistik personal:
  - Jawaban benar: X/Y
  - Streak terpanjang
  - Rata-rata waktu menjawab
- Tombol "Kembali ke Beranda"

---

### 6.4 Sistem Scoring

#### 6.4.1 Perhitungan Poin
```
Base Points = Point Type Value (Standard: 1000, Double: 2000, None: 0)

Time Bonus Factor = 1 - (time_taken / time_limit / 2)
  → Menjawab instan: faktor = 1.0 (dapat 100% poin)
  → Menjawab di akhir: faktor = 0.5 (dapat 50% poin)

Streak Bonus = streak_count × 100 (max 500)
  → Streak 1: +100, Streak 2: +200, ..., Streak 5+: +500

Points Earned = floor(Base Points × Time Bonus Factor) + Streak Bonus
```

#### 6.4.2 Contoh Perhitungan
| Skenario | Base | Waktu | Faktor | Streak | Total |
|----------|------|-------|--------|--------|-------|
| Jawab cepat, streak 0 | 1000 | 2s/20s | 0.95 | 0 | 950 |
| Jawab cepat, streak 3 | 1000 | 2s/20s | 0.95 | 300 | 1250 |
| Jawab lambat, streak 0 | 1000 | 18s/20s | 0.55 | 0 | 550 |
| Double points, cepat | 2000 | 3s/20s | 0.925 | 0 | 1850 |
| Jawab salah | 0 | - | - | 0 | 0 |

---

### 6.5 Guest Play (Bermain Tanpa Akun)

- Pemain **tidak perlu registrasi** untuk bermain
- Cukup masukkan game code, pilih nickname dan avatar
- Data game disimpan di `game_players` dengan `user_id = null`
- Pemain yang login: `user_id` terisi, bisa lihat riwayat game

---

### 6.6 Internationalization (i18n)

#### 6.6.1 Mekanisme
- Menggunakan **Vue I18n** untuk frontend
- Menggunakan **Laravel Localization** untuk backend (validation messages, emails)
- Bahasa disimpan di:
  - `resources/js/locales/id.json` — Bahasa Indonesia
  - `resources/js/locales/en.json` — English
  - `lang/id/` — Laravel backend Indonesia
  - `lang/en/` — Laravel backend English

#### 6.6.2 Switching Bahasa
- Dropdown bahasa di navbar (bendera + nama bahasa)
- Disimpan di profil user (kolom `locale`)
- Guest: disimpan di localStorage + cookie
- Bahasa default: **Indonesia**

#### 6.6.3 Cakupan Terjemahan
- Semua UI text (navigasi, tombol, label, placeholder)
- Validation messages
- Flash messages / notifications
- Error pages
- Motivational messages di scoreboard
- Email notifications

---

### 6.7 Admin Panel

Admin panel hanya bisa diakses oleh user dengan `is_admin = true`. Menggunakan middleware authorization.

#### 6.7.1 Dashboard Admin
- Statistik overview:
  - Total user (terdaftar)
  - Total kuis (aktif / draf / dihapus)
  - Total game session (hari ini / bulan ini / total)
  - Total pemain (registered / guest)
- Grafik aktivitas (game session per hari, 30 hari terakhir)
- Daftar game session terbaru

#### 6.7.2 Manajemen User
- Daftar semua user dengan search & filter
- Kolom: Avatar, Nama, Email, Kuis Dibuat, Game Dimainkan, Tanggal Daftar, Status
- Aksi: Lihat detail, Nonaktifkan, Hapus, Toggle admin
- Detail user: profil, daftar kuis, riwayat game

#### 6.7.3 Manajemen Kuis
- Daftar semua kuis (semua user)
- Filter: Publik, Privat, Dipublikasi, Draf, Dihapus (trashed)
- Kolom: Judul, Kreator, Pertanyaan, Visibility, Status, Dibuat
- Aksi: Lihat detail, Hapus, Restore (soft deleted)

#### 6.7.4 Riwayat Game
- Daftar semua game session
- Filter: Status (Waiting, Playing, Finished), Tanggal
- Kolom: Game Code, Kuis, Host, Pemain, Status, Waktu
- Aksi: Lihat detail, Hapus

#### 6.7.5 Pengaturan Sistem
- Pengaturan umum (nama aplikasi, bahasa default)
- Toggle fitur: registrasi, email verification, guest play
- Maintenance mode

---

## 7. Halaman & Route

### 7.1 Halaman Publik

| Route | Halaman | Deskripsi |
|-------|---------|-----------|
| `GET /` | Landing Page | Hero section, CTA join game & buat kuis |
| `GET /play` | Join Game | Input game code |
| `GET /play/{code}` | Player Lobby | Input nickname, pilih avatar, tunggu host |
| `GET /play/{code}/game` | Player Game | Layar bermain pemain |

### 7.2 Halaman Auth

| Route | Halaman | Deskripsi |
|-------|---------|-----------|
| `GET /login` | Login | |
| `GET /register` | Register | |
| `GET /forgot-password` | Forgot Password | |
| `GET /reset-password/{token}` | Reset Password | |

### 7.3 Halaman Kreator (Auth Required)

| Route | Halaman | Deskripsi |
|-------|---------|-----------|
| `GET /dashboard` | Dashboard | Daftar kuis kreator |
| `GET /quizzes/create` | Buat Kuis | Editor kuis baru |
| `GET /quizzes/{quiz}/edit` | Edit Kuis | Editor kuis existing |
| `GET /quizzes/{quiz}/host` | Host Game | Lobby + kontrol game |

### 7.4 API Routes (Internal - Inertia)

| Method | Route | Controller | Deskripsi |
|--------|-------|------------|-----------|
| POST | `/quizzes` | QuizController@store | Buat kuis |
| PUT | `/quizzes/{quiz}` | QuizController@update | Update kuis |
| DELETE | `/quizzes/{quiz}` | QuizController@destroy | Hapus kuis |
| POST | `/quizzes/{quiz}/duplicate` | QuizController@duplicate | Duplikat kuis |
| POST | `/quizzes/{quiz}/publish` | QuizController@publish | Publikasi kuis |
| POST | `/quizzes/{quiz}/questions` | QuestionController@store | Tambah pertanyaan |
| PUT | `/questions/{question}` | QuestionController@update | Edit pertanyaan |
| DELETE | `/questions/{question}` | QuestionController@destroy | Hapus pertanyaan |
| POST | `/questions/reorder` | QuestionController@reorder | Reorder pertanyaan |
| POST | `/game-sessions` | GameSessionController@store | Buat game session |
| POST | `/game-sessions/{session}/start` | GameSessionController@start | Mulai game |
| POST | `/game-sessions/{session}/next` | GameSessionController@next | Pertanyaan berikutnya |
| POST | `/game-sessions/{session}/end` | GameSessionController@end | Akhiri game |
| GET | `/game-sessions/{session}/results` | GameSessionController@results | Hasil game |
| GET | `/game-sessions/{session}/export` | GameSessionController@export | Export CSV |

### 7.5 API Routes (Player - Stateless)

| Method | Route | Deskripsi |
|--------|-------|-----------|
| POST | `/api/games/join` | Join game (code + nickname + avatar) |
| POST | `/api/games/{session}/answer` | Kirim jawaban |
| GET | `/api/games/{session}/status` | Status game saat ini |

### 7.6 Halaman Admin (Admin Required)

| Route | Halaman | Deskripsi |
|-------|---------|-----------|
| `GET /admin` | Admin Dashboard | Statistik & overview |
| `GET /admin/users` | Manajemen User | Daftar & kelola user |
| `GET /admin/users/{user}` | Detail User | Profil & aktivitas user |
| `GET /admin/quizzes` | Manajemen Kuis | Daftar semua kuis |
| `GET /admin/quizzes/{quiz}` | Detail Kuis | Detail kuis & pertanyaan |
| `GET /admin/games` | Riwayat Game | Daftar game session |
| `GET /admin/games/{session}` | Detail Game | Detail game session |
| `GET /admin/settings` | Pengaturan | Pengaturan sistem |

---

## 8. Broadcasting Events (WebSocket)

### 8.1 Channels

| Channel | Tipe | Deskripsi |
|---------|------|-----------|
| `game.{sessionId}` | Presence | Channel utama game session |

### 8.2 Events

| Event | Direction | Payload | Deskripsi |
|-------|-----------|---------|-----------|
| `PlayerJoined` | Server → All | `{player, totalPlayers}` | Pemain baru bergabung |
| `PlayerLeft` | Server → All | `{player, totalPlayers}` | Pemain disconnect |
| `GameStarted` | Server → All | `{totalQuestions}` | Host memulai game |
| `QuestionStarted` | Server → All | `{question, questionNumber, totalQuestions, timeLimit}` | Pertanyaan baru dimulai |
| `AnswerSubmitted` | Server → Host | `{answeredCount, totalPlayers}` | Update counter jawaban |
| `TimerEnded` | Server → All | `{questionId}` | Waktu habis |
| `AnswerRevealed` | Server → All | `{correctAnswer, stats, playerResults}` | Jawaban benar ditampilkan |
| `ScoreboardUpdated` | Server → All | `{leaderboard, playerPositions}` | Update scoreboard |
| `GameEnded` | Server → All | `{finalLeaderboard, podium}` | Game selesai |

---

## 9. Komponen Vue

### 9.1 Layout Components

| Komponen | Deskripsi |
|----------|-----------|
| `AppLayout.vue` | Layout utama dengan navbar |
| `GuestLayout.vue` | Layout untuk halaman auth |
| `GameLayout.vue` | Layout fullscreen untuk game (tanpa navbar) |

### 9.2 Quiz Creator Components

| Komponen | Deskripsi |
|----------|-----------|
| `QuizCard.vue` | Kartu kuis di dashboard |
| `QuizEditor.vue` | Container editor kuis |
| `QuestionList.vue` | Sidebar daftar pertanyaan |
| `QuestionListItem.vue` | Item pertanyaan di sidebar |
| `QuestionEditor.vue` | Editor pertanyaan utama |
| `AnswerOption.vue` | Slot jawaban berwarna |
| `QuestionProperties.vue` | Sidebar properti pertanyaan |
| `ImageUploader.vue` | Upload gambar pertanyaan |

### 9.3 Game Components — Host

| Komponen | Deskripsi |
|----------|-----------|
| `HostLobby.vue` | Lobby menunggu pemain |
| `HostCountdown.vue` | Countdown 3-2-1 |
| `HostQuestion.vue` | Tampilan pertanyaan |
| `HostAnswerReveal.vue` | Reveal jawaban benar + statistik |
| `HostScoreboard.vue` | Top 5 leaderboard |
| `HostPodium.vue` | Podium juara 1-2-3 |
| `HostFinalLeaderboard.vue` | Leaderboard akhir lengkap |

### 9.4 Game Components — Player

| Komponen | Deskripsi |
|----------|-----------|
| `PlayerJoin.vue` | Input game code |
| `PlayerSetup.vue` | Input nickname & pilih avatar |
| `PlayerLobby.vue` | Menunggu host mulai |
| `PlayerCountdown.vue` | Countdown 3-2-1 |
| `PlayerQuestion.vue` | 4 tombol jawaban (warna + shape) |
| `PlayerAnswerResult.vue` | Hasil benar/salah |
| `PlayerScoreboard.vue` | Posisi & skor pemain |
| `PlayerFinalResult.vue` | Hasil akhir personal |

### 9.5 Shared Components

| Komponen | Deskripsi |
|----------|-----------|
| `AvatarGrid.vue` | Grid pemilihan avatar |
| `AvatarDisplay.vue` | Tampilan avatar (SVG) |
| `CountdownOverlay.vue` | Overlay countdown 3-2-1 |
| `TimerBar.vue` | Progress bar timer |
| `ConfettiEffect.vue` | Animasi confetti |
| `LanguageSwitcher.vue` | Dropdown ganti bahasa |
| `GameCodeInput.vue` | Input 6 digit game code |
| `ScoreAnimation.vue` | Animasi penambahan skor |
| `StreakBadge.vue` | Badge streak counter |

---

## 10. Animasi & Efek Visual

### 10.1 Daftar Animasi

| Animasi | Lokasi | Deskripsi | Implementasi |
|---------|--------|-----------|--------------|
| **Bounce In** | Lobby | Pemain baru muncul | CSS `@keyframes bounceIn` |
| **Float Idle** | Lobby | Avatar mengambang | CSS `@keyframes float` |
| **Zoom Countdown** | Pre-question | 3..2..1 zoom in-out | Vue `<Transition>` + CSS scale |
| **Slide In** | Question | Pertanyaan masuk dari atas | CSS `transform: translateY` |
| **Timer Drain** | Question | Bar timer mengecil | CSS `width` transition |
| **Ripple Press** | Player answer | Efek ripple saat tekan | CSS `@keyframes ripple` |
| **Shake** | Wrong answer | Layar goyang ringan | CSS `@keyframes shake` |
| **Confetti** | Correct/Podium | Partikel jatuh | Canvas atau CSS particles |
| **Score Count Up** | Score reveal | Angka naik bertahap | `requestAnimationFrame` |
| **Bar Grow** | Answer stats | Bar chart tumbuh | CSS `height` transition |
| **Slide Rank** | Scoreboard | Pemain naik/turun | Vue `<TransitionGroup>` + FLIP |
| **Podium Rise** | Final | Podium naik dari bawah | CSS `transform: translateY` |
| **Crown Drop** | Podium | Crown jatuh ke juara 1 | CSS `@keyframes drop` |
| **Pulse Glow** | Correct answer | Jawaban benar bersinar | CSS `@keyframes pulse` |
| **Fade Dim** | Wrong answers | Jawaban salah pudar | CSS `opacity` transition |
| **Stagger Reveal** | Scoreboard | Nama muncul bertahap | `transition-delay` per item |

### 10.2 Prinsip Animasi
- **Durasi:** 200-500ms untuk micro-interactions, 500-1500ms untuk transitions besar
- **Easing:** `cubic-bezier(0.34, 1.56, 0.64, 1)` untuk bounce, `ease-out` untuk slide
- **Performance:** Gunakan `transform` dan `opacity` saja (GPU-accelerated)
- **Reduced Motion:** Hormati `prefers-reduced-motion` — kurangi/hilangkan animasi

---

## 11. Desain Visual

### 11.1 Filosofi Desain
- **Minimalis** — UI bersih, whitespace cukup, tidak cluttered
- **Ceria** — Warna-warna cerah, rounded corners, friendly
- **Fungsional** — Setiap elemen punya tujuan jelas
- **Responsif** — Mobile-first untuk player, desktop-first untuk host/kreator

### 11.2 Color Palette

```
Primary:     #6C5CE7 (Ungu cerah — brand color)
Secondary:   #00CEC9 (Teal/Cyan)
Background:  #F8F9FE (Abu-abu sangat terang)
Surface:     #FFFFFF (Putih)
Text:        #2D3436 (Hitam keabu-abuan)
Text Light:  #636E72 (Abu-abu)

Answer Colors:
  Red:       #FF6B6B
  Blue:      #4ECDC4 → diganti → #5B8DEF  
  Yellow:    #FECA57
  Green:     #48DBAB

Accent:
  Success:   #00B894
  Warning:   #FDCB6E
  Error:     #FF7675
  Info:      #74B9FF

Gradients:
  Brand:     linear-gradient(135deg, #6C5CE7, #A29BFE)
  Festive:   linear-gradient(135deg, #6C5CE7, #FF6B6B, #FECA57)
```

### 11.3 Typography
- **Font Family:** `Inter` (sans-serif) — clean, modern, great readability
- **Headings:** Bold / Extra Bold
- **Body:** Regular / Medium
- **Scale:**
  - Display: 48-72px (countdown, game code)
  - H1: 32-36px
  - H2: 24-28px
  - H3: 20px
  - Body: 16px
  - Small: 14px
  - Caption: 12px

### 11.4 Border Radius
- Cards: `12px`
- Buttons: `8px` (regular), `12px` (large), `full` (avatar)
- Inputs: `8px`
- Answer blocks: `12px`

### 11.5 Shadows
```css
--shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
--shadow-md: 0 4px 6px rgba(0,0,0,0.07);
--shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
--shadow-answer: 0 4px 0 rgba(0,0,0,0.15); /* efek "tombol tebal" */
```

---

## 12. Responsive Design

### 12.1 Breakpoints (Tailwind Default)
| Breakpoint | Min Width | Target |
|------------|-----------|--------|
| sm | 640px | Mobile landscape |
| md | 768px | Tablet |
| lg | 1024px | Desktop |
| xl | 1280px | Large desktop |
| 2xl | 1536px | Proyektor |

### 12.2 Layout Strategy

| Halaman | Mobile | Desktop |
|---------|--------|---------|
| Landing | Single column, stacked | Two column hero |
| Dashboard | 1 col cards | 3 col grid |
| Quiz Editor | Bottom sheet properties | 3 column layout |
| Host Game | Responsive, min 768px recommended | Full layout |
| Player Game | Full screen, optimized | Full screen, centered |

---

## 13. Infrastructure & Deployment

### 13.1 Production Environment
- **Domain:** yahoot.web.id
- **Container Runtime:** Docker + Docker Compose
- **Reverse Proxy:** Nginx (host-level) → Docker containers
- **SSL:** Let's Encrypt (Certbot) auto-renewal

### 13.2 Docker Services

| Service | Image / Build | Port | Deskripsi |
|---------|--------------|------|-----------|
| `app` | PHP 8.4 FPM Alpine (custom) | 9000 | Laravel application |
| `nginx` | nginx:1.27-alpine | 8000 | Web server |
| `postgres` | postgres:17-alpine | 5432 | Database |
| `redis` | redis:7-alpine | 6379 | Cache & queue |
| `reverb` | PHP 8.4 FPM Alpine (custom) | 8080 | WebSocket server |
| `queue` | PHP 8.4 FPM Alpine (custom) | — | Queue worker |
| `scheduler` | PHP 8.4 FPM Alpine (custom) | — | Task scheduler |

### 13.3 File Storage
- **Provider:** IDCloudHost Object Storage (S3-compatible)
- **Endpoint:** `https://is3.cloudhost.id`
- **Bucket:** `yahoot`
- **Digunakan untuk:** Cover image kuis, gambar pertanyaan, export CSV
- **Konfigurasi:** Menggunakan Laravel Flysystem S3 driver (`league/flysystem-aws-s3-v3`)

### 13.4 Email Service
- **Provider:** Brevo (Sendinblue)
- **Protokol:** SMTP relay (`smtp-relay.brevo.com:587`)
- **From Address:** `noreply@yahoot.web.id`
- **Digunakan untuk:** Verifikasi email, reset password, notifikasi

### 13.5 Database
- **Engine:** PostgreSQL 17
- **Persistent Volume:** Docker named volume `postgres_data`
- **Backup:** pg_dump via scheduled task

### 13.6 Deployment Flow
```
1. Push ke branch `main`
2. SSH ke server
3. git pull origin main
4. docker compose build --no-cache app
5. docker compose up -d
6. docker compose exec app php artisan migrate --force
7. docker compose exec app php artisan config:cache
8. docker compose exec app php artisan route:cache
9. docker compose exec app php artisan view:cache
```

---

## 14. Keamanan

| Aspek | Implementasi |
|-------|-------------|
| Auth | Laravel Fortify + Jetstream + Sanctum |
| CSRF | Laravel built-in CSRF protection |
| Authorization | Policies (QuizPolicy, GameSessionPolicy) |
| Rate Limiting | Rate limiter pada join game & submit answer |
| Input Validation | Form Request classes untuk setiap endpoint |
| File Upload | Validasi tipe & ukuran gambar (max 2MB, jpg/png/gif/webp) |
| Game Code | Random 6 digit, regenerate jika collision |
| WebSocket Auth | Private/Presence channel authorization |
| XSS | Vue auto-escaping + Laravel sanitization |
| SQL Injection | Eloquent ORM (parameterized queries) |

---

## 15. Performance

| Aspek | Strategi |
|-------|----------|
| Asset Loading | Vite code splitting per route |
| Images | WebP format, lazy loading, max 2MB |
| Real-time | Laravel Reverb (lightweight WebSocket) |
| Database | Eager loading, database indexes |
| Caching | Cache game session data di memory |
| Animation | GPU-accelerated transforms only |
| Bundle Size | Tree-shaking, dynamic imports |

---

## 16. Struktur File Project

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php
│   │   ├── QuizController.php
│   │   ├── QuestionController.php
│   │   ├── GameSessionController.php
│   │   ├── PlayerController.php
│   │   ├── LanguageController.php
│   │   └── Admin/
│   │       ├── AdminDashboardController.php
│   │       ├── AdminUserController.php
│   │       ├── AdminQuizController.php
│   │       ├── AdminGameController.php
│   │       └── AdminSettingController.php
│   ├── Requests/
│   │   ├── Quiz/
│   │   │   ├── StoreQuizRequest.php
│   │   │   └── UpdateQuizRequest.php
│   │   ├── Question/
│   │   │   ├── StoreQuestionRequest.php
│   │   │   ├── UpdateQuestionRequest.php
│   │   │   └── ReorderQuestionsRequest.php
│   │   └── Game/
│   │       ├── JoinGameRequest.php
│   │       └── SubmitAnswerRequest.php
│   └── Middleware/
│       ├── SetLocale.php
│       └── EnsureUserIsAdmin.php
├── Models/
│   ├── User.php
│   ├── Quiz.php
│   ├── Question.php
│   ├── Answer.php
│   ├── GameSession.php
│   ├── GamePlayer.php
│   └── PlayerAnswer.php
├── Events/
│   ├── PlayerJoined.php
│   ├── PlayerLeft.php
│   ├── GameStarted.php
│   ├── QuestionStarted.php
│   ├── AnswerSubmitted.php
│   ├── TimerEnded.php
│   ├── AnswerRevealed.php
│   ├── ScoreboardUpdated.php
│   └── GameEnded.php
├── Policies/
│   ├── QuizPolicy.php
│   └── GameSessionPolicy.php
├── Services/
│   ├── GameService.php          # Game logic & state management
│   ├── ScoringService.php       # Scoring calculation
│   └── GameCodeService.php      # Game code generation
├── Enums/
│   ├── GameStatus.php
│   ├── QuestionType.php
│   ├── PointType.php
│   ├── QuizVisibility.php
│   └── AnswerColor.php

database/
├── migrations/
│   ├── xxxx_create_quizzes_table.php
│   ├── xxxx_create_questions_table.php
│   ├── xxxx_create_answers_table.php
│   ├── xxxx_create_game_sessions_table.php
│   ├── xxxx_create_game_players_table.php
│   └── xxxx_create_player_answers_table.php
├── factories/
│   ├── QuizFactory.php
│   ├── QuestionFactory.php
│   ├── AnswerFactory.php
│   ├── GameSessionFactory.php
│   ├── GamePlayerFactory.php
│   └── PlayerAnswerFactory.php
├── seeders/
│   └── SampleQuizSeeder.php

resources/
├── js/
│   ├── app.js
│   ├── i18n.js                  # Vue I18n setup
│   ├── composables/
│   │   ├── useGame.js           # Game state composable
│   │   ├── useTimer.js          # Timer composable
│   │   ├── useScoreboard.js     # Scoreboard composable
│   │   └── useSound.js          # Sound effects composable
│   ├── locales/
│   │   ├── id.json              # Bahasa Indonesia
│   │   └── en.json              # English  
│   ├── Components/
│   │   ├── Avatar/
│   │   │   ├── AvatarGrid.vue
│   │   │   ├── AvatarDisplay.vue
│   │   │   └── avatars/         # SVG avatar components
│   │   ├── Game/
│   │   │   ├── CountdownOverlay.vue
│   │   │   ├── TimerBar.vue
│   │   │   ├── ConfettiEffect.vue
│   │   │   ├── ScoreAnimation.vue
│   │   │   ├── StreakBadge.vue
│   │   │   └── GameCodeInput.vue
│   │   ├── Quiz/
│   │   │   ├── QuizCard.vue
│   │   │   ├── QuizEditor.vue
│   │   │   ├── QuestionList.vue
│   │   │   ├── QuestionListItem.vue
│   │   │   ├── QuestionEditor.vue
│   │   │   ├── AnswerOption.vue
│   │   │   ├── QuestionProperties.vue
│   │   │   └── ImageUploader.vue
│   │   └── UI/
│   │       ├── LanguageSwitcher.vue
│   │       ├── Modal.vue
│   │       ├── Dropdown.vue
│   │       └── Toast.vue
│   ├── Layouts/
│   │   ├── AppLayout.vue
│   │   ├── GuestLayout.vue
│   │   └── GameLayout.vue
│   └── Pages/
│       ├── Landing.vue
│       ├── Dashboard.vue
│       ├── Auth/
│       │   ├── Login.vue
│       │   └── Register.vue
│       ├── Quiz/
│       │   ├── Create.vue
│       │   └── Edit.vue
│       ├── Host/
│       │   ├── Lobby.vue
│       │   ├── Game.vue
│       │   └── Results.vue
│       ├── Player/
│       │   ├── Join.vue
│       │   ├── Setup.vue
│       │   ├── Lobby.vue
│       │   └── Game.vue
│       └── Admin/
│           ├── Dashboard.vue
│           ├── Users/
│           │   ├── Index.vue
│           │   └── Show.vue
│           ├── Quizzes/
│           │   ├── Index.vue
│           │   └── Show.vue
│           ├── Games/
│           │   ├── Index.vue
│           │   └── Show.vue
│           └── Settings.vue

lang/
├── id/
│   ├── validation.php
│   ├── auth.php
│   └── messages.php
└── en/
    ├── validation.php
    ├── auth.php
    └── messages.php

tests/
├── Feature/
│   ├── Quiz/
│   │   ├── CreateQuizTest.php
│   │   ├── EditQuizTest.php
│   │   ├── DeleteQuizTest.php
│   │   └── DuplicateQuizTest.php
│   ├── Question/
│   │   ├── CreateQuestionTest.php
│   │   ├── EditQuestionTest.php
│   │   └── ReorderQuestionsTest.php
│   ├── Game/
│   │   ├── CreateGameSessionTest.php
│   │   ├── JoinGameTest.php
│   │   ├── SubmitAnswerTest.php
│   │   ├── ScoringTest.php
│   │   └── GameFlowTest.php
│   ├── Admin/
│   │   ├── AdminDashboardTest.php
│   │   ├── AdminUserManagementTest.php
│   │   ├── AdminQuizManagementTest.php
│   │   └── AdminGameManagementTest.php
│   └── Auth/
│       └── ProfileAvatarTest.php
└── Unit/
    ├── ScoringServiceTest.php
    └── GameCodeServiceTest.php

docker/
├── nginx/
│   ├── default.conf              # Container nginx config
│   └── yahoot.web.id.conf         # Production reverse proxy
└── php/
    ├── php.ini                    # PHP configuration
    └── opcache.ini                # OPcache configuration

Dockerfile                         # Multi-stage Docker build
docker-compose.yml                 # Docker Compose services
.dockerignore                      # Docker ignore rules
```

---

## 17. Milestone & Fase Pengembangan

### Fase 1 — Foundation (Sprint 1-2)
- [x] Setup project Laravel + Inertia + Vue
- [x] Database migrations & models
- [x] Factories & seeders
- [x] Enums
- [x] Internationalization (i18n) setup
- [x] Avatar system (SVG components)
- [x] Landing page
- [x] User profile (avatar & language selection)
- [x] Middleware SetLocale
- [x] S3-compatible storage setup (IDCloudHost)
- [x] Email service setup (Brevo/SMTP)
- [x] Docker & Docker Compose setup
- [x] Nginx reverse proxy configuration
- [x] Admin middleware & is_admin migration
- [x] .env.example & environment documentation

### Fase 2 — Quiz Creator (Sprint 3-4)
- [x] Dashboard kreator
- [x] Quiz CRUD
- [x] Question CRUD
- [x] Answer management
- [x] Image upload (S3)
- [x] Drag & drop reorder
- [x] Quiz validation & publish
- [x] Duplicate quiz
- [x] Tests untuk semua Quiz & Question endpoints

### Fase 3 — Game Engine (Sprint 5-7)
- [x] Laravel Reverb setup
- [x] Game session creation & game code
- [x] Lobby system (host & player)
- [x] Join game (guest & authenticated)
- [x] Real-time player join/leave
- [x] Question flow (countdown → question → answer → reveal → scoreboard)
- [x] Timer system
- [x] Answer submission & validation
- [x] Scoring service
- [x] Scoreboard per pertanyaan
- [x] Tests untuk game flow

### Fase 4 — Polish & Animation (Sprint 8-9)
- [x] Semua animasi (lihat Section 10)
- [x] Confetti effect
- [x] Podium & final leaderboard
- [ ] Sound effects (opsional)
- [x] Motivational messages
- [x] CSV export
- [ ] Responsive testing
- [ ] Performance optimization
- [x] Accessibility (reduced motion)

### Fase 5 — Admin Panel & Launch (Sprint 10-11)
- [ ] Admin dashboard (statistik & overview)
- [ ] Manajemen user (CRUD, toggle admin)
- [ ] Manajemen kuis (view, delete, restore)
- [ ] Riwayat game session
- [ ] Pengaturan sistem
- [ ] Integration testing
- [ ] E2E testing semua flow
- [ ] Bug fixing
- [ ] Documentation
- [ ] Deployment & launch 🚀

---

## 18. Hal yang Tidak Termasuk (Out of Scope v1)

Fitur berikut **tidak** akan dikembangkan di versi 1:
- Tipe pertanyaan selain Multiple Choice & True/False (Puzzle, Poll, Slider, dll)
- Team mode
- Kuis berbasis AI / auto-generate
- Laporan analitik mendalam
- Integrasi LMS
- Custom themes
- Monetisasi / paywall
- Mobile app (native)
- Audio/video dalam pertanyaan
- Timer per-pemain yang berbeda

---

## 19. Glosarium

| Istilah | Definisi |
|---------|----------|
| **Kreator** | Pengguna yang membuat dan mengelola kuis |
| **Pemain** | Pengguna yang bergabung dan bermain di game session |
| **Host** | Kreator yang sedang menjalankan game session (menampilkan di layar besar) |
| **Game Session** | Satu kali permainan kuis dengan kode unik |
| **Game Code** | Kode 6 digit untuk bergabung ke game session |
| **Streak** | Jumlah jawaban benar berturut-turut |
| **Podium** | Penampilan 3 besar di akhir game |
| **Leaderboard** | Daftar peringkat seluruh pemain |
| **Scoreboard** | Peringkat sementara setelah setiap pertanyaan |
