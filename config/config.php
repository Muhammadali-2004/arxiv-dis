<?php
// =====================================================
// АРХИВИ КОРҲОИ ИЛМӢ — ДИС ДДТТ
// Танзимот: PostgreSQL Cloud (Neon)
// =====================================================

if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);
    
    // ─── Маълумоти Neon PostgreSQL ───
    define('DB_HOST', 'ep-holy-moon-abf98mmg.eu-west-2.aws.neon.tech');
    define('DB_PORT', '5432');
    define('DB_NAME', 'neondb');
    define('DB_USER', 'neondb_owner');
    define('DB_PASS', 'npg_j7JhlFuB3Dot');
    
    // ─── Танзимоти асосӣ ───
    define('BASE_URL', '');
    define('UPLOAD_DIR', __DIR__ . '/../uploads/');
    define('MAX_SIZE', 20 * 1024 * 1024);
    define('SITE_NAME', 'Архиви Корҳои Илмӣ');
    define('SITE_FULL', 'Системаи Иттилоотии Архиви Корҳои Илмии Донишҷӯён');
}

// ─── Пайвасти PostgreSQL ───
if (!function_exists('db')) {
    function db(): PDO {
        static $pdo;
        if (!$pdo) {
            try {
                $dsn = "pgsql:host=".DB_HOST.";port=".DB_PORT.
                       ";dbname=".DB_NAME.";sslmode=require";
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                $pdo->exec("SET NAMES 'UTF8'");
            } catch (PDOException $e) {
                die('<div style="font-family:Georgia,serif;padding:40px;background:#f8f9fa;color:#000;border:2px solid #1e40af;border-radius:4px;margin:40px auto;max-width:600px">
                    <h2 style="color:#1e40af;border-bottom:2px solid #1e40af;padding-bottom:10px">Хатои пайваст</h2>
                    <p>'.htmlspecialchars($e->getMessage()).'</p>
                </div>');
            }
        }
        return $pdo;
    }
}

// ─── Ёрдамчиҳо ───
if (!function_exists('url')) {
    function url(string $path = ''): string {
        return BASE_URL . '/' . ltrim($path, '/');
    }
}

if (!function_exists('e')) {
    function e(?string $s): string {
        return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('filesize_human')) {
    function filesize_human(int $b): string {
        if ($b >= 1048576) return round($b/1048576, 1).' MB';
        if ($b >= 1024)    return round($b/1024, 0).' KB';
        return $b.' B';
    }
}

if (!function_exists('time_ago')) {
    function time_ago(string $dt): string {
        $d = time() - strtotime($dt);
        if ($d < 60)    return 'Ҳозир';
        if ($d < 3600)  return floor($d/60).' дақ. пеш';
        if ($d < 86400) return floor($d/3600).' соат пеш';
        if ($d < 2592000) return floor($d/86400).' рӯз пеш';
        return date('d.m.Y', strtotime($dt));
    }
}

if (!function_exists('work_type_label')) {
    function work_type_label(?string $type): string {
        return match($type) {
            'курсовая' => 'Кори курсӣ',
            'дипломная' => 'Кори дипломӣ',
            'магистр' => 'Магистрӣ',
            'мақола' => 'Мақолаи илмӣ',
            'реферат' => 'Реферат',
            default => '—'
        };
    }
}

if (!function_exists('status_label')) {
    function status_label(?string $status): string {
        return match($status) {
            'draft' => 'Лоиҳа',
            'pending' => 'Дар интизор',
            'approved' => 'Тасдиқшуда',
            'rejected' => 'Радшуда',
            default => '—'
        };
    }
}

if (!function_exists('log_activity')) {
    function log_activity(?int $uid, string $action, string $desc = ''): void {
        try {
            db()->prepare("INSERT INTO activity_log(user_id, action, description) VALUES(:u,:a,:d)")
                ->execute([':u'=>$uid, ':a'=>$action, ':d'=>$desc]);
        } catch (Exception $e) { /* silent */ }
    }
}
