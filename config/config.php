<?php
// =====================================================
// ТАНЗИМИ ПАЙВАСТ — Neon PostgreSQL (Cloud)
// АРХИВИ КОРҲОИ ИЛМӢ — ДИС ДДТТ
// =====================================================

// ─── Маълумоти Neon PostgreSQL ─────────────────────
define('DB_HOST', 'ep-holy-moon-abf98mmg.eu-west-2.aws.neon.tech');
define('DB_PORT', '5432');
define('DB_NAME', 'neondb');
define('DB_USER', 'neondb_owner');
define('DB_PASS', 'npg_j7JhlFuB3Dot');

// ─── Танзими URL ───────────────────────────────────
// Агар сайт дар домени асосӣ бошад (масалан arxiv.example.com) → ''
// Агар сайт дар поддиректория бошад (масалан example.com/arxiv) → '/arxiv'
define('BASE_URL',   '');

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_SIZE',   20 * 1024 * 1024); // 20MB

// ─── Пайваст ба Neon PostgreSQL ────────────────────
function db(): PDO {
    static $pdo;
    if (!$pdo) {
        try {
            $dsn = "pgsql:host=".DB_HOST.";port=".DB_PORT.
                   ";dbname=".DB_NAME.";sslmode=require";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT         => false,
            ]);
            // UTF-8
            $pdo->exec("SET NAMES 'UTF8'");
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:40px;background:#fee2e2;
                        color:#991b1b;border-radius:8px;margin:40px auto;max-width:600px">
                <h2>⚠️ Хатои пайваст ба Neon PostgreSQL</h2>
                <p style="margin-top:10px">'.htmlspecialchars($e->getMessage()).'</p>
                <hr style="margin:16px 0">
                <p><b>Тафтиш кунед:</b></p>
                <ol style="margin-top:8px;line-height:2">
                    <li>Neon аккаунт фаъол аст?</li>
                    <li>Маълумоти DB_HOST, DB_USER, DB_PASS дуруст аст?</li>
                    <li>Сервери шумо PostgreSQL-ро дастгирӣ мекунад? (php_pdo_pgsql)</li>
                    <li>Файли SQL дар Neon SQL Editor иҷро шуд?</li>
                </ol>
            </div>');
        }
    }
    return $pdo;
}

// ─── Ёрдамчиҳои умумӣ ──────────────────────────────
function url(string $path = ''): string {
    return BASE_URL . '/' . ltrim($path, '/');
}

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function filesize_human(int $b): string {
    if ($b >= 1048576) return round($b/1048576, 1).' MB';
    if ($b >= 1024)    return round($b/1024, 0).' KB';
    return $b.' B';
}

function time_ago(string $dt): string {
    $d = time() - strtotime($dt);
    if ($d < 60)    return 'Ҳозир';
    if ($d < 3600)  return floor($d/60).' дақ. пеш';
    if ($d < 86400) return floor($d/3600).' соат пеш';
    return date('d.m.Y', strtotime($dt));
}

function work_icon(string $type): string {
    return match(true) {
        str_contains($type, 'хатм')   => '🎓',
        str_contains($type, 'курсӣ')  => '📝',
        str_contains($type, 'мақола') => '📰',
        str_contains($type, 'лоиҳа')  => '⚙️',
        default => '📄'
    };
}
