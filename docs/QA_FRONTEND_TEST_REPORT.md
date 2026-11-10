# Laporan QA-02: Audit HTML5 Semantik, Uji Responsivitas, dan Validasi Form

| Item | Isi |
| --- | --- |
| Penguji (QA) | Aghni Ridfalah, 102012300296 |
| Tanggal uji | 8 November 2026 |
| Issue Linear | PAD-27 [QA-02] |
| Branch kerja | `feature/PAD-27-qa-frontend-test` |
| Versi yang diuji | `main` commit `c3abf59` (UI final Tahap 5 dari FE) |
| Lingkungan | Windows, Google Chrome + DevTools, PHP 8.3 (Laravel Herd), Laravel 13, SQLite, data `php artisan migrate:fresh --seed` |
| Akun uji | `pengelola@campus.test` (Kepala Laboratorium), password `password` |
| Kriteria rubrik | Project III: HTML semantik, kelengkapan >= 4 halaman, konsistensi CSS, tampilan responsif HP dan laptop |

## Ringkasan hasil

| Checklist | Hasil |
| --- | --- |
| 1. Tag HTML5 semantik | Lulus. 7 halaman memakai `header`, `nav`, `main`, `footer`; 1 saran perbaikan kecil |
| 2. Responsif HP (375px) dan laptop (1366px) | Lulus. 7 halaman x 2 ukuran, tidak ada yang terpotong |
| 3. Validasi form | Lulus. 8 skenario ditolak dengan benar; 2 pesan masih berbahasa Inggris |
| Kelengkapan halaman | Lulus. 27 view, 7 halaman utama diuji (syarat >= 4) |

## 0. Kondisi awal (baseline)

`php artisan test` pada `main` sebelum pengujian: **110 passed, 1 failed (711 assertions)**.
Yang gagal: `Tests\Feature\UiComponentsTest` baris 94 (memastikan halaman tidak memuat `window.confirm(`).
Penyebab: folder `public/build` tidak ikut di Git, sehingga di laptop yang belum menjalankan `npm run build` layout memakai mode CDN dan menyisipkan `resources/js/campus-space.js` yang berisi `window.confirm(` sebagai cadangan. Lihat Temuan 1.

Bukti screenshot: lampiran dokumen laporan Project III (Word).

## 1. Audit tag HTML5 semantik

Cara uji: login sebagai pengelola, buka tiap halaman, lalu hitung tag di Chrome DevTools Console dengan
`['header','nav','main','section','article','footer','aside'].map(t => t + ':' + document.getElementsByTagName(t).length)`.

| No | Halaman | URL | header | nav | main | section | article | footer | aside | Hasil |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | Dashboard | `/dashboard` | 2 | 2 | 1 | 2 | 0 | 1 | 1 | Lulus |
| 2 | Jelajahi Ruangan | `/rooms` | 2 | 3 | 1 | 1 | 12 | 1 | 1 | Lulus |
| 3 | Jadwal Ruangan | `/schedules` | 2 | 3 | 1 | 2 | 0 | 1 | 2 | Lulus |
| 4 | Peminjaman | `/bookings` | 2 | 3 | 1 | 1 | 0 | 1 | 1 | Lulus |
| 5 | Laporan Utilisasi | `/dashboard/utilization` | 2 | 2 | 1 | 6 | 4 | 1 | 2 | Lulus |
| 6 | Ruangan & Foto | `/rooms/manage` | 2 | 3 | 1 | **0** | 0 | 1 | 1 | Lulus, dengan saran |
| 7 | Tambah Ruang | `/rooms/create` | 2 | 2 | 1 | 1 | 0 | 1 | 1 | Lulus |

Kesimpulan: semua halaman memakai struktur semantik utama (`header`, `nav`, `main`, `footer`). `section` dipakai di 6 dari 7 halaman, `article` dipakai untuk kartu ruang dan kartu laporan. Halaman Ruangan & Foto belum membungkus tabelnya dengan `section` (Temuan 3).

Bukti screenshot: lampiran dokumen laporan Project III (Word).

## 2. Uji responsivitas

Cara uji: Chrome DevTools Device Toolbar (Ctrl + Shift + M), lalu menu ⋮ > Capture full size screenshot.

| No | Halaman | HP: iPhone SE 375 x 667 | Laptop: 1366 x 768 |
| --- | --- | --- | --- |
| 1 | Dashboard | Lulus | Lulus |
| 2 | Jelajahi Ruangan | Lulus | Lulus |
| 3 | Jadwal Ruangan | Lulus | Lulus |
| 4 | Peminjaman | Lulus | Lulus |
| 5 | Laporan Utilisasi | Lulus | Lulus |
| 6 | Ruangan & Foto | Lulus | Lulus |
| 7 | Tambah Ruang | Lulus | Lulus |

