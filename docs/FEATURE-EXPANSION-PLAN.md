# Yahoot — Feature Expansion Plan

**Tanggal:** 2026-06-15
**Status:** Sebagian terimplementasi
**Sumber:** Permintaan penambahan fitur baru di atas codebase saat ini (Laravel 12 + Vue 3 + Inertia v2 + Reverb)

### Progres Implementasi

- ✅ **Quick wins:** Poll (1), Nickname Generator (5), Kategori & Tag (8), Sound customization (14b).
- ✅ **Fondasi data (Phase 2):** Global Leaderboard / Ranking (7), Badge & Achievement (6),
  Advanced Analytics (13a PDF/print report, 13b difficulty insights, 13c progress tracking).
- ✅ **Engagement berat (Phase 3):** Power-ups (2), Team Mode (3), Reactions/Emoji (14a).
- ✅ **Phase 1-3 gap-fixing pass (2026-06-15):** poll completeness bug di Editor, init
  `powerups_available` saat join, setting eksplisit `powerups_enabled`/`reactions_enabled`/
  `team_selection` saat start game, host toggle UI di Dashboard untuk power-up/reaksi/mode
  tim, pemilihan tim manual saat join (`GET /api/games/code/{code}/info`), serta grouping
  per-tim di lobby host + standings tim di podium finished.
- ✅ **Phase 1-3 gap-fixing pass #2 (2026-06-15):** `apiUsePowerup` kini menolak request
  setelah pertanyaan tidak lagi aktif (status `reviewing`) — sebelumnya `fifty_fifty` bisa
  membocorkan 2 jawaban salah meski hasil sudah di-reveal; `powerupsAvailable` di
  `Player/Game.vue` kini di-seed dari data pemain hasil join (bukan list statis), agar
  power-up yang sudah dipakai tetap tampak terpakai setelah refresh halaman.
- ⬜ Belum: Practice/Solo (4), Import/Export (9), Question Bank (10),
  Marketplace/Explore (11), AI Question Generator (12), Live Chat (14a chat — emoji done).

Dokumen ini merinci rencana implementasi untuk 14 fitur baru, dikelompokkan menjadi
5 group berdasarkan domain & ketergantungan. Setiap fitur memuat: perubahan data
model, backend, frontend, event WebSocket (bila ada), i18n, testing, dan estimasi
effort.

## Ringkasan & Urutan Pengerjaan

| # | Fitur | Group | Effort | Ketergantungan |
|---|-------|-------|--------|----------------|
| 1 | Poll/Survey question type | A. Game Mechanics | S | — |
| 2 | Power-ups | A. Game Mechanics | L | — |
| 3 | Team Mode | A. Game Mechanics | L | — |
| 4 | Practice/Solo Mode | A. Game Mechanics | M | — |
| 5 | Nickname Generator | B. Player Identity | S | — |
| 6 | Badge & Achievement | B. Player Identity | M | analytics tracking |
| 7 | Global Leaderboard / User Ranking | B. Player Identity | M | — |
| 8 | Kategori & Tag kuis | C. Quiz Management | S | — |
| 9 | Import / Export kuis | C. Quiz Management | M | — |
| 10 | Question Bank | C. Quiz Management | M | kategori/tag (8) |
| 11 | Quiz Template / Marketplace publik | C. Quiz Management | M | kategori/tag (8) |
| 12 | AI Question Generator | C. Quiz Management | M | — |
| 13 | Advanced Analytics (PDF, difficulty, progress) | D. Analytics | M | — |
| 14 | Real-time Enhancements (chat/emoji, sound) | E. Real-time | M | — |

Effort: **S** = 1–2 hari, **M** = 3–5 hari, **L** = 1–2 minggu (estimasi 1 dev).

**Urutan rekomendasi:** mulai dari fitur S tanpa dependency (1, 5, 8), lalu fitur
analytics-tracking (13) karena menjadi fondasi Badge (6) & difficulty insights,
kemudian sisanya.

---

# GROUP A — Game Mechanics

## 1. Poll/Survey Question Type

**Tujuan:** Soal tanpa jawaban benar — hanya mengumpulkan opini. Hasil ditampilkan
sebagai bar distribusi, tidak ada poin/streak.

### Data Model
- **`app/Enums/QuestionType.php`** — tambah case `Poll = 'poll'`.
- Tidak perlu migration baru. Kolom `answers.is_correct` cukup di-set `false` untuk
  semua opsi poll. Tambah method helper di enum:
  ```php
  public function isScored(): bool
  {
      return $this !== self::Poll;
  }
  ```

### Backend
- **`ScoringService::calculate()`** — jika `$question->type->isScored() === false`,
  langsung return `['points_earned' => 0, 'streak_bonus' => 0, 'new_streak' => $player->streak]`
  (streak tidak berubah, tidak putus).
- **`PlayerController::apiAnswer()`** — saat tipe Poll, set `is_correct = false` dan
  jangan ubah streak. Tetap simpan `PlayerAnswer` agar distribusi terhitung.
- **`RevealService::getRevealData()`** — untuk Poll, `correctAnswer` dikirim kosong
  (`['answers' => []]`); `stats.answer_counts` tetap dihitung. Frontend memakai flag
  `isPoll` untuk menyembunyikan indikator benar/salah.
- **Form Request** `StoreQuestionRequest`/`UpdateQuestionRequest` — saat `type=poll`,
  tidak wajib ada `is_correct=true`.

