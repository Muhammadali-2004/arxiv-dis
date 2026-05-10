<?php
session_start();
require_once __DIR__ . '/config/config.php';
if (!empty($_SESSION['uid'])) log_activity($_SESSION['uid'], 'logout', 'Хуруҷ');
session_destroy();
header('Location: '.url('login.php'));
exit;
