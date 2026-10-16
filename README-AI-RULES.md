# Laravel AI Project Rules — Panduan Final

Paket ini menyediakan instruksi lintas-provider, pencatatan riwayat AI otomatis, rekap laporan terpusat, dan standar Git untuk proyek Laravel. Anggota dapat langsung memakai AI setelah melakukan `git clone` dan konfigurasi identitas Git; **pengguna tidak perlu menjalankan perintah `start` atau `finish` secara manual** karena AI yang akan mencatat log sesi secara otomatis di belakang layar.

## Hal yang perlu dipahami
1. Pastikan aplikasi/agent AI yang digunakan (Cursor, Claude Code, Gemini CLI, GitHub Copilot, Windsurf, dll.) memiliki akses ke terminal lokal dan membaca file aturan proyek (`AGENTS.md`, `CLAUDE.md`, `GEMINI.md`, atau `.github/copilot-instructions.md`).
2. Setiap kali anggota memberikan prompt, AI akan otomatis mengeksekusi `php scripts/ai-task.php auto` untuk mencatat identitas pengguna (dari `git config`), nama provider AI, isi prompt, respons AI, serta ringkasan perubahan *before–after*.
3. Seluruh sesi tidak lagi dipecah menjadi banyak file terpisah, melainkan langsung digabungkan (*append*) ke dalam file riwayat masing-masing anggota dan file riwayat gabungan tim.
4. Jangan memasukkan rahasia, token, password, API key, atau isi `.env` ke dalam prompt, log, maupun Git.

## Struktur
- `AGENTS.md`: aturan utama proyek dan instruksi pencatatan otomatis bagi AI.
- `GEMINI.md`: aturan khusus untuk Gemini CLI dan lingkungan yang membacanya.
- `CLAUDE.md`: aturan khusus untuk Claude Code.
- `.github/copilot-instructions.md`: instruksi khusus untuk GitHub Copilot.
- `docs/ai-usage/`:
  - `PROMPT-LOG.md`: tabel indeks seluruh prompt yang tercatat otomatis.
  - `CHANGELOG-AI.md`: tabel indeks riwayat perubahan kode oleh AI.
  - `sessions/riwayat-<nama-anggota>.md`: gabungan riwayat sesi lengkap milik masing-masing anggota.
  - `sessions/RIWAYAT-SEMUA-SESI.md`: gabungan riwayat seluruh sesi AI dari semua anggota tim.
- `scripts/ai-task.php`: skrip CLI yang dieksekusi otomatis oleh AI untuk mencatat log dan memeriksa keamanan Git.
- `.githooks/pre-commit`: pesan informasi; tidak memblokir commit.

## Setup Proyek
Salin file paket ini ke root repository Laravel. Jika file aturan sudah ada, gabungkan isinya; jangan menimpa aturan lama secara membabi buta.

Hook tidak wajib diaktifkan. Jika ingin menjalankan pesan informasi sebelum commit, aktifkan dengan:

```bash
git config core.hooksPath .githooks