### Frontend
- **`Components/Quiz/QuestionProperties.vue`** — tambah opsi `poll` di dropdown tipe.
  `getDefaultAnswers('poll')` mengembalikan 4 opsi warna tanpa `is_correct`.
- **`Components/Quiz/QuestionEditor.vue`** — sembunyikan toggle "correct answer" saat
  tipe poll.
- **`Pages/Player/Game.vue`** — pada state `result`, jika poll: tampilkan "Terima kasih
  atas suaramu!" alih-alih correct/incorrect.
- **`Pages/Host/Game.vue`** — saat reveal poll, semua bar netral (tanpa highlight hijau).

### i18n
Tambah di `quiz` dan `game`: `poll`, `poll_thanks`, `poll_no_correct_answer`.

### Testing
- `tests/Feature/Game/PollQuestionTest.php`: poll tidak memberi poin, tidak memutus
  streak, distribusi terhitung, reveal tidak mengirim correctAnswer.
- `tests/Unit/ScoringServiceTest.php`: tambah case poll → 0 poin.

**Effort:** S

---

## 2. Power-ups

**Tujuan:** Item game-show yang bisa dipakai pemain saat menjawab: `double_points`,
`fifty_fifty` (hapus 2 opsi salah), `freeze_timer` (jeda timer 5 detik). Pemain
mendapat jatah power-up terbatas per game.

### Data Model
- **Enum baru** `app/Enums/PowerUpType.php`: `DoublePoints`, `FiftyFifty`, `FreezeTimer`
  dengan method `label()` dan `icon()`.
- **Migration** `add_powerups_to_game_players_table`: kolom JSON
  `powerups_available` (default `["double_points","fifty_fifty","freeze_timer"]`) dan
  `powerups_used` (default `[]`).
- **Migration** `add_powerup_to_player_answers_table`: kolom `powerup_used` (string,
  nullable) untuk mencatat power-up yang dipakai di jawaban itu.
- **`GamePlayer`** & **`PlayerAnswer`** — tambah ke `$fillable` + cast array.
- **`GameSession.settings`** (JSON sudah ada) — flag `powerups_enabled` (bool) agar
  host bisa mematikan fitur per game.

### Backend
- **Controller baru / method** `PlayerController::apiUsePowerup()`
  (route `POST /api/games/{gameSession}/powerup`):
  - Validasi `player_id`, `player_token`, `powerup` (PowerUpType), `question_id`.
  - Pastikan power-up masih tersedia & belum dipakai di pertanyaan ini.
  - Pindahkan dari `powerups_available` ke `powerups_used`.
  - Untuk `fifty_fifty`: server memilih 2 opsi salah untuk disembunyikan dan
    mengembalikannya di response (jangan percaya client).
  - Untuk `freeze_timer`/`double_points`: cukup catat; efek diterapkan di
    `apiAnswer`.
- **`ScoringService::calculate()`** — terima parameter opsional `?PowerUpType $powerup`.
  Jika `DoublePoints`, kalikan `points_earned` 2× (sebelum streak bonus).
- **`PlayerController::apiAnswer()`** — baca `powerup_used` dari `PlayerAnswer` draft /
  request, teruskan ke scoring, simpan di kolom.
- **Event baru** `PowerUpUsed` (broadcast `game.{id}`) opsional — agar host melihat
  siapa memakai power-up (untuk efek visual). Field: `playerId`, `nickname`, `powerup`.

### Frontend
- **`Composables/useGame.js`** — tambah method `usePowerup(playerId, powerup, token)`
  + listener `PowerUpUsed`. Simpan state `powerupsAvailable`.
- **Komponen baru** `Components/Game/PowerUpBar.vue` — tampil di `Pages/Player/Game.vue`
  state `question`: tombol-tombol power-up dengan ikon, disabled jika sudah dipakai.
- **`useTimer.js`** — tambah method `freeze(durationMs)` untuk `freeze_timer`
  (menahan `timeRemaining` selama durasi).
- **`Pages/Player/Game.vue`** — saat `fifty_fifty`, hapus 2 opsi dari grid jawaban
  sesuai response server.
- **`Pages/Host/Game.vue`** — badge kecil saat pemain memakai power-up.

### i18n
Section baru `powerups`: nama, deskripsi, dan tooltip tiap power-up.

### Testing
- `tests/Feature/Game/PowerUpTest.php`: double points menggandakan skor; fifty-fifty
  mengembalikan tepat 2 opsi salah & idempotent; power-up hanya bisa dipakai sekali;
  power-up disabled saat `powerups_enabled=false`.

**Effort:** L — sentuh scoring, timer, event, dan UI player.

---

## 3. Team Mode

**Tujuan:** Pemain dikelompokkan ke dalam tim; skor diakumulasi per tim; leaderboard &
podium berbasis tim. Host memilih mode (Individual / Team) saat membuat GameSession.

### Data Model
- **Migration** `create_game_teams_table`: `id`, `game_session_id` (FK cascade),
  `name`, `color`, `score` (default 0), `created_at/updated_at`.
- **Migration** `add_team_id_to_game_players_table`: `game_team_id` (FK nullable
  cascade null).
- **`GameSession.settings`** — flag `mode` = `individual|team` dan `team_count`.
- **Model baru** `app/Models/GameTeam.php`: `belongsTo(GameSession)`,
  `hasMany(GamePlayer)`, fillable `game_session_id, name, color, score`.