Yang diperiksa di setiap halaman: tidak ada bagian terpotong, tidak ada geser ke samping, teks dan tombol terbaca serta bisa ditekan.

- HP: isi tersusun satu kolom; sidebar berubah menjadi tombol ☰ yang membuka menu dari samping (drawer) dengan tombol × dan latar digelapkan.
- Laptop: sidebar selalu tampil; kartu statistik berjajar; tabel tampil lengkap.

Bukti screenshot: lampiran dokumen laporan Project III (Word).

## 3. Uji validasi form

| No | Form | Skenario | Input | Diharapkan | Hasil nyata | Pesan | Status |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | Tambah Ruang | Semua kosong | (kosong) | Ditolak | Ditolak | "Isian ini wajib diisi." di tiap kolom wajib | Lulus |
| 2 | Tambah Ruang | Kapasitas negatif | Kapasitas `-5` | Ditolak | Ditolak | "Nilai minimal 1." | Lulus |
| 3 | Tambah Ruang | Kode ruang kembar | Kode `KLS-201` (sudah ada) | Ditolak | Ditolak oleh server, data tidak tersimpan | "The code has already been taken." | Lulus, catatan bahasa |
| 4 | Ajukan Peminjaman | Semua kosong | (kosong) | Ditolak | Ditolak | "Isian ini wajib diisi." | Lulus |
| 5 | Ajukan Peminjaman | Bentrok jadwal | R10, 9 Nov 2026 13.30-14.30 (sudah ada booking disetujui 13.00-15.00) | Ditolak | Ditolak | "Jadwal bentrok dengan jadwal atau peminjaman lain di ruangan yang sama." | Lulus |
| 6 | Ajukan Peminjaman | Melebihi kapasitas | R10 (48 orang), 60 peserta | Ditolak | Ditolak | "Jumlah peserta melebihi kapasitas ruang (48 orang)." | Lulus |
| 7 | Ajukan Peminjaman | Waktu sudah lewat | 7 Nov 2026 11.00-12.00 | Ditolak | Ditolak | "The start time field must be a date after now." | Lulus, catatan bahasa |
| 8 | Ajukan Peminjaman | Selesai sebelum mulai | Mulai 14.00, selesai 12.00 | Ditolak | Ditolak | "Waktu selesai harus setelah waktu mulai." | Lulus |

Kesimpulan: semua input salah ditolak dan tidak ada data salah yang tersimpan. Validasi bekerja di dua lapis: browser (skenario 1, 2, 4, 8) dan server (skenario 3, 5, 6, 7).

Bukti screenshot: lampiran dokumen laporan Project III (Word).

## 4. Konsistensi CSS

Seluruh halaman memakai satu sistem gaya (`resources/css/campus-space.css`, prefiks `.cs-`): warna, tombol, kartu, form, dan pesan galat tampil seragam di semua halaman yang diuji, baik di HP maupun laptop.

## 5. Temuan

| No | Temuan | Dampak | Prioritas | Saran | Untuk |
| --- | --- | --- | --- | --- | --- |
| 1 | `public/build` tidak ada di Git; tanpa `npm run build` aplikasi memakai mode CDN, sehingga `UiComponentsTest` gagal (110/111) dan Console menampilkan peringatan "The browser build of Tailwind CSS should not be used in production" | Hasil test berbeda antar laptop; mode CDN tidak disarankan untuk demo/produksi | Sedang | Tambahkan `npm install && npm run build` ke panduan instalasi, dan sesuaikan test agar tidak bergantung pada ada atau tidaknya `public/build` | FE |
| 2 | Pesan validasi dari server masih berbahasa Inggris ("The code has already been taken.", "The start time field must be a date after now.") | Tidak konsisten dengan tampilan yang berbahasa Indonesia | Sedang | Tambahkan file terjemahan `lang/id/validation.php` dan atur `APP_LOCALE=id`, atau tambah `messages()` di Form Request | BE |
| 3 | Halaman Ruangan & Foto (`/rooms/manage`) tidak memakai `section` | Struktur semantik kurang lengkap (rubrik tetap terpenuhi) | Rendah | Bungkus tabel data ruang dengan `<section aria-label="Data master ruang">` | FE |

## 6. Kesimpulan

Seluruh kriteria Project III terpenuhi: halaman memakai HTML5 semantik, jumlah halaman melebihi 4, gaya CSS konsisten, tampilan responsif di HP dan laptop, dan validasi form menolak semua input yang salah. Ada 3 temuan (2 sedang, 1 rendah) yang tidak menghalangi penggunaan aplikasi.

## 7. Pemakaian AI

Langkah pengujian, skrip hitung tag di Console, dan kerangka laporan ini disusun dengan bantuan Claude (Anthropic). Seluruh pengujian dijalankan sendiri oleh QA di browser, dan setiap hasil diambil dari screenshot pengujian nyata.
