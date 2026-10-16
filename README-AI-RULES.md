# Laravel AI Project Rules — Panduan Final

Paket ini menyediakan instruksi lintas-provider, pencatatan AI opsional, template laporan, dan bantuan Git untuk proyek Laravel. Anggota dapat langsung memakai AI setelah clone dan konfigurasi Git; pencatatan AI bukan syarat untuk coding atau commit.

## Batasan yang perlu dipahami
1. File rules tidak otomatis dibaca oleh semua aplikasi AI. Setiap provider harus mendukung atau dikonfigurasi untuk membaca file instruksinya.
2. Paket ini tidak dapat mengambil semua prompt/output dari semua provider secara otomatis. Pencatatan adalah opsional; provider web/desktop sering tidak mengekspos nama aplikasi atau percakapan ke script lokal.
3. Skrip membantu membuat laporan dan commit, tetapi tidak dapat menjamin isi ringkasan before–after sepenuhnya benar tanpa pemeriksaan diff.
4. Jangan memasukkan rahasia, token, password, API key, atau isi `.env` ke log maupun Git.

## Struktur
- `AGENTS.md`: aturan utama proyek.
- `GEMINI.md`: aturan Gemini CLI dan lingkungan yang membacanya.
- `CLAUDE.md`: aturan Claude Code.
- `.github/copilot-instructions.md`: instruksi GitHub Copilot.
- `docs/ai-usage/`: indeks dan laporan sesi.
- `scripts/ai-task.php`: pencatatan sesi dan bantuan commit yang opsional.
- `.githooks/pre-commit`: pesan informasi; tidak memblokir commit karena laporan AI.

## Setup
Salin file paket ini ke root repository Laravel. Jika file aturan sudah ada, gabungkan isinya; jangan menimpa aturan lama secara membabi buta.

Hook tidak wajib diaktifkan. Jika ingin menjalankan pesan informasi sebelum commit, aktifkan dengan:

```bash
git config core.hooksPath .githooks
```

Tanpa langkah ini pun anggota tetap bisa coding, memakai AI, dan melakukan commit seperti biasa. Pastikan PHP dan Git tersedia jika ingin menggunakan script opsional:

```bash
php -v
git --version
git status
```

## Setup anggota (sekali per clone/laptop)
Konfigurasikan identitas Git satu kali:

```bash
git config user.name "Nama Lengkap"
git config user.email "email-github-anggota"
```

Lalu langsung gunakan AI seperti biasa. Pastikan provider membaca file aturan yang sesuai; tidak semua aplikasi AI otomatis membaca semua file instruksi. Pencatatan AI tidak wajib.

## Pencatatan AI opsional
Jika dosen/PM meminta laporan penggunaan AI, jalankan:

```bash
php scripts/ai-task.php start
```

Script mengambil nama dari `git config user.name` dan mencoba mendeteksi provider dari environment aplikasi/CLI. Jika provider tidak terdeteksi, script menawarkan input manual opsional. Setelah coding, tinjau `git status` dan `git diff`, lalu jalankan `php scripts/ai-task.php finish` jika ingin membuat laporan dan menggunakan alur commit dari script.

## Penting tentang commit otomatis
Pencatatan AI tidak mengunci commit. Jika memilih alur `finish`, script akan meminta konfirmasi `COMMIT` dan tidak melakukan push. Periksa `git status` sebelum proses karena script menggunakan `git add -A`, yang dapat men-stage semua perubahan di working tree. Jika ada perubahan anggota lain/tugas lain, pisahkan dahulu.

## Format commit — Conventional Commits
Gunakan tipe standar berbahasa Inggris, deskripsi Bahasa Indonesia, dan nama anggota yang melakukan tugas:

`type(scope-opsional): deskripsi Bahasa Indonesia (by Nama Lengkap)`

Contoh:
- `feat(booking): tambahkan formulir peminjaman laboratorium (by Yardan Zufar Adiwangsa)`
- `fix(auth): perbaiki validasi halaman login (by Yardan Zufar Adiwangsa)`
- `docs(readme): lengkapi panduan instalasi proyek (by Yardan Zufar Adiwangsa)`
- `test(booking): tambahkan pengujian validasi jadwal (by Yardan Zufar Adiwangsa)`

Tipe yang didukung: `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`, `perf`, `build`, `ci`, `revert`. Scope seperti `auth`, `dashboard`, atau `booking` bersifat opsional. Jika menggunakan alur script, nama diambil dari `git config user.name`; Git juga menyimpan author/committer berdasarkan konfigurasi Git lokal. Konfigurasikan nama/email Git dengan benar pada setiap laptop.

## Catatan audit
Git diff adalah bukti perubahan kode. Output AI lengkap hanya tercatat jika tersedia dari platform atau dimasukkan ke laporan. Jangan menyatakan prompt/output terekam otomatis bila mekanisme provider tidak menyediakan datanya.