- **`GamePlayer`** — tambah `game_team_id` ke fillable + relasi `team()`.

### Backend
- **`GameSessionController::store()`** — terima `mode` & `team_count`; jika team, buat
  N `GameTeam` (warna dari palet tetap).
- **Player join flow** (`PlayerController::apiJoin()`) — saat team mode, assign pemain
  ke tim dengan anggota paling sedikit (auto-balance), atau biarkan pemain memilih tim
  (opsi `team_selection = auto|manual` di settings). Sertakan info tim di response.
- **`ScoringService`** — tambah `getTeamLeaderboard(int $gameSessionId): Collection`
  yang menjumlahkan skor anggota per tim. Saat menyimpan jawaban, increment
  `game_teams.score` selain `game_players.score`.
- **`RevealService` / `GameSessionController::end()`** — kirim leaderboard tim bila
  mode team. Tambah field `mode` & `teams` ke event payload.
- **Events** `ScoreboardUpdated`, `GameEnded`, `QuestionStarted` — extend
  `broadcastWith()` agar menyertakan data tim saat mode team (backward compatible:
  field opsional).

### Frontend
- **`Pages/Player/Join.vue`** — bila manual team selection: langkah pilih tim.
- **`Pages/Player/Game.vue`** — tampilkan badge tim pemain; scoreboard menampilkan
  ranking tim.
- **`Pages/Host/Game.vue`** — lobby dikelompokkan per tim; scoreboard & podium berbasis
  tim.
- **`Composables/useGame.js`** — state `teams`, `teamLeaderboard`; baca dari event.
- **Komponen baru** `Components/Game/TeamBadge.vue`.

### i18n
Section `team`: `team_mode`, `individual_mode`, `choose_team`, `team_score`,
`team_count`, nama tim default.

### Testing
- `tests/Feature/Game/TeamModeTest.php`: auto-balance merata; skor tim = jumlah anggota;
  leaderboard & podium tim benar; mode individual tidak terpengaruh.

**Effort:** L — menyentuh join, scoring, event, dan banyak screen.

---

## 4. Practice / Solo Mode

**Tujuan:** Pemain berlatih sendiri tanpa host & tanpa WebSocket. Soal ditampilkan
berurutan, timer per soal, skor lokal, ringkasan di akhir. Tidak memengaruhi statistik
multiplayer.

### Data Model
- **Migration** `create_practice_sessions_table` (opsional, untuk menyimpan riwayat):
  `id`, `quiz_id` (FK cascade), `user_id` (FK nullable), `score`, `correct_count`,
  `total_questions`, `completed_at`, timestamps. Bila tidak butuh riwayat, mode bisa
  full client-side dan tabel ini dilewati.
- Reuse `ScoringService` untuk konsistensi perhitungan poin.

### Backend
- **Controller baru** `app/Http/Controllers/PracticeController.php`:
  - `start(Quiz $quiz)` — render `Pages/Practice/Play.vue` dengan seluruh soal+jawaban
    (acak urutan). Hanya untuk quiz `is_published` & `visibility=public` atau milik user.
  - `submit(Quiz $quiz)` (opsional) — terima hasil akhir, simpan `PracticeSession`.
- **Route** `GET /quizzes/{quiz}/practice` (name `quizzes.practice`) dan
  `POST /quizzes/{quiz}/practice` — di grup auth atau publik sesuai kebijakan.
- **Policy** — tambah `practice` ability di `QuizPolicy` (publik boleh, privat hanya
  owner).

### Frontend
- **Page baru** `Pages/Practice/Play.vue` — reuse `TimerBar`, `CountdownOverlay`,
  answer shapes, `ConfettiEffect`. State machine lokal (tanpa Echo): question → result →
  next → summary. Hitung skor pakai rumus yang sama (port kecil dari ScoringService ke
  util JS, atau panggil endpoint submit per soal — rekomendasi: hitung lokal untuk
  latensi nol).
- **`Pages/Dashboard.vue`** & **`Pages/Landing.vue`** — tombol "Latihan / Practice".
- **Komponen** `Components/Game/PracticeSummary.vue` — ringkasan akhir (skor, akurasi,
  rata-rata waktu, tombol ulang).

### i18n
Section `practice`: `practice_mode`, `start_practice`, `summary`, `accuracy`,
`try_again`, `your_score`.

### Testing
- `tests/Feature/PracticeTest.php`: hanya quiz published/owned bisa di-practice; data
  soal dikirim tanpa membocorkan `is_correct` lebih dari yang perlu (kirim is_correct
  karena dievaluasi client — atau evaluasi via endpoint untuk anti-cheat). Catat
  keputusan ini di PRD.

> **Catatan keamanan:** karena solo mode mengevaluasi di client, `is_correct` ikut
> terkirim. Ini dapat diterima untuk practice (bukan kompetitif). Jika ingin anti-cheat,
> sediakan endpoint `submit` per-soal yang mengevaluasi di server.

**Effort:** M

---

# GROUP B — Player Identity & Progression

## 5. Nickname Generator

**Tujuan:** Tombol "acak" di layar join untuk menghasilkan nama lucu
(mis. "PandaCepat42"), gaya Kahoot.

### Data Model
Tidak ada perubahan DB. Daftar kata disimpan sebagai aset frontend (atau config).

