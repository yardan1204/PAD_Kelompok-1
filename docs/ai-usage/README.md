# Dokumentasi Penggunaan AI (Opsional)

Anggota boleh langsung menggunakan AI tanpa membuat laporan sesi. File aturan proyek tetap berlaku jika provider yang dipakai membacanya.

## Jika laporan diperlukan
1. Pastikan `git config user.name` sudah diatur.
2. Jalankan `php scripts/ai-task.php start`.
3. Nama diambil otomatis dari konfigurasi Git. Provider dicoba dideteksi dari environment; jika tidak tersedia, input provider ditawarkan secara opsional.
4. Kerjakan tugas dengan AI dan periksa `git status` serta `git diff`.
5. Jalankan `php scripts/ai-task.php finish` hanya jika ingin menyimpan laporan dan memakai alur commit script.

Tidak ada hook yang mewajibkan sesi AI aktif atau laporan AI sebelum commit. Jangan menyimpan rahasia atau data sensitif. Prompt/output hanya dicatat jika benar-benar dimasukkan/tersedia; tidak semua provider menyediakan akses otomatis.
