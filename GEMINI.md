# Instruksi Gemini

Ikuti `AGENTS.md` sebagai sumber aturan utama dan `README-AI-RULES.md` untuk alur kerja.

- Untuk tugas baru, pastikan sesi sudah dicatat dengan `php scripts/ai-task.php start`.
- Identitas anggota hanya memerlukan Nama Lengkap; jangan meminta data identitas lain.
- Perbaiki hanya baris/bagian yang terkait langsung dengan permintaan. Jangan mengganti seluruh file/function/komponen untuk perbaikan lokal.
- Periksa konteks kode dan `git diff` sebelum mengedit; pertahankan perubahan yang sudah ada.
- Hindari refactor atau formatting massal yang tidak diminta.
- Catat prompt asli, output yang tersedia, file terdampak, before–after, dan hasil tes. Jangan mengarang data yang tidak tersedia.
- Setelah coding, periksa diff dan jalankan `php scripts/ai-task.php finish` hanya jika perubahan yang akan di-commit aman dan terkait tugas.
- Jangan melakukan push otomatis atau melewati hook.
- Gunakan Conventional Commits: `type(scope-opsional): deskripsi Bahasa Indonesia (by Nama Lengkap)`, misalnya `fix(auth): perbaiki validasi halaman login (by Yardan Zufar Adiwangsa)`. Pilih tipe yang tepat seperti `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`, `perf`, `build`, `ci`, atau `revert`.