### Backend (opsional)
- Endpoint ringan `GET /api/nickname` yang mengembalikan 1 nama acak (berguna agar
  daftar kata konsisten lintas platform/mobile). Implementasi via array di
  `config/nicknames.php` (adjective + noun + angka). Tanpa state.

### Frontend
- **Util baru** `resources/js/utils/nicknameGenerator.js` — array `adjectives` &
  `nouns` (versi ID & EN, pilih sesuai locale), fungsi `randomNickname()`.
- **`Pages/Player/Join.vue`** — ikon dadu 🎲 di sebelah input nickname; klik → isi
  field. Pre-fill otomatis saat halaman load (boleh diubah).

### i18n
Daftar kata disediakan per-locale di util; tambah label `game.randomize_nickname`.

### Testing
- Test komponen ringan (Vue) atau cukup unit test util: `randomNickname()` selalu
  menghasilkan string non-kosong & dalam batas panjang nickname (validasi
  `JoinGameRequest`).

**Effort:** S

---

## 6. Badge & Achievement System

**Tujuan:** Lencana untuk pencapaian (mis. "First Win", "Streak Master 10x",
"Speed Demon", "Played 50 Games"). Hanya untuk user terdaftar (guest tidak punya
profil persisten).

### Data Model
- **Enum/Config** `app/Enums/BadgeType.php` atau `config/badges.php` mendefinisikan
  daftar badge: `key`, `name`, `description`, `icon`, dan `criteria` (deklaratif).
- **Migration** `create_user_badges_table`: `id`, `user_id` (FK cascade), `badge_key`
  (string), `earned_at`, unique(`user_id`,`badge_key`), timestamps.
- **Model** `app/Models/UserBadge.php` + relasi `User::badges()`.

### Backend
- **Service baru** `app/Services/AchievementService.php`:
  - `evaluate(User $user): array` — periksa kriteria semua badge terhadap statistik
    user (jumlah game, total menang, best streak, jumlah jawaban benar, dst.) dan
    berikan badge yang belum dimiliki. Return badge yang baru didapat.
  - Statistik dihitung via query agregat ke `game_players` + `player_answers`
    (eager/aggregate, hindari N+1).
