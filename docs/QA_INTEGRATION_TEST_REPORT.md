# Laporan QA-03: Instalasi MySQL (XAMPP), Uji CRUD, dan Uji SQL Injection

| Item | Isi |
| --- | --- |
| Penguji (QA) | Aghni Ridfalah, 102012300296 |
| Tanggal uji | 10 November 2026 |
| Issue Linear | PAD-32 [QA-03] |
| Branch kerja | `feature/PAD-32-qa-integration-test` |
| Versi yang diuji | `main` commit `0918d87` (termasuk BE-06 MySQL, PAD-26 validasi, BE-07 keamanan) |
| Lingkungan | Windows, XAMPP 3.3.0 (MariaDB 10.4.32, phpMyAdmin 5.2.1), PHP 8.4.20 (Laravel Herd), Google Chrome |
| Akun uji | `pengelola@campus.test` (Kepala Laboratorium), password `password` |
| Tujuan | Memastikan aplikasi siap demo: bisa dipasang dari nol di MySQL, CRUD berjalan, dan aman dari SQL Injection |

## Ringkasan hasil

| Checklist | Hasil |
| --- | --- |
| 1. Instalasi dari nol di XAMPP (MySQL) | Lulus. Berhasil dengan 2 cara: impor skrip SQL dan `migrate:fresh --seed` |
| 2. Uji CRUD Room dan Booking | Lulus. Create, Read, Update, Delete berjalan untuk kedua entitas dan terbukti di MySQL |
| 3. Uji SQL Injection | Lulus. 6 percobaan di pencarian, form, dan login; semua diperlakukan sebagai teks biasa |

Ditemukan 5 catatan perbaikan (bagian 5), tidak ada yang menghalangi demo.

## 1. Instalasi dari nol di MySQL (XAMPP)

| No | Langkah | Hasil | Bukti |
| --- | --- | --- | --- |
| 1.1 | Menyalakan Apache dan MySQL di XAMPP Control Panel | Running, MySQL port 3306. PHP aplikasi tetap 8.4.20 dari Herd (tidak bentrok dengan PHP XAMPP) | `01` |
| 1.2 | Impor `database/campus_space_xampp.sql` lewat phpMyAdmin ke server tanpa database aplikasi | "Impor telah selesai, 139 kueri dilakukan", database `campus_space_xampp` terbentuk | `02` |
| 1.3 | Memeriksa isi database hasil impor | 14 tabel InnoDB `utf8mb4_unicode_ci`: 3 unit, 19 ruang, 44 fasilitas, 20 jadwal, 21 peminjaman, 5 akun, 15 migration | `03` |
| 1.4 | Mengubah `.env` ke MySQL, `php artisan config:clear`, `php artisan migrate:status` | 15 migration berstatus Ran; Laravel tersambung ke MySQL | `04` |
| 1.5 | `php artisan migrate:fresh --seed` di MySQL | 15 migration DONE, DemoAccountSeeder dan DemoCampusSeeder DONE | `05` |
| 1.6 | Menjalankan aplikasi dan login | Dashboard tampil dengan data dari MySQL (17 ruang aktif, 2 pengajuan menunggu) | `06` |

Konfigurasi `.env` yang dipakai:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=campus_space_xampp
DB_USERNAME=root
DB_PASSWORD=
```

Rekomendasi untuk demo: pakai `php artisan migrate:fresh --seed`, karena tanggal data demo menyesuaikan hari demo. Data di skrip SQL memakai tanggal tetap (8-10 November 2026), sehingga booking "akan datang" sudah lewat saat demo (Temuan 3).

## 2. Uji CRUD Room dan Booking

Setiap perubahan dicek di aplikasi, lalu dicek langsung di tabel MySQL lewat phpMyAdmin.

| No | Entitas | Operasi | Data uji | Hasil di aplikasi | Hasil di MySQL | Status | Bukti |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 2.1 | Room | Create | `QA-ROOM-01`, "Ruang Uji QA-03", Class Lab, 30 orang | Muncul di daftar Ruangan & Foto | - | Lulus | `07` |
| 2.2 | Room | Read | Halaman detail ruang | Deskripsi, kapasitas 30, Gedung FRI Lantai 3, Tersedia | - | Lulus | `08` |
| 2.3 | Room | Update | Nama jadi "Ruang Uji QA-03 Revisi", kapasitas 35 | Perubahan tampil di detail | id 20, nama dan kapasitas sesuai | Lulus | `09`, `10` |
| 2.4 | Room | Delete | Hapus ruang uji | Konfirmasi muncul, lalu "berhasil dihapus" | 0 baris | Lulus | `11`, `12`, `13` |
| 2.5 | Booking | Create + Read | R12, 12 Nov 2026 10.00-12.00, 20 orang | Status "Menunggu persetujuan", rincian sesuai | - | Lulus | `14` |
| 2.6 | Booking | Update | 25 orang, 13.00-15.00 | Perubahan tampil di detail | id 22, room_id 16, 25 orang, 13:00-15:00, pending | Lulus | `15`, `16` |
| 2.7 | Booking | Delete | Hapus peminjaman uji | Konfirmasi muncul, lalu "Peminjaman dihapus.", kembali 21 pengajuan | 0 baris | Lulus | `17`, `18`, `19` |

Pengamatan pendukung:

- Relasi Room-Booking terjaga: ruang yang memiliki riwayat peminjaman menampilkan "Tidak dapat dihapus", sedangkan ruang tanpa peminjaman dapat dihapus.
- Semua aksi hapus memakai dialog konfirmasi yang menyebut data yang akan dihapus beserta dampaknya.

## 3. Uji SQL Injection

| No | Lokasi | Input | Tujuan serangan | Hasil | Status | Bukti |
| --- | --- | --- | --- | --- | --- | --- |
| 3.1 | Pencarian ruang | `' OR '1'='1` | Menampilkan semua data | "0 ruangan ditemukan" | Aman | `20` |
| 3.2 | Pencarian ruang | `'; DROP TABLE rooms; --` | Menghapus tabel ruang | "0 ruangan ditemukan"; setelah reset tetap 19 ruang | Aman | `21`, `22` |
| 3.3 | Form Tambah Ruang (Nama) | `Uji'); DELETE FROM rooms; --` | Menghapus semua ruang lewat data yang disimpan | Tersimpan sebagai teks; ruang lain utuh (kembali 19 setelah data uji dihapus) | Aman | `23`, `24`, `25` |
| 3.4 | Login (Kata Sandi) | email benar + `' OR '1'='1` | Masuk tanpa tahu kata sandi | Ditolak: "Email atau kata sandi salah." | Aman | `26` |
| 3.5 | Login (Email) | `'OR'1'='1@campus.test` | Masuk sebagai pengguna mana pun | Ditolak: "Email atau kata sandi salah." | Aman | `27` |
| 3.6 | Login (pembanding) | email dan kata sandi benar | Memastikan login tidak rusak | Berhasil masuk ke Dashboard | Normal | `28` |

