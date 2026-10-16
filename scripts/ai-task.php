<?php
declare(strict_types=1);

/**
 * Workflow Pencatatan Sesi AI Otomatis (Riwayat Gabungan).
 * Dieksekusi otomatis oleh AI di belakang layar:
 *   php scripts/ai-task.php auto --provider="Nama AI" --goal="Tujuan" --prompt="Prompt user" --before="Kondisi awal" --work="Pekerjaan AI" --after="Kondisi akhir" --output="Respons AI"
 */

function command(string $cmd, ?string &$output = null): int {
    $lines = [];
    $code = 0;
    exec($cmd . ' 2>&1', $lines, $code);
    $output = implode(PHP_EOL, $lines);
    return $code;
}

function git(string $args, ?string &$output = null): int {
    return command('git ' . $args, $output);
}

function rootPath(string $path = ''): string {
    $base = dirname(__DIR__);
    if ($path === '') {
        return $base;
    }
    return $base . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
}

function fail(string $message): never {
    fwrite(STDERR, "GAGAL: {$message}" . PHP_EOL);
    exit(1);
}

function ensureRoot(): void {
    $out = '';
    if (git('rev-parse --show-toplevel', $out) !== 0) {
        fail('Gagal memeriksa root repository Git.');
    }
    $repoRoot = realpath(trim($out));
    $expectedRoot = realpath(rootPath());
    if ($repoRoot === false || $expectedRoot === false || strcasecmp($repoRoot, $expectedRoot) !== 0) {
        fail('Jalankan perintah dari root repository Git.');
    }
}

function getGitIdentity(): array {
    $name = '';
    $email = '';
    git('config user.name', $name);
    git('config user.email', $email);
    $name = trim($name);
    $email = trim($email);

    if ($name === '' || $email === '') {
        fail('Identitas Git belum lengkap. Jalankan: git config user.name "Nama Lengkap" dan git config user.email "email@domain.com"');
    }
    return ['name' => $name, 'email' => $email];
}

function slugify(string $text): string {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text) ?? 'user', '-'));
    return $slug !== '' ? $slug : 'user';
}

function detectProvider(?string $override = null): string {
    if ($override !== null && trim($override) !== '') {
        return trim($override);
    }
    $markers = [
        'Claude Code'    => ['CLAUDECODE', 'CLAUDE_CODE', 'CLAUDE_CODE_ENTRYPOINT'],
        'Gemini CLI'     => ['GEMINI_CLI', 'GEMINI_AGENT'],
        'Codex'          => ['CODEX_HOME', 'CODEX_THREAD_ID'],
        'GitHub Copilot' => ['COPILOT_AGENT', 'GITHUB_COPILOT'],
        'Cursor'         => ['CURSOR_AGENT', 'CURSOR_TRACE_ID'],
        'Windsurf'       => ['WINDSURF_AGENT'],
        'Antigravity'    => ['ANTIGRAVITY_AGENT'],
    ];
    foreach ($markers as $provider => $variables) {
        foreach ($variables as $variable) {
            if (getenv($variable) !== false && trim((string) getenv($variable)) !== '') {
                return $provider;
            }
        }
    }
    return 'AI Assistant (Auto)';
}

function sanitizeSecrets(string $text): string {
    return preg_replace('/\b(password|secret|api[_ -]?key|token|authorization)\s*([:=])\s*\S+/i', '$1$2 [REDACTED]', $text) ?? $text;
}

function parseCliArgs(array $argv): array {
    $options = [];
    $commands = [];

    for ($i = 1; $i < count($argv); $i++) {
        $arg = $argv[$i];
        if (str_starts_with($arg, '--')) {
            $opt = substr($arg, 2);
            if (str_contains($opt, '=')) {
                [$key, $val] = explode('=', $opt, 2);
                $options[$key] = $val;
            } else {
                if (isset($argv[$i + 1]) && !str_starts_with($argv[$i + 1], '-')) {
                    $options[$opt] = $argv[++$i];
                } else {
                    $options[$opt] = '';
                }
            }
        } elseif (str_starts_with($arg, '-')) {
            $opt = substr($arg, 1);
            if (isset($argv[$i + 1]) && !str_starts_with($argv[$i + 1], '-')) {
                $options[$opt] = $argv[++$i];
            } else {
                $options[$opt] = '';
            }
        } else {
            $commands[] = $arg;
        }
    }

    return [
        'mode'    => $commands[0] ?? '',
        'options' => $options,
    ];
}

