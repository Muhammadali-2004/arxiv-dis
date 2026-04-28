<?php
require_once __DIR__ . '/auth.php';
$cur = basename($_SERVER['PHP_SELF'], '.php');
$u   = current_user();
function nav_a(string $href, string $label, string $cur_page, string $id): string {
    $active = ($cur_page === $id) ? ' class="on"' : '';
    return '<a href="'.url($href).'"'.$active.'>'.$label.'</a>';
}
?>
<!DOCTYPE html>
<html lang="tg">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= isset($page_title) ? e($page_title).' — ' : '' ?>Архиви Корҳои Илмӣ · ДИС ДДТТ</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">
</head>
<body>

<header class="site-header">
  <div class="hdr-top">
    Донишкадаи Иқтисоди ва Савдои (ДИС) — Донишгоҳи Давлатии Технологии Тоҷикистон
  </div>
  <div class="hdr-row">
    <a href="<?= url('index.php') ?>" class="logo">
      <div class="logo-icon">📚</div>
      <div class="logo-text">
        <b>Архиви Корҳои Илмӣ</b>
        <small>ДИС ДДТТ · Системаи Иттилоотӣ</small>
      </div>
    </a>

    <nav class="site-nav">
      <?= nav_a('index.php',  '🏠 Асосӣ',      $cur, 'index') ?>
      <?= nav_a('works.php',  '📄 Архив',       $cur, 'works') ?>
      <?php if (logged_in()): ?>
        <?= nav_a('upload.php','⬆️ Бор кардан', $cur, 'upload') ?>
        <?php if (is_admin()): ?>
          <?= nav_a('admin.php','⚙️ Идора',     $cur, 'admin') ?>
        <?php endif; ?>
        <span class="hdr-user">👤 <?= e($u['name']) ?>
          <span class="badge b-<?= e($u['role']) ?>"><?= e($u['role']) ?></span>
        </span>
        <?= nav_a('profile.php','Профил',        $cur, 'profile') ?>
        <a href="<?= url('logout.php') ?>" class="btn btn-ghost btn-sm" style="margin-left:4px">Хуруҷ</a>
      <?php else: ?>
        <a href="<?= url('register.php') ?>">Қайд</a>
        <a href="<?= url('login.php') ?>" class="nav-cta">Дохил</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main>