Kenapa aman: aplikasi memakai Eloquent/Query Builder Laravel yang menjalankan **prepared statement**. Perintah SQL dan isian pengguna dikirim terpisah ke database, sehingga isian tidak pernah dijalankan sebagai perintah. Kata sandi dicocokkan dengan hash bcrypt, bukan dimasukkan ke query. Pesan galat login bersifat umum sehingga tidak membocorkan email mana yang terdaftar.

## 4. Kesimpulan

Aplikasi Campus Space siap didemokan di XAMPP (MySQL): database bisa dipasang dari nol dengan skrip SQL maupun migration dan seeder, CRUD Room dan Booking berjalan dan tersimpan dengan benar di MySQL, dan seluruh percobaan SQL Injection gagal.

## 5. Temuan

| No | Temuan | Dampak | Prioritas | Saran | Untuk |
| --- | --- | --- | --- | --- | --- |
| 1 | File database SQLite `PAD_KELOMPOK_1` (155 KB) ikut ter-commit di folder utama repo, berisi data akun termasuk hash kata sandi | Data tidak seharusnya ada di Git; ukuran repo bertambah | Sedang | Hapus dari Git (`git rm --cached PAD_KELOMPOK_1`) dan tambahkan ke `.gitignore` | BE (Nandhira) |
| 2 | Judul halaman detail ruang dan dialog konfirmasi hapus ruang menampilkan `&#039;` untuk tanda kutip (contoh: "Ruangan Uji&#039;); ..."). Penyebab: teks di-escape dua kali, `title="Ruangan {{ $room->name }}"` di `rooms/show.blade.php` lalu `{{ $title }}` di komponen `page-header` | Nama ruang yang mengandung `'` atau `&` tampil berantakan. Bukan celah keamanan | Rendah | Ganti menjadi `:title="'Ruangan ' . $room->name"`; periksa pola serupa di pesan konfirmasi | FE |
| 3 | Data di `database/campus_space_xampp.sql` memakai tanggal tetap (8-10 November 2026) | Saat demo, booking "akan datang" sudah lewat | Rendah | Untuk demo gunakan `php artisan migrate:fresh --seed`; cantumkan di README | BE |
| 4 | Pesan berhasil hapus ruang berbunyi "Ruang Ruang Uji QA-03 Revisi berhasil dihapus." (kata "Ruang" ganda bila nama ruang diawali "Ruang") | Teks kurang rapi | Rendah | Gunakan format "Ruang \"{nama}\" berhasil dihapus." atau tanpa awalan | BE |
| 5 | Beberapa pesan commit BE tidak mengikuti format kelompok (contoh: "feateure_dhira:update commit untuk pull perubahan terbau:") | Riwayat Git sulit dibaca dan dinilai | Rendah | Ikuti `type(scope): deskripsi (by Nama)` sesuai AGENTS.md | PM / BE |

Catatan regresi: temuan QA-02 nomor 2 (pesan validasi server berbahasa Inggris) sudah ditindaklanjuti di PAD-26 (`messages()` Bahasa Indonesia pada Form Request). Perlu uji ulang singkat sebelum demo.

## 6. Pemakaian AI

Langkah pengujian, query pemeriksaan phpMyAdmin, skenario SQL Injection, dan kerangka laporan ini disusun dengan bantuan Claude (Anthropic). Seluruh pengujian dijalankan sendiri oleh QA di laptop dengan XAMPP, dan setiap hasil diambil dari screenshot pengujian nyata (lampiran dokumen laporan Project IV, nomor `01`-`28`).
