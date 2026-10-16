# GitHub Copilot — Instruksi Proyek

Ikuti `AGENTS.md` dan `README-AI-RULES.md`.

- Mulai sesi tugas dengan `php scripts/ai-task.php start`.
- Identitas anggota yang diminta hanya Nama Lengkap.
- Lakukan perubahan kode sekecil mungkin dan hanya pada baris/bagian yang terkait langsung dengan permintaan.
- Jangan mengganti seluruh file/function/class/komponen untuk memperbaiki masalah lokal.
- Periksa konteks dan Git diff sebelum mengedit; pertahankan perubahan yang sudah ada.
- Jangan melakukan refactor, rename, atau formatting massal yang tidak diminta.
- Catat prompt asli, output yang tersedia, file terdampak, before–after, dan tes sebenarnya.
- Sebelum commit, tinjau `git status` dan diff. Gunakan `php scripts/ai-task.php finish` hanya jika seluruh perubahan yang akan di-commit memang terkait tugas.
- Jangan push otomatis atau melewati hook.
- Format commit wajib mengikuti Conventional Commits: `type(scope-opsional): deskripsi Bahasa Indonesia (by Nama Lengkap)`, misalnya `fix(auth): perbaiki validasi halaman login (by Yardan Zufar Adiwangsa)`. Gunakan tipe seperti `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`, `perf`, `build`, `ci`, atau `revert`.