function autoLog(array $opts = []): never {
    ensureRoot();

    $identity = getGitIdentity();
    $fullName = $identity['name'];
    $email    = $identity['email'];
    $userSlug = slugify($fullName);
    $provider = detectProvider($opts['provider'] ?? null);

    $prompt = sanitizeSecrets(trim((string) ($opts['prompt'] ?? '')));
    if ($prompt === '') {
        fail('Parameter --prompt wajib diisi oleh AI.');
    }

    $goal = trim((string) ($opts['goal'] ?? ''));
    if ($goal === '') {
        $goal = mb_strimwidth(preg_replace('/\s+/', ' ', $prompt) ?? $prompt, 0, 80, '...');
    }

    $aiOutput   = sanitizeSecrets(trim((string) ($opts['output'] ?? 'Dieksekusi langsung oleh AI pada working tree.')));
    $before     = trim((string) ($opts['before'] ?? 'Kondisi sebelum perubahan kode.'));
    $work       = trim((string) ($opts['work'] ?? 'Perubahan kode sesuai prompt pengguna.'));
    $after      = trim((string) ($opts['after'] ?? 'Perubahan selesai diterapkan.'));
    $testResult = trim((string) ($opts['test'] ?? 'Tidak dijalankan otomatis.'));

    $diffStat = '';
    git('diff --stat', $diffStat);
    $nameStatus = '';
    git('diff --name-status', $nameStatus);
    $untracked = '';
    git('ls-files --others --exclude-standard', $untracked);
    $fileList = trim($nameStatus . ($untracked !== '' ? "\nUntracked:\n" . $untracked : ''));
    if ($fileList === '') {
        $fileList = 'Tidak ada file yang dimodifikasi (hanya diskusi/analisis).';
    }

    $id  = 'AI-' . date('Ymd-His');
    $now = date('Y-m-d H:i:s P');
    $dir = rootPath('docs/ai-usage/sessions');
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        fail('Tidak dapat membuat folder sesi.');
    }

    // Format blok riwayat yang akan ditambahkan (append)
    $sessionEntry = "\n---\n\n"
        . "## Sesi {$id} — {$now}\n\n"
        . "- **Identitas Git**: {$fullName} (`{$email}`)\n"
        . "- **Provider AI**: {$provider}\n"
        . "- **Tujuan**: {$goal}\n\n"
        . "### Prompt Asli\n> " . str_replace("\n", "\n> ", $prompt) . "\n\n"
        . "### Output / Respons AI\n{$aiOutput}\n\n"
        . "### Ringkasan Perubahan (Before - Work - After)\n"
        . "- **Before (Kondisi Awal)**: {$before}\n"
        . "- **Pekerjaan AI**: {$work}\n"
        . "- **After (Kondisi Akhir)**: {$after}\n"
        . "- **Pengujian**: {$testResult}\n\n"
        . "### File Terdampak\n```text\n{$fileList}\n```\n"
        . ($diffStat !== '' ? "Diff stat:\n```text\n{$diffStat}\n```\n" : '');

    // 1. Gabungkan ke File Riwayat Masing-Masing User
    $userFileName = "riwayat-{$userSlug}.md";
    $userFilePath = $dir . DIRECTORY_SEPARATOR . $userFileName;
    if (!file_exists($userFilePath)) {
        $userHeader = "# Riwayat Penggunaan AI — {$fullName}\n\n"
            . "## Identitas Anggota\n"
            . "- **Nama Lengkap (`user.name`)**: {$fullName}\n"
            . "- **Email Git (`user.email`)**: {$email}\n"
            . "- **Pembaruan Terakhir**: {$now}\n";
        file_put_contents($userFilePath, $userHeader);
    } else {
        $existingUser = file_get_contents($userFilePath) ?: '';
        $existingUser = preg_replace('/- \*\*Pembaruan Terakhir\*\*: .*/', "- **Pembaruan Terakhir**: {$now}", $existingUser, 1) ?? $existingUser;
        file_put_contents($userFilePath, $existingUser);
    }
    file_put_contents($userFilePath, $sessionEntry, FILE_APPEND);

    // 2. Gabungkan ke File Riwayat Seluruh Tim
    $allFileName = "RIWAYAT-SEMUA-SESI.md";
    $allFilePath = $dir . DIRECTORY_SEPARATOR . $allFileName;
    if (!file_exists($allFilePath)) {
        $allHeader = "# Riwayat Gabungan Seluruh Sesi AI (Semua Anggota)\n\n"
            . "File ini berisi gabungan seluruh riwayat prompt dan aktivitas AI dari semua anggota tim berdasarkan `git config user.name` dan `git config user.email`.\n";
        file_put_contents($allFilePath, $allHeader);
    }
    file_put_contents($allFilePath, $sessionEntry, FILE_APPEND);

    // 3. Tambahkan baris ke PROMPT-LOG.md
    $promptIndex = rootPath('docs/ai-usage/PROMPT-LOG.md');
    $promptDir = dirname($promptIndex);
    if (!is_dir($promptDir) && !mkdir($promptDir, 0775, true) && !is_dir($promptDir)) {
        fail('Tidak dapat membuat folder dokumentasi ai-usage.');
    }
    if (!file_exists($promptIndex)) {
        $promptHeader = "# Indeks Prompt AI (Otomatis)\n\n"
            . "Baris pada tabel ini ditambahkan secara otomatis oleh AI setiap kali pengguna memberikan prompt, menggunakan identitas dari `git config user.name` dan `git config user.email`.\n\n"
            . "| ID Sesi | Tanggal | Identitas Git (Nama & Email) | Provider | Tujuan | File Riwayat | Status |\n"
            . "|---|---|---|---|---|---|---|\n";
        file_put_contents($promptIndex, $promptHeader);
    }
    $cleanName   = str_replace('|', '/', "{$fullName} ({$email})");
    $cleanProv   = str_replace('|', '/', $provider);
    $cleanGoal   = str_replace('|', '/', $goal);
    $indexLine   = "| {$id} | " . date('Y-m-d H:i') . " | {$cleanName} | {$cleanProv} | {$cleanGoal} | `sessions/{$userFileName}` | tercatat otomatis |";
    file_put_contents($promptIndex, rtrim(file_get_contents($promptIndex) ?: '') . "\n" . $indexLine . "\n");

    // 4. Tambahkan baris ke CHANGELOG-AI.md
    $changeIndex = rootPath('docs/ai-usage/CHANGELOG-AI.md');
    if (!file_exists($changeIndex)) {
        $changeHeader = "# Indeks Perubahan AI (Otomatis)\n\n"
            . "| ID Sesi | Identitas Git (Nama & Email) | Ringkasan Perubahan | Pengujian | File Riwayat |\n"
            . "|---|---|---|---|---|\n";
        file_put_contents($changeIndex, $changeHeader);
    }
    $cleanAfter  = str_replace('|', '/', $after);
    $cleanTest   = str_replace('|', '/', strtok($testResult, "\n") ?: 'Tidak dijalankan');
    $changeLine  = "| {$id} | {$cleanName} | {$cleanAfter} | {$cleanTest} | `sessions/{$userFileName}` |";
    file_put_contents($changeIndex, rtrim(file_get_contents($changeIndex) ?: '') . "\n" . $changeLine . "\n");

    fwrite(STDOUT, "Riwayat AI otomatis ditambahkan ke:\n- docs/ai-usage/sessions/{$userFileName}\n- docs/ai-usage/sessions/{$allFileName}\n");
    exit(0);
}

$cli = parseCliArgs($argv);
$mode = $cli['mode'];

if ($mode === 'auto') {
    autoLog($cli['options']);
}

if ($mode === 'validate') {
    ensureRoot();
    $identity = getGitIdentity();
    fwrite(STDOUT, "Konfigurasi siap. Git User: {$identity['name']} <{$identity['email']}>\n");
    exit(0);
}

fwrite(STDERR, "Penggunaan: php scripts/ai-task.php auto --provider=\"...\" --goal=\"...\" --prompt=\"...\" [--before=\"...\"] [--work=\"...\"] [--after=\"...\"] [--output=\"...\"]\n");
exit(2);