- **Hook poin masuk:** panggil `AchievementService::evaluate()` di
  `GameSessionController::end()` untuk tiap pemain terdaftar (dispatch **queued job**
  `EvaluateAchievements` karena melibatkan banyak pemain — sesuai konvensi "heavy ops =
  queued").
- **Event/Notifikasi** opsional `BadgeEarned` (broadcast ke
  `App.Models.User.{id}`) agar muncul toast realtime.
- **Controller** `ProfileBadgeController` atau tambahan di profil — endpoint untuk
  menampilkan badge user (dipakai di halaman profil).

### Frontend
- **Komponen** `Components/Badges/BadgeCard.vue` & `BadgeGrid.vue` (earned vs locked).
- **Halaman profil** — section "Achievements" menampilkan grid badge.
- **Toast** saat `BadgeEarned` (pakai `useSwal`).
- **`Pages/Host/Results.vue` / `Player/Game.vue` finished** — tampilkan badge baru yang
  didapat di sesi itu.

### i18n
Section `badges`: nama & deskripsi tiap badge (ID/EN).

### Testing
- `tests/Feature/AchievementTest.php`: tiap badge diberikan tepat saat kriteria
  terpenuhi & tidak dobel (unique constraint); guest tidak menerima badge; job
  ter-dispatch saat game end.

**Effort:** M (tergantung jumlah badge; mulai dengan 5–6 badge).

---

## 7. Global Leaderboard / User Ranking

**Tujuan:** Peringkat user lintas sesi (mis. total skor sepanjang masa, total
kemenangan, atau XP). Halaman leaderboard global + posisi user sendiri.

### Data Model
Dua opsi:
- **Opsi A (dihitung saat-runtime):** agregasi langsung dari `game_players`
  (`SUM(score)`, `COUNT` menang). Sederhana, tapi berat bila data besar.
- **Opsi B (denormalisasi — direkomendasikan):** kolom agregat di `users`:
  `total_xp` (unsignedBigInteger default 0), `games_played`, `games_won`. Di-update
  saat game end (transaksi). Leaderboard = `ORDER BY total_xp DESC`.
  - **Migration** `add_ranking_stats_to_users_table`.

### Backend
- **Service** `RankingService`:
  - `recordGameResult(GameSession $session)` — dipanggil di `end()`; untuk tiap pemain
    terdaftar, increment `total_xp` (mis. = skor akhir), `games_played`, dan
    `games_won` untuk juara. Gunakan queued job bersama achievement.
  - `topPlayers(int $limit, string $period = 'all')` — leaderboard global; dukung
    filter periode (`all|month|week`) bila pakai Opsi A, atau tabel snapshot bila
    Opsi B + periode.
  - `rankFor(User $user)` — posisi user (window function / count rank).
- **Controller** `LeaderboardController@index` → `Pages/Leaderboard.vue`.
- **Route** publik `GET /leaderboard` (name `leaderboard`).

### Frontend
- **Page baru** `Pages/Leaderboard.vue` — tabel top N + kartu "posisimu". Reuse
  avatar display.
- **Nav** — link Leaderboard di navigasi utama & landing.

### i18n
Section `leaderboard`: `global_leaderboard`, `your_rank`, `total_xp`, `games_won`,
`this_week`, `this_month`, `all_time`.

### Testing
- `tests/Feature/LeaderboardTest.php`: XP terakumulasi benar saat game end; juara
  dapat +1 `games_won`; ranking terurut; guest tidak muncul.

**Effort:** M

---

# GROUP C — Quiz Management

## 8. Kategori & Tag Kuis

**Tujuan:** Mengelompokkan kuis (kategori tunggal mis. "Matematika") dan label bebas
(tag, banyak). Untuk discovery, filter dashboard, dan marketplace.

### Data Model
- **Migration** `create_categories_table`: `id`, `name`, `slug` (unique), `icon`
  (nullable), timestamps. Seed kategori awal lewat seeder.
- **Migration** `add_category_id_to_quizzes_table`: `category_id` (FK nullable).
- **Tag (many-to-many):** `create_tags_table` (`id`, `name`, `slug` unique) +
  `create_quiz_tag_table` pivot (`quiz_id`, `tag_id`).
- **Models** `Category`, `Tag`; relasi `Quiz::category()`, `Quiz::tags()`
  (belongsToMany).

### Backend
- **`QuizController`** store/update — terima `category_id` & `tags[]`; sync tags
  (`syncWithoutDetaching` / `sync`). Tags baru dibuat on-the-fly (firstOrCreate).
- **`QuizController@index`** (dashboard) — tambah filter `category` & `tag`.
- **Admin** — CRUD kategori di `Admin\` (controller `CategoryController`) + halaman
  `Admin/Categories`.
- **Form Request** — validasi `category_id` exists, `tags` array of string.

### Frontend
- **`Pages/Quiz/Editor.vue`** — dropdown kategori + input tag (chip/multiselect).
- **`Pages/Dashboard.vue`** — filter berdasarkan kategori/tag.
- **`Pages/Admin/Categories/Index.vue`** — kelola kategori.
- **Komponen** `Components/Quiz/TagInput.vue`, `CategorySelect.vue`.

### i18n
Section `category` & `tag`: label, placeholder, nama kategori default.

### Testing
- `tests/Feature/CategoryTagTest.php`: assign kategori & tag; filter dashboard; tag
  firstOrCreate tidak duplikat; admin CRUD kategori.

**Effort:** S–M

---

## 9. Import / Export Kuis

**Tujuan:** Bulk membuat soal dari file (CSV/JSON/Excel) dan mengekspor kuis untuk
backup/berbagi.

### Data Model
Tidak ada perubahan skema. Pakai format pertukaran terdefinisi (JSON sebagai sumber
kebenaran; CSV untuk soal sederhana).

**Format JSON (export):**
```json
{
  "title": "...", "description": "...", "theme": "standard",
  "questions": [
    {
      "type": "multiple_choice", "question_text": "...", "time_limit": 20,
      "points": "standard",
      "answers": [
        {"answer_text": "...", "is_correct": true, "color": "red"}
      ]
    }
  ]
}
```

### Backend
- **Service** `QuizImportService` & `QuizExportService`:
  - Import: validasi struktur (schema), buat Quiz + Questions + Answers dalam
    transaksi; map warna/shape default bila kosong; lewati/limit jumlah soal.
  - Export: serialisasi quiz → JSON (download) dan CSV (flatten 1 baris per soal).
- **Controller** `QuizImportExportController`:
  - `POST /quizzes/import` (upload file) → buat quiz draft, redirect ke editor.
  - `GET /quizzes/{quiz}/export?format=json|csv` → `StreamedResponse`
    (pola sama dengan `GameSessionController::export()`).
- **Form Request** `ImportQuizRequest`: mimes (csv,txt,json,xlsx), max size.
- **CSV/Excel:** gunakan paket `maatwebsite/excel` (jika boleh tambah dependency) atau
  parser CSV native Laravel untuk format sederhana. Rekomendasi: mulai JSON + CSV
  native, tambah xlsx belakangan.

### Frontend
- **`Pages/Dashboard.vue`** — tombol "Import" (modal upload + contoh template
  downloadable).
- **`Pages/Quiz/Editor.vue`** — tombol "Export" (JSON/CSV).
- **Komponen** `Components/Quiz/ImportModal.vue`.

### i18n
Section `import_export`: label tombol, instruksi format, pesan sukses/gagal,
contoh template.

### Testing
- `tests/Feature/QuizImportExportTest.php`: import JSON valid membuat quiz lengkap;
  import invalid ditolak dengan pesan jelas; export JSON round-trips
  (import(export(quiz)) ≈ quiz); CSV ter-generate benar; hanya owner bisa export.

**Effort:** M

---

## 10. Question Bank

**Tujuan:** Bank soal reusable lintas kuis. User menyimpan soal ke bank, lalu menarik
ke kuis mana pun. Mempercepat pembuatan kuis.

### Data Model
- **Migration** `create_question_bank_items_table`: `id`, `user_id` (FK cascade),
  `type`, `question_text`, `image`, `time_limit`, `points`, `category_id` (nullable),
  `answers` (JSON — opsi tersimpan inline), timestamps. (Menyimpan answers sebagai JSON
  menghindari tabel terpisah; saat ditarik ke quiz, di-expand jadi Answer rows.)
- **Model** `BankQuestion` + relasi `User::bankQuestions()`.
- Reuse `category_id` dari fitur #8 untuk pengorganisasian.

### Backend
- **Controller** `QuestionBankController`: `index` (list + search + filter kategori/
  tipe), `store` (simpan soal ke bank — bisa dari editor atau form), `destroy`,
  `importToQuiz(Quiz $quiz)` (salin item terpilih → Question+Answer di quiz, transaksi).
- **Tambahan di QuestionEditor flow:** tombol "Save to Bank" pada soal existing.
- **Routes** `/question-bank` (index), `POST /question-bank`,
  `DELETE /question-bank/{item}`, `POST /quizzes/{quiz}/from-bank`.
- **Policy** — user hanya akses bank miliknya.

### Frontend
- **Page baru** `Pages/QuestionBank/Index.vue` — grid/list soal bank, search, filter,
  preview, delete.
- **`Pages/Quiz/Editor.vue`** — tombol "Add from Bank" → modal pilih soal
  (`Components/Quiz/BankPickerModal.vue`); "Save to Bank" pada tiap soal.

### i18n
Section `question_bank`.

### Testing
- `tests/Feature/QuestionBankTest.php`: simpan & tarik soal; tarik menyalin jawaban
  benar; isolasi antar user; filter kategori/tipe.

**Effort:** M

---

## 11. Quiz Template / Marketplace Publik

**Tujuan:** Discovery kuis publik. User menelusuri & memainkan/menduplikasi kuis publik
buatan orang lain. Memanfaatkan `visibility=public` & `is_published` yang sudah ada.

### Data Model
- Reuse `quizzes.visibility` (`public`), `is_published`, `category_id`, `tags`.
- **Migration** `add_marketplace_stats_to_quizzes_table` (opsional):
  `plays_count`, `duplicates_count`, `featured` (bool) untuk sorting & kurasi.
- **Migration** `create_quiz_favorites_table` (opsional): user mem-bookmark kuis.

### Backend
- **Controller** `ExploreController@index` → `Pages/Explore.vue`: list kuis
  `public + published`, dengan search, filter kategori/tag, sort
  (popular/newest/featured), pagination. Eager-load user (author), questions count.
- **Reuse** `QuizController@duplicate` (sudah ada) untuk "gunakan template" — duplikasi
  ke akun user sebagai draft. Increment `duplicates_count`.
- **Increment `plays_count`** di `GameSessionController::store()` saat host memulai game
  dari kuis publik milik orang lain (opsional).
- **Admin** — toggle `featured` di `Admin\QuizController`.
- **Routes** `GET /explore` (publik), `POST /quizzes/{quiz}/favorite` (auth).

### Frontend
- **Page baru** `Pages/Explore.vue` — kartu kuis (cover, judul, author, kategori, jumlah
  soal, plays), filter sidebar, tombol "Mainkan/Practice" & "Gunakan Template".
- **Nav/Landing** — link Explore.
- **Komponen** `Components/Quiz/QuizCard.vue` (reusable).

### i18n
Section `explore`: `explore_quizzes`, `popular`, `newest`, `featured`, `use_template`,
`by_author`, `plays`.

### Testing
- `tests/Feature/ExploreTest.php`: hanya kuis public+published tampil; privat
  tersembunyi; filter & sort; duplicate membuat draft milik user & menaikkan counter;
  favorite toggle.

**Effort:** M

---

## 12. AI Question Generator

**Tujuan:** Generate soal otomatis dari topik/teks menggunakan Claude API. Fitur
pembeda utama: host mengetik topik ("Sistem Tata Surya", "10 soal, sedang"), AI
menghasilkan soal MC + jawaban yang langsung bisa di-edit.

> **Stack note:** integrasi memakai **Claude API** (Anthropic). Default ke model
> Claude terbaru. Lihat `docs/superpowers` / skill `claude-api` untuk referensi
> model id & SDK saat implementasi. Karena pemanggilan API eksternal + bisa lambat,
> WAJIB lewat **queued job** dan butuh network egress (cek network policy environment).

### Data Model
- Tidak ada perubahan skema inti. Opsional **migration**
  `create_ai_generation_logs_table` untuk audit/rate-limit: `user_id`, `prompt`,
  `tokens_used`, `status`, timestamps.
- Konfigurasi: `config/services.php` → `anthropic` (api key dari env `ANTHROPIC_API_KEY`),
  diakses via `config()` bukan `env()`.

### Backend
- **Service** `AiQuestionService`:
  - `generate(string $topic, int $count, string $difficulty, string $type, string $locale): array`
    — bangun prompt terstruktur yang meminta JSON sesuai skema Question/Answer
    (gunakan tool-use / structured output agar parsing andal), panggil Claude API
    (HTTP client Laravel), validasi & sanitasi hasil, kembalikan array soal.
  - Tegakkan batas: max soal per request, panjang topik, jenis (MC/TF/poll).
- **Job** `GenerateQuestionsJob` (`ShouldQueue`) — jalankan service, broadcast hasil ke
  `App.Models.User.{id}` via event `QuestionsGenerated`, atau simpan draft ke quiz.
- **Controller** `AiQuestionController@generate`
  (`POST /quizzes/{quiz}/ai-generate`): validasi input, dispatch job, kembalikan status.
- **Rate limiting** — `RateLimiter` per user (mis. N generasi/hari) + cek admin setting
  `ai_generation_enabled` (AppSetting) agar bisa dimatikan global.
- **Form Request** `AiGenerateRequest`: `topic` (required, max), `count` (1–20),
  `difficulty` (easy|medium|hard), `type`.

### Frontend
- **`Pages/Quiz/Editor.vue`** — tombol "✨ Generate with AI" → modal
  (`Components/Quiz/AiGenerateModal.vue`): input topik, jumlah, kesulitan, tipe.
- Loading state (Inertia deferred / WebSocket): tampilkan skeleton; saat
  `QuestionsGenerated` masuk, tambahkan soal ke editor sebagai draft yang bisa diedit
  sebelum disimpan.
- Tampilkan disclaimer "Tinjau soal hasil AI sebelum dipublikasikan".

### i18n
Section `ai`: label tombol, field modal, status (generating/done/error), disclaimer.

### Testing
- `tests/Feature/AiQuestionTest.php`: **mock** Claude API (jangan panggil API nyata di
  test). Verifikasi: prompt terbentuk benar; respons valid → soal tersimpan/draft;
  respons invalid ditangani; rate limit menolak setelah batas; fitur off via setting
  → 403. Gunakan `Http::fake()`.

**Effort:** M (kompleksitas utama: prompt design, parsing andal, error handling).

---

# GROUP D — Analytics

## 13. Advanced Analytics

Tiga sub-fitur di atas data game yang sudah ada (`player_answers`, `game_players`).

### 13a. PDF Report

**Tujuan:** Ekspor hasil game ke PDF (saat ini hanya CSV) — leaderboard + analisis
per-soal, siap cetak untuk guru.

**Backend**
- Tambah paket PDF (`barryvdh/laravel-dompdf` direkomendasikan) — perlu izin tambah
  dependency.
- **`GameSessionController::exportPdf(GameSession $gameSession)`**
  (`GET /game-sessions/{gameSession}/export-pdf`): render Blade
  `resources/views/reports/game.blade.php` (satu-satunya pengecualian Blade selain
  `app.blade.php`) → PDF stream. Data: metadata game, leaderboard
  (`ScoringService::getLeaderboard`), per-question stats (reuse logika `stats()`).

**Frontend**
- **`Pages/Host/Results.vue`** & **`Host/Stats.vue`** — tombol "Download PDF" di samping
  "Download CSV".

### 13b. Question Difficulty Insights

**Tujuan:** Tandai soal yang paling sering dijawab salah, lintas semua sesi sebuah
kuis — membantu memperbaiki soal.

**Backend**
- **Service** `QuizAnalyticsService::difficulty(Quiz $quiz): Collection` — agregasi
  `player_answers` per `question_id` di semua game session kuis itu: hitung
  `correct_rate`, `avg_time`, `total_attempts`. Klasifikasi `easy/medium/hard` dari
  correct_rate (mis. >0.8 easy, 0.4–0.8 medium, <0.4 hard). Eager/aggregate query,
  hindari N+1.
- **Controller** `QuizAnalyticsController@index` (`GET /quizzes/{quiz}/analytics`) →
  `Pages/Quiz/Analytics.vue`.

**Frontend**
- **Page baru** `Pages/Quiz/Analytics.vue` — tabel soal dengan badge difficulty, bar
  correct-rate, avg time; sorting. Reuse pola chart yang ada di Admin Dashboard /
  Host Stats.

### 13c. Player Progress Tracking

**Tujuan:** Untuk user terdaftar (kelas/edukasi), lacak perkembangan lintas sesi:
tren skor, akurasi, jumlah game dimainkan.

**Backend**
- **Service** `PlayerProgressService::forUser(User $user): array` — agregasi
  `game_players` (where `user_id`) di banyak sesi: time-series skor & akurasi,
  totalnya. Bila fitur ranking (#7) memakai kolom denormalisasi, reuse untuk angka
  ringkas.
- **Controller** — section di profil atau `GET /me/progress` →
  `Pages/Progress.vue`.

**Frontend**
- **Page/section** `Pages/Progress.vue` — line chart skor per game, akurasi, statistik
  ringkas, daftar game terakhir.

### i18n
Section `analytics`: `difficulty`, `easy/medium/hard`, `correct_rate`, `avg_time`,
`download_pdf`, `progress`, `accuracy_trend`, `score_trend`.

### Testing
- `tests/Feature/Analytics/DifficultyTest.php`: klasifikasi sesuai correct_rate;
  agregasi lintas sesi benar.
- `tests/Feature/Analytics/ProgressTest.php`: time-series user benar; isolasi antar
  user; guest dikecualikan.
- `tests/Feature/Analytics/PdfExportTest.php`: response PDF (content-type) ter-generate;
  hanya host/owner boleh.

**Effort:** M (gabungan tiga sub-fitur).

---

# GROUP E — Real-time Enhancements

## 14a. Live Chat / Reactions / Emoji

**Tujuan:** Interaksi realtime di lobby & antar-soal — emoji reactions melayang dan/atau
chat ringan. Default: **emoji reactions** (lebih aman dari moderasi daripada free chat).

### Data Model
- **Reactions:** tanpa persistensi (ephemeral) — cukup broadcast event.
- **Chat (opsional, jika diinginkan):** `create_game_messages_table`
  (`game_session_id`, `game_player_id`, `body`, `created_at`) bila ingin riwayat;
  default ephemeral tanpa tabel.
- **`GameSession.settings`** — flag `reactions_enabled`, `chat_enabled` (host toggle).

### Backend
- **Event** `ReactionSent` (broadcast `game.{id}`): `playerId`, `nickname`, `emoji`.
- **Endpoint** `POST /api/games/{gameSession}/react` (`PlayerController::apiReact`):
  validasi `player_id`, `player_token`, `emoji` (whitelist set emoji), broadcast.
  Rate-limit per player (mis. 1/detik) via `RateLimiter` untuk cegah spam.
- **Chat (opsional)** `POST /api/games/{gameSession}/message` dengan filter
  kata kotor + rate limit + panjang maksimal. Hanya aktif bila `chat_enabled`.

### Frontend
- **`Composables/useGame.js`** — listener `ReactionSent` (+ `MessageSent`); state
  `reactions[]` (auto-expire), `messages[]`.
- **Komponen** `Components/Game/ReactionBar.vue` (tombol emoji) &
  `FloatingReactions.vue` (animasi emoji naik & memudar, gaya Instagram Live).
- **`Pages/Player/Game.vue`** & **`Pages/Host/Game.vue`** — tampilkan reaction bar di
  lobby/reveal; overlay floating emoji. Chat panel opsional (collapsible).
- **Host control** — toggle reactions/chat on/off.

### i18n
Section `reactions` / `chat`: label, placeholder, toggle.

### Testing
- `tests/Feature/Game/ReactionTest.php`: emoji di-broadcast; non-whitelist ditolak;
  rate-limit bekerja; reactions off via setting → 403. (Chat: filter & panjang.)

**Effort:** M

---

## 14b. Music / Sound Customization

**Tujuan:** Host memilih tema musik/sound per kuis (atau matikan); pemain bisa mute.
Memperluas `useSound.js` yang sudah ada.

### Data Model
- **`Quiz.settings`** (JSON sudah ada) — `sound_theme`
  (`classic|chill|energetic|none`) dan `music_enabled`.
- Tidak perlu migration (gunakan settings JSON). Bila ingin upload musik custom: butuh
  storage (S3 sudah ada) + kolom — di luar scope awal; mulai dari preset.

### Backend
- **`QuizController` store/update** — terima & validasi `sound_theme`, `music_enabled`
  di settings.
- Kirim `sound_theme` ke client lewat props game host/player (sudah ada jalur theme).

### Frontend
- **`Composables/useSound.js`** — refactor agar menerima `theme` dan memetakan
  ke set frekuensi/track berbeda (lobby vs question vs tick vs correct/wrong). Tambah
  preset chill/energetic. Hormati preferensi `music_enabled` & mute lokal pemain
  (localStorage, mirip `useTheme`).
- **`Pages/Quiz/Editor.vue`** — kontrol pemilihan sound theme (mirip `ThemeSelector`):
  `Components/Quiz/SoundThemeSelector.vue` dengan preview.
- **`Pages/Player/Game.vue` & `Host/Game.vue`** — tombol mute/unmute global.

### i18n
Section `sound`: `sound_theme`, nama preset, `mute`, `unmute`, `music`.

### Testing
- Sebagian besar UI/audio (sulit di-unit-test). Test backend:
  `tests/Feature/QuizSoundSettingTest.php` — settings tersimpan & tervalidasi.
  Test frontend ringan untuk pemetaan preset (opsional).

**Effort:** S–M

---

# Pertimbangan Lintas-Fitur

- **Konvensi wajib (lihat `CLAUDE.md`/`AGENTS.md`):** semua validasi via Form Request;
  Eloquent saja (tanpa `DB::` raw); named routes + `route()`; `config()` bukan `env()`
  di luar file config; operasi berat → queued job; PHP pakai constructor property
  promotion, return type eksplisit, PHPDoc untuk array; jalankan
  `vendor/bin/pint --dirty` setelah perubahan PHP.
- **Testing:** Pest 4, feature test sebagai default, gunakan factory + state; aktifkan
  skill `pest-testing`; **setiap perubahan butuh test**.
- **i18n:** ID default, EN fallback. Tambahkan key di `id.json` **dan** `en.json` untuk
  setiap fitur — keduanya 337 baris saat ini, jaga sinkron.
- **Broadcasting:** event baru implements `ShouldBroadcastNow`, channel `game.{id}`,
  payload via `broadcastWith()`. Tambah field opsional agar backward compatible.
- **Deferred Inertia props** (AI generate, analytics berat) → sediakan skeleton/pulse
  loading.
- **Network policy:** AI Question Generator butuh egress ke Anthropic API — verifikasi
  kebijakan jaringan environment sebelum implementasi.
- **Dependency baru** yang mungkin diperlukan (konfirmasi dulu):
  `maatwebsite/excel` (import/export xlsx), `barryvdh/laravel-dompdf` (PDF),
  Anthropic SDK/HTTP (AI). Mulai dari yang native bila memungkinkan.

# Roadmap Bertahap (saran)

1. **Quick wins (1 sprint):** Poll (1), Nickname Generator (5), Kategori & Tag (8),
   Sound customization (14b).
2. **Fondasi data:** Analytics tracking + difficulty (13b), denormalisasi ranking (7),
   Achievement (6) — saling menguatkan.
3. **Engagement berat:** Power-ups (2), Team Mode (3), Reactions/Chat (14a).
4. **Konten & discovery:** Import/Export (9), Question Bank (10), Marketplace (11),
   Practice Mode (4).
5. **Pembeda:** AI Question Generator (12), PDF report (13a), Progress tracking (13c).
