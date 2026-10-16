# Instruksi Gemini

Ikuti `AGENTS.md` sebagai sumber aturan utama dan `README-AI-RULES.md` untuk alur kerja.

- **Pencatatan Otomatis**: Pengguna tidak perlu menjalankan `start` atau `finish`. Setiap kali selesai memproses prompt pengguna, Anda WAJIB menjalankan secara otomatis di terminal:
  `php scripts/ai-task.php auto --provider="Gemini" --goal="..." --prompt="..." --output="..." --before="..." --work="..." --after="..." --test="..."`
- Identitas pengguna (`user.name` dan `user.email`) diambil otomatis oleh script dari konfigurasi Git lokal. Jangan meminta identitas di chat.
- Perbaiki hanya baris/bagian yang terkait langsung dengan permintaan. Jangan mengganti seluruh file/function/komponen untuk perbaikan lokal.
- Periksa konteks kode dan `git diff` sebelum mengedit; pertahankan perubahan yang sudah ada.
- Hindari refactor atau formatting massal yang tidak diminta.
- Jangan melakukan push otomatis atau melewati hook.
- Gunakan Conventional Commits: `type(scope-opsional): deskripsi Bahasa Indonesia (by Nama Lengkap)` sesuai `git config user.name`.