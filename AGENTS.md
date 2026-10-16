# Aturan Utama AI — Proyek Laravel

File ini adalah sumber aturan utama. Sebelum mengubah kode, baca file ini dan `README-AI-RULES.md`.

## 1. Identitas dan pencatatan (OPSIONAL)
- Pengguna boleh langsung memakai AI tanpa menjalankan script pencatatan.
- Jika laporan AI diperlukan, pengguna dapat menjalankan `php scripts/ai-task.php start`; script mengambil Nama Lengkap dari `git config user.name` dan mencoba mendeteksi provider secara otomatis.
- Jangan meminta NIM, email, nomor telepon, username, atau informasi pribadi lain untuk laporan AI. Email Git hanya dikelola pengguna dalam konfigurasi Git.
- Provider tidak selalu dapat dideteksi dari aplikasi desktop/web. Jika tidak terdeteksi, script akan menawarkan input provider secara opsional.
- Jangan mengklaim prompt atau respons AI terekam otomatis. Rekam hanya informasi yang benar-benar tersedia; jangan mengarang prompt, output, waktu, hasil tes, atau fakta perubahan.
- Jangan mencatat kredensial, token, password, API key, data pribadi, atau isi `.env`.

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
- Pencatatan sesi dan penggunaan `php scripts/ai-task.php finish` bersifat opsional.
- Commit hanya setelah meninjau `git status` dan `git diff`, memastikan perubahan yang akan di-commit memang terkait tugas, dan memastikan tidak ada rahasia.
- Jangan push otomatis.
- Jangan menggunakan `--no-verify`, mengubah riwayat Git, atau menghapus perubahan pengguna.
- Jangan memasukkan `.env`, kredensial, token, atau file rahasia.
- Jika ada konflik, tes gagal, file rahasia, atau perubahan yang tidak terkait, hentikan proses commit dan laporkan masalahnya.

## 6. Laporan akhir
Sampaikan ringkasan, file dan bagian yang berubah, before–after, serta tes yang benar-benar dijalankan. Sebutkan lokasi laporan sesi hanya jika pencatatan opsional digunakan, dan hash commit jika tersedia.
