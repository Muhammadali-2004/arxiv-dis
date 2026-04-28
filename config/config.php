<?php
// =====================================================
// ТАМОМИ РОҲҲО АЗ /arxiv/ СУРАТ МЕГИРАНД
// =====================================================
define('BASE_URL',   '/arxiv');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_SIZE',   20 * 1024 * 1024); // 20MB

// ——— Базаи маълумот (Neon) ———

define('DB_HOST', 'ep-holy-moon-abf98mmg.eu-west-2.aws.neon.tech'); // ← аз Neon
define('DB_PORT', '543');
define('DB_NAME', 'neondb');         // ← аз Neon
define('DB_USER', 'neondb_owner');   // ← аз Neon
define('DB_PASS', 'npg_j7JhlFuB3Dot');       // ← аз Neon

function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $dsn = "pgsql:host=".DB_HOST.";port=".DB_PORT.
               ";dbname=".DB_NAME.";sslmode=require";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}


// ——— Ёрдамчиҳо ———
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
    if ($d < 60)   return 'Ҳозир';
    if ($d < 3600) return floor($d/60).' дақ. пеш';
    if ($d < 86400)return floor($d/3600).' соат пеш';
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
