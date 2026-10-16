# Instruksi Claude Code

Ikuti `AGENTS.md` sebagai sumber aturan utama dan `README-AI-RULES.md` untuk alur kerja.

- Mulai sesi dengan `php scripts/ai-task.php start` agar prompt asli dan Nama Lengkap pengguna dicatat.
- Jangan meminta informasi identitas selain Nama Lengkap.
- Terapkan patch minimal: ubah hanya baris/bagian yang diperlukan untuk memenuhi permintaan.
- Jangan menulis ulang seluruh file, class, function, atau komponen jika perbaikan dapat dilakukan secara lokal.
- Periksa `git status` dan diff sebelum mengedit; jangan menimpa perubahan pengguna.
- Jangan melakukan refactor atau formatting yang tidak diminta.
- Dokumentasikan output AI yang tersedia, before–after, file terdampak, dan tes yang benar-benar dijalankan.
- Gunakan `php scripts/ai-task.php finish` hanya setelah meninjau perubahan. Jangan push otomatis.
- Gunakan Conventional Commits: `type(scope-opsional): deskripsi Bahasa Indonesia (by Nama Lengkap)`, misalnya `fix(auth): perbaiki validasi halaman login (by Yardan Zufar Adiwangsa)`. Pilih tipe yang tepat seperti `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`, `perf`, `build`, `ci`, atau `revert`.
