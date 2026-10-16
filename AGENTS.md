# Aturan Utama AI — Proyek Laravel

File ini adalah sumber aturan utama. Sebelum mengubah kode, baca file ini dan `README-AI-RULES.md`.

## 1. Identitas dan Pencatatan Otomatis — WAJIB BAGI AI
- **Pengguna TIDAK PERLU menjalankan script `start` atau `finish` secara manual.** Setelah pengguna melakukan `git clone` dan mengatur `git config user.name` serta `git config user.email`, seluruh pencatatan dilakukan otomatis oleh AI.
- Nama Lengkap dan Email diambil otomatis oleh script dari `git config user.name` dan `git config user.email`. Jangan meminta NIM, nama, email, atau data pribadi lain di chat.
- **Setiap kali selesai menjawab prompt atau mengubah kode**, AI **WAJIB** langsung mengeksekusi perintah berikut di terminal tanpa menunggu diminta pengguna:
  ```bash
  php scripts/ai-task.php auto --provider="NAMA_AI" --goal="Tujuan singkat" --prompt="Prompt asli user" --output="Ringkasan jawaban AI" --before="Kondisi awal" --work="Perubahan yang dilakukan" --after="Kondisi akhir" --test="Hasil tes / Tidak dijalankan"

## 2. Aturan perubahan kode — WAJIB
- **Lakukan perubahan sekecil mungkin dan hanya pada baris atau bagian yang berkaitan langsung dengan permintaan pengguna.**
- Jangan mengganti seluruh isi file, seluruh class, seluruh function, seluruh komponen, atau seluruh halaman hanya untuk memperbaiki satu masalah lokal.
- Jangan melakukan refactor, formatting massal, rename, pemindahan file, atau perubahan gaya kode yang tidak diminta.
- Pertahankan kode yang sudah berfungsi, struktur file, penamaan, komentar, dan pola proyek yang ada.
- Sebelum mengedit, baca konteks kode yang relevan dan periksa `git diff` agar perubahan pengguna sebelumnya tidak tertimpa.
- Gunakan patch/diff yang terarah. Jika perubahan yang lebih luas memang diperlukan, jelaskan alasannya dan minta persetujuan terlebih dahulu.
- Jangan menimpa perubahan yang belum di-commit atau pekerjaan anggota lain.
- Setelah mengedit, periksa diff untuk memastikan hanya perubahan yang diperlukan masuk.
- Jika instruksi pengguna tidak jelas atau perbaikan memerlukan perubahan lintas-file yang signifikan, jelaskan lingkupnya dan minta klarifikasi sebelum melakukan perubahan besar.

## 3. Standar Laravel
- Ikuti versi PHP/Laravel, arsitektur, naming, dan pola kode yang sudah digunakan.
- Jangan menambah dependensi atau mengubah skema database tanpa alasan yang jelas.
- Untuk perubahan database, gunakan migration sesuai konvensi proyek; jangan menghapus data.
- Jangan mengklaim tes berhasil jika belum dijalankan. Catat perintah dan hasil tes sebenarnya.

## 4. Before–after dan output
Untuk setiap sesi, catat:
- kondisi sebelum perubahan dan masalah awal;
- ringkasan pekerjaan AI;
- bagian/baris yang diperbaiki dan alasannya;
- kondisi setelah perubahan;
- file yang terdampak;
- tes dan hasil verifikasi;
- keterbatasan atau pekerjaan yang belum selesai.

Gunakan Git diff sebagai bukti perubahan. Hindari menyalin seluruh file ke laporan; cukup jelaskan bagian yang berubah dan kutip potongan kecil bila dibutuhkan.

## 5. Git commit — Conventional Commits
- Gunakan standar **Conventional Commits** dengan tipe berbahasa Inggris dan deskripsi perubahan dalam Bahasa Indonesia.
- Format wajib: `type(scope-opsional): deskripsi Bahasa Indonesia (by Nama Lengkap)`.
- Contoh: `fix(auth): perbaiki validasi halaman login (by Yardan Zufar Adiwangsa)`.
- Tipe yang diperbolehkan:
  - `feat`: menambahkan fitur baru.
  - `fix`: memperbaiki bug atau kesalahan.
  - `docs`: perubahan dokumentasi.
  - `style`: perubahan format/tampilan kode yang tidak mengubah perilaku, misalnya spasi atau CSS; untuk perubahan UI yang terlihat oleh pengguna, utamakan `feat` atau `fix` sesuai konteks.
  - `refactor`: merapikan struktur kode tanpa mengubah perilaku.
  - `test`: menambah atau memperbaiki pengujian.
  - `chore`: pemeliharaan umum/tooling yang tidak masuk tipe lain.
  - `perf`: peningkatan performa.
  - `build`: perubahan sistem build atau dependensi.
  - `ci`: perubahan konfigurasi CI/CD.
  - `revert`: membatalkan commit sebelumnya.
- Scope bersifat opsional, singkat, dan relevan, misalnya `auth`, `dashboard`, `booking`, atau `database`.
- Deskripsi harus spesifik, singkat, berbahasa Indonesia, dan menjelaskan perubahan; jangan gunakan pesan generik seperti `perbaikan` atau `update` saja.
- Nama setelah `(by ...)` sebaiknya sama dengan `git config user.name`. Jangan menebak atau mengambil nama dari username perangkat.
- Git juga menyimpan author/committer sesuai konfigurasi Git lokal. Jika nama di riwayat Git tidak sesuai, minta pemilik perangkat memperbaiki `git config user.name` dan `git config user.email`; jangan mengarang email atau mengubahnya tanpa izin.
- Commit hanya setelah meninjau `git status` dan `git diff`, memastikan perubahan yang akan di-commit memang terkait tugas, dan memastikan tidak ada rahasia.
- Jangan push otomatis.
- Jangan menggunakan `--no-verify`, mengubah riwayat Git, atau menghapus perubahan pengguna.
- Jangan memasukkan `.env`, kredensial, token, atau file rahasia.
- Jika ada konflik, tes gagal, file rahasia, atau perubahan yang tidak terkait, hentikan proses commit dan laporkan masalahnya.

## 6. Laporan akhir
Sampaikan ringkasan, file dan bagian yang berubah, before–after, serta tes yang benar-benar dijalankan. Sebutkan lokasi file riwayat sesi (`docs/ai-usage/sessions/riwayat-<nama>.md` dan `docs/ai-usage/sessions/RIWAYAT-SEMUA-SESI.md`) yang telah diperbarui secara otomatis, serta hash commit jika tersedia.