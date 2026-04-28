<?php

// === БАЗОВЫЕ НАСТРОЙКИ ===
define('BASE_URL', '');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_SIZE', 20 * 1024 * 1024); // 20MB


// === ПОДКЛЮЧЕНИЕ К БАЗЕ (Railway / Neon) ===

// Railway автоматически даёт DATABASE_URL
$dbUrl = getenv('DATABASE_URL');

if (!$dbUrl) {
    die('DATABASE_URL не найден');
}

// парсим URL
$parsed = parse_url($dbUrl);

define('DB_HOST', $parsed['ep-holy-moon-abf98mmg.eu-west-2.aws.neon.tech']);
define('DB_PORT', $parsed['port']);
define('DB_NAME', ltrim($parsed['neondb'], '/'));
define('DB_USER', $parsed['neondb_owner']);
define('DB_PASS', $parsed['npg_j7JhlFuB3Dot']);


// === ПОДКЛЮЧЕНИЕ PDO ===
if (!function_exists('db')) {
    function db(): PDO {
        static $pdo;

        if (!$pdo) {
            $dsn = "pgsql:host=" . DB_HOST .
                   ";port=" . DB_PORT .
                   ";dbname=" . DB_NAME .
                   ";sslmode=require";

            try {
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                die("Ошибка подключения к БД: " . $e->getMessage());
            }
        }

        return $pdo;
    }
}