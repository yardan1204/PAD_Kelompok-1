# Instruksi Claude Code

Ikuti `AGENTS.md` sebagai sumber aturan utama dan `README-AI-RULES.md` untuk alur kerja.

- **Pencatatan Otomatis**: Pengguna tidak perlu menjalankan `start` atau `finish`. Setiap kali selesai memproses prompt pengguna, Anda WAJIB menjalankan secara otomatis di terminal:
  `php scripts/ai-task.php auto --provider="Claude Code" --goal="..." --prompt="..." --output="..." --before="..." --work="..." --after="..." --test="..."`
- Identitas pengguna (`user.name` dan `user.email`) diambil otomatis oleh script dari konfigurasi Git lokal. Jangan meminta identitas di chat.
- Terapkan patch minimal: ubah hanya baris/bagian yang diperlukan untuk memenuhi permintaan.
- Jangan menulis ulang seluruh file, class, function, atau komponen jika perbaikan dapat dilakukan secara lokal.
- Periksa `git status` dan diff sebelum mengedit; jangan menimpa perubahan pengguna.
- Jangan melakukan refactor atau formatting yang tidak diminta.
- Jangan push otomatis.
- Gunakan Conventional Commits: `type(scope-opsional): deskripsi Bahasa Indonesia (by Nama Lengkap)` sesuai `git config user.name`.