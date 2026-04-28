<?php
// =====================================================
// ТАМОМИ РОҲҲО АЗ /arxiv/ СУРАТ МЕГИРАНД
// =====================================================
define('BASE_URL',   '/arxiv');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_SIZE',   20 * 1024 * 1024); // 20MB

// ——— Базаи маълумот (Neon) ———

define('DB_HOST', 'ep-holy-moon-abf98mmg.eu-west-2.aws.neon.tech'); // ← аз Neon
define('DB_PORT', '8080');           // ← аз Neon
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
