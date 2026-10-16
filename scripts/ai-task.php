<?php

declare(strict_types=1);

/**
 * Workflow sesi AI.
 * Jalankan dari root repository: php scripts/ai-task.php start|finish|validate
 */

function command(string $cmd, ?string &$output = null): int
{
    $lines = [];
    exec($cmd . ' 2>&1', $lines, $code);
    $output = implode(PHP_EOL, $lines);
    return $code;
}
function git(string $args, ?string &$output = null): int
{
    return command('git ' . $args, $output);
}
function rootPath(string $path = ''): string
{
    return dirname(__DIR__) . ($path !== '' ? DIRECTORY_SEPARATOR . $path : '');
}
function fail(string $message): never
{
    fwrite(STDERR, "GAGAL: {$message}" . PHP_EOL);
    exit(1);
}
function ask(string $label): string
{
    fwrite(STDOUT, $label . ': ');
    $line = fgets(STDIN);
    return trim($line === false ? '' : $line);
}
function ensureRoot(): void
{
    $out = '';
    if (git('rev-parse --show-toplevel', $out) !== 0 || realpath(trim($out)) !== realpath(rootPath())) {
        fail('Jalankan perintah dari root repository Git.');
    }
}
function activeSessions(): array
{
    $files = glob(rootPath('docs/ai-usage/sessions/AI-*.md')) ?: [];
    return array_values(array_filter($files, static function (string $file): bool {
        return str_contains(file_get_contents($file) ?: '', '- Status: AKTIF');
    }));
}
function detectProvider(): string
{
    // Detect only the presence of known environment markers; never print their values.
    $markers = [
        'Claude Code' => ['CLAUDECODE', 'CLAUDE_CODE', 'CLAUDE_CODE_ENTRYPOINT'],
        'Gemini CLI' => ['GEMINI_CLI'],
        'Codex' => ['CODEX_HOME', 'CODEX_THREAD_ID'],
        'GitHub Copilot' => ['COPILOT_AGENT', 'GITHUB_COPILOT'],
        'Cursor' => ['CURSOR_AGENT'],
        'Windsurf' => ['WINDSURF_AGENT'],
        'Antigravity' => ['ANTIGRAVITY_AGENT'],
    ];
    foreach ($markers as $provider => $variables) {
        foreach ($variables as $variable) {
            if (getenv($variable) !== false && trim((string) getenv($variable)) !== '') return $provider;
        }
    }
    return 'Tidak terdeteksi';
}
function start(): never
{
    ensureRoot();
    if (activeSessions() !== []) fail('Masih ada sesi aktif. Selesaikan dahulu dengan perintah finish.');
    $fullName = '';
    git('config user.name', $fullName);
    $fullName = trim($fullName);
    if ($fullName === '') fail('Nama Git belum dikonfigurasi. Jalankan: git config user.name "Nama Lengkap"');
    $provider = detectProvider();
    fwrite(STDOUT, "Nama dari konfigurasi Git: {$fullName}" . PHP_EOL);
    fwrite(STDOUT, "Provider terdeteksi: {$provider}" . PHP_EOL);
    if ($provider === 'Tidak terdeteksi') {
        $manualProvider = ask('Provider AI (opsional; Enter untuk melewati)');
        if ($manualProvider !== '') $provider = $manualProvider;
    }
    $goal = ask('Tujuan tugas');
    fwrite(STDOUT, "Masukkan prompt asli dalam satu baris. Jangan sertakan password, token, API key, atau rahasia." . PHP_EOL);
    $prompt = ask('Prompt asli');
    if ($goal === '' || $prompt === '') fail('Tujuan dan prompt asli wajib diisi untuk membuat laporan opsional.');
    if (preg_match('/\b(password|secret|api[_ -]?key|token)\s*[:=]\s*\S+/i', $prompt)) {
        fail('Prompt tampaknya mengandung rahasia. Hapus rahasia sebelum mencatat.');
    }

    $id = 'AI-' . date('Ymd-His');
    $dir = rootPath('docs/ai-usage/sessions');
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) fail('Tidak dapat membuat folder sesi.');
    $file = $dir . DIRECTORY_SEPARATOR . $id . '.md';
    $content = "# Laporan Sesi AI {$id}\n\n"
        . "- ID sesi: {$id}\n"
        . "- Nama Lengkap: {$fullName}\n"
        . "- Provider/platform: {$provider}\n"
        . "- Waktu mulai: " . date('c') . "\n"
        . "- Waktu selesai: Belum selesai\n"
        . "- Status: AKTIF\n"
        . "- Commit: BELUM\n\n"
        . "## Tujuan\n\n{$goal}\n\n"
        . "## Prompt asli\n\n{$prompt}\n\n"
        . "## Output/respons AI\n\nBelum dicatat. Tambahkan respons AI yang relevan jika tersedia dari provider. Jangan mengarang output.\n\n"
        . "## Before\n\nIsi kondisi awal/masalah sebelum perubahan.\n\n"
        . "## Pekerjaan AI\n\nIsi ringkasan perubahan yang benar-benar dilakukan.\n\n"
        . "## After\n\nIsi kondisi setelah perubahan dan verifikasi.\n\n"
        . "## File terdampak\n\nAkan diisi berdasarkan Git.\n\n"
        . "## Pengujian\n\nBelum dijalankan.\n\n"
        . "## Catatan verifikasi\n\nBelum diverifikasi.\n";
    if (file_put_contents($file, $content) === false) fail('Gagal menulis laporan sesi.');
    fwrite(STDOUT, "Sesi dibuat: docs/ai-usage/sessions/{$id}.md" . PHP_EOL);
    fwrite(STDOUT, "Setelah coding, periksa diff dan lengkapi bagian Output AI jika diperlukan, lalu jalankan: php scripts/ai-task.php finish" . PHP_EOL);
    exit(0);
}
function finish(): never
{
    ensureRoot();
    $sessions = activeSessions();
    if (count($sessions) !== 1) fail('Harus ada tepat satu sesi aktif. Jalankan start terlebih dahulu.');
    $file = $sessions[0];
    $content = file_get_contents($file) ?: '';

    $status = '';
    git('status --short --untracked-files=all', $status);
    if (trim($status) === '') fail('Tidak ada perubahan untuk didokumentasikan.');
    if (preg_match('/^(?:UU|AA|DD|AU|UA|DU|UD)\s/m', $status)) fail('Ada konflik Git. Selesaikan konflik terlebih dahulu.');

    $paths = '';
    git('status --porcelain=v1 --untracked-files=all', $paths);
    foreach (preg_split('/\R/', trim($paths)) as $line) {
        if ($line === '') continue;
        $path = trim(substr($line, 3), "\"");
        if (preg_match('/(?:^|\/)\.env(?:\..*)?$|(?:^|\/)(?:id_rsa|id_ed25519)$/i', $path)) {
            fail("File berpotensi rahasia terdeteksi: {$path}");
        }
    }

    $before = ask('Before — kondisi sebelum perubahan');
    $work = ask('Pekerjaan AI — apa yang benar-benar diubah');
    $after = ask('After — kondisi setelah perubahan');
    if ($before === '' || $work === '' || $after === '') fail('Before, pekerjaan AI, dan after wajib diisi.');

    fwrite(STDOUT, "Jalankan php artisan test? (y/N): ");
    $answer = strtolower(trim((string) fgets(STDIN)));
    $testResult = 'Tidak dijalankan.';
    if ($answer === 'y' || $answer === 'yes') {
        $testOutput = '';
        $code = command('php artisan test', $testOutput);
        $testResult = "Exit code: {$code}\n\n{$testOutput}";
        if ($code !== 0) fail("Tes gagal; commit dibatalkan.\n{$testOutput}");
    }

    $diffStat = '';
    git('diff --stat', $diffStat);
    $nameStatus = '';
    git('diff --name-status', $nameStatus);
    $untracked = '';
    git('ls-files --others --exclude-standard', $untracked);
    $fileList = trim($nameStatus . ($untracked !== '' ? "\nUntracked:\n" . $untracked : ''));

    $content = preg_replace('/- Status: AKTIF/', '- Status: SIAP DICOMMIT', $content, 1) ?? $content;
    $content = preg_replace('/- Waktu selesai: Belum selesai/', '- Waktu selesai: ' . date('c'), $content, 1) ?? $content;
    $content = preg_replace('/## Before\s+.*?\s+## Pekerjaan AI/s', "## Before\n\n{$before}\n\n## Pekerjaan AI", $content, 1) ?? $content;
    $content = preg_replace('/## Pekerjaan AI\s+.*?\s+## After/s', "## Pekerjaan AI\n\n{$work}\n\n## After", $content, 1) ?? $content;
    $content = preg_replace('/## After\s+.*?\s+## File terdampak/s', "## After\n\n{$after}\n\n## File terdampak", $content, 1) ?? $content;
    $content = preg_replace('/## File terdampak\s+.*?\s+## Pengujian/s', "## File terdampak\n\n```text\n{$fileList}\n```\n\nDiff stat:\n```text\n{$diffStat}\n```\n\n## Pengujian", $content, 1) ?? $content;
    $content = preg_replace('/## Pengujian\s+.*?\s+## Catatan verifikasi/s', "## Pengujian\n\n{$testResult}\n\n## Catatan verifikasi", $content, 1) ?? $content;
    file_put_contents($file, $content);

    fwrite(STDOUT, "Tipe Conventional Commit: feat, fix, docs, style, refactor, test, chore, perf, build, ci, revert." . PHP_EOL);
    $type = ask('Tipe commit');
    $scope = ask('Scope opsional (mis. auth, dashboard, booking; kosongkan jika tidak perlu)');
    $message = ask('Deskripsi perubahan dalam Bahasa Indonesia');
    $allowed = ['feat','fix','docs','style','refactor','test','chore','perf','build','ci','revert'];
    if (!in_array($type, $allowed, true) || $message === '') fail('Tipe/deskripsi commit tidak valid.');
    if ($scope !== '' && !preg_match('/^[a-z0-9][a-z0-9-]*$/', $scope)) fail('Scope harus berupa huruf kecil, angka, atau tanda hubung.');
    if (preg_match('/[\r\n]/', $message)) fail('Deskripsi commit harus satu baris.');
    $fullName = firstField($content, '- Nama Lengkap:');
    if ($fullName === '') fail('Nama Lengkap tidak ditemukan pada laporan sesi.');
    $subject = $type . ($scope !== '' ? '(' . $scope . ')' : '') . ': ' . $message . ' (by ' . $fullName . ')';

    fwrite(STDOUT, "\nPerubahan yang akan di-commit:\n{$status}\n");
    fwrite(STDOUT, "PERIKSA: git add -A memasukkan semua perubahan yang belum di-commit. Pastikan semuanya terkait tugas ini.\nKetik COMMIT untuk mengonfirmasi: ");
    if (trim((string) fgets(STDIN)) !== 'COMMIT') {
        $saved = file_get_contents($file) ?: '';
        $saved = preg_replace('/- Status: SIAP DICOMMIT/', '- Status: AKTIF', $saved, 1) ?? $saved;
        file_put_contents($file, $saved);
        fail('Commit dibatalkan. Sesi dikembalikan ke AKTIF; periksa perubahan lalu jalankan finish kembali.');
    }

    $promptIndex = rootPath('docs/ai-usage/PROMPT-LOG.md');
    $indexLine = "| " . basename($file, '.md') . " | " . date('Y-m-d') . " | " . str_replace('|', '/', firstField($content, '- Nama Lengkap:')) . " | " . str_replace('|', '/', firstField($content, '- Provider/platform:')) . " | " . str_replace('|', '/', firstSection($content, '## Tujuan')) . " | `sessions/" . basename($file) . "` | selesai |";
    file_put_contents($promptIndex, rtrim(file_get_contents($promptIndex) ?: '') . "\n" . $indexLine . "\n");

    $changeIndex = rootPath('docs/ai-usage/CHANGELOG-AI.md');
    $changeLine = "| " . basename($file, '.md') . " | " . str_replace('|', '/', $after) . " | " . str_replace('|', '/', strtok($testResult, "\n")) . " | menunggu commit |";
    file_put_contents($changeIndex, rtrim(file_get_contents($changeIndex) ?: '') . "\n" . $changeLine . "\n");

    $out = '';
    if (git('add -A', $out) !== 0) fail("git add gagal: {$out}");
    if (git('diff --cached --check', $out) !== 0) fail("Pemeriksaan diff gagal: {$out}");
    $staged = '';
    git('diff --cached --name-only', $staged);
    if (preg_match('/(?:^|\/)\.env(?:\..*)?$|(?:^|\/)(?:id_rsa|id_ed25519)$/im', $staged)) fail('File rahasia masuk staging. Commit dibatalkan.');
    if (preg_match('/(^|\/)\.env(?:\..*)?$|(?:^|\/)(?:id_rsa|id_ed25519)$/im', $staged)) fail('File sensitif terdeteksi.');

    if (git('commit -m ' . escapeshellarg($subject), $out) !== 0) fail("Commit gagal:\n{$out}");
    $hash = '';
    git('rev-parse --short HEAD', $hash);
    $hash = trim($hash);
    $content = file_get_contents($file) ?: '';
    $content = preg_replace('/- Status: SIAP DICOMMIT/', '- Status: SELESAI', $content, 1) ?? $content;
    $content = preg_replace('/- Commit: BELUM/', '- Commit: ' . $hash . ' (`' . $subject . '`)' , $content, 1) ?? $content;
    file_put_contents($file, $content);

    // Update only the newest changelog row, not historical rows.
    $changeIndexContent = file_get_contents($changeIndex) ?: '';
    $changeIndexContent = preg_replace('/\| menunggu commit \|\s*$/m', '| ' . $hash . ' |', $changeIndexContent, 1) ?? $changeIndexContent;
    file_put_contents($changeIndex, $changeIndexContent);

    // The hash is only known after the task commit, so record it in a small follow-up docs commit.
    $out = '';
    $metaFiles = 'docs/ai-usage/sessions/' . basename($file) . ' docs/ai-usage/CHANGELOG-AI.md';
    if (git('add ' . escapeshellarg('docs/ai-usage/sessions/' . basename($file)) . ' ' . escapeshellarg('docs/ai-usage/CHANGELOG-AI.md'), $out) !== 0) {
        fail("Task commit berhasil ({$hash}), tetapi staging metadata gagal. Periksa status Git. {$out}");
    }
    if (git('commit -m ' . escapeshellarg('docs: catat metadata sesi AI ' . basename($file, '.md') . ' (by ' . $fullName . ')' ), $out) !== 0) {
        fail("Task commit berhasil ({$hash}), tetapi commit metadata gagal. Periksa status Git. {$out}");
    }
    fwrite(STDOUT, "Task commit berhasil: {$subject} ({$hash})\n");
    fwrite(STDOUT, "Hash task dicatat dalam commit dokumentasi terpisah.\nTidak ada push yang dilakukan.\n");
    exit(0);
}
function firstField(string $content, string $prefix): string
{
    foreach (preg_split('/\R/', $content) as $line) if (str_starts_with($line, $prefix)) return trim(substr($line, strlen($prefix)));
    return '';
}
function firstSection(string $content, string $heading): string
{
    if (preg_match('/' . preg_quote($heading, '/') . '\s+(.*?)\s+\n## /s', $content, $m)) return trim(str_replace("\n", ' ', $m[1]));
    return '';
}
ensureRoot();
$mode = $argv[1] ?? '';
if ($mode === 'start') start();
if ($mode === 'finish') finish();
if ($mode === 'validate') {
    fwrite(STDOUT, "Validasi aturan AI: pencatatan sesi bersifat opsional; commit tidak diblokir oleh sesi AI.\n");
    exit(0);
}
fwrite(STDERR, "Penggunaan: php scripts/ai-task.php start|finish|validate\n");
exit(2);
