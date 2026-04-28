<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Саҳифаи асосӣ';
$db = db();

$total    = $db->query("SELECT COUNT(*) FROM scientific_works WHERE status='approved'")->fetchColumn()||0;
$this_yr  = $db->query("SELECT COUNT(*) FROM scientific_works WHERE status='approved' AND year=".date('Y'))->fetchColumn()||0;
$students = $db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn()||0;
$dloads   = $db->query("SELECT COALESCE(SUM(downloads),0) FROM scientific_works WHERE status='approved'")->fetchColumn()||0;

$recent = $db->query("
  SELECT sw.*, wt.name type_name, f.name fac_name
  FROM scientific_works sw
  LEFT JOIN work_types wt ON wt.id=sw.work_type_id
  LEFT JOIN faculties f ON f.id=sw.faculty_id
  WHERE sw.status='approved'
  ORDER BY sw.uploaded_at DESC LIMIT 5
")->fetchAll();

// Статистика по категориям
$by_type = $db->query("
  SELECT wt.name, COUNT(*) cnt
  FROM scientific_works sw
  JOIN work_types wt ON wt.id=sw.work_type_id
  WHERE sw.status='approved'
  GROUP BY wt.name ORDER BY cnt DESC
")->fetchAll();

include 'includes/header.php';
?>

<!-- HERO -->
<section class="hero">
  <div class="hero-wrap">
    <div class="hero-pill">🎓 ДИС ДДТТ · Системаи Рақамии Архив</div>
    <h1>Архиви Корҳои Илмии<br>Донишҷӯён</h1>
    <p>Нигоҳдорӣ, ҷустуҷӯ ва дастрасии осон ба корҳои илмӣ, рисолаҳо ва мақолаҳои донишҷӯён</p>
    <form action="<?= url('works.php') ?>" method="GET" class="hero-search">
      <input name="q" placeholder="Ҷустуҷӯ: ном, муаллиф, калидвожа..." autocomplete="off">
      <button type="submit" class="btn btn-gold">🔍 Ёфтан</button>
    </form>
  </div>
</section>

<div class="page-wrap">

  <!-- STATS -->
  <div class="stats-row fu">
    <div class="stat-box fu fu1">
      <div class="sb-ic">📄</div>
      <div class="sb-val"><b><?= number_format($total) ?></b><span>Ҷамъи корҳо</span></div>
    </div>
    <div class="stat-box ok fu fu2">
      <div class="sb-ic">🆕</div>
      <div class="sb-val"><b><?= number_format($this_yr) ?></b><span>Соли <?= date('Y') ?></span></div>
    </div>
    <div class="stat-box wn fu fu3">
      <div class="sb-ic">👨‍🎓</div>
      <div class="sb-val"><b><?= number_format($students) ?></b><span>Донишҷӯён</span></div>
    </div>
    <div class="stat-box er fu fu4">
      <div class="sb-ic">⬇️</div>
      <div class="sb-val"><b><?= number_format($dloads) ?></b><span>Зеркашиҳо</span></div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:2fr 1fr;gap:22px">

    <!-- Охирин корҳо -->
    <div>
      <div class="pg-head">
        <div><h2>📋 Охирин корҳо</h2><p>Ба наздикӣ илова шудаанд</p></div>
        <a href="<?= url('works.php') ?>" class="btn btn-outline btn-sm">Ҳама корҳо →</a>
      </div>

      <?php if(empty($recent)): ?>
      <div class="empty"><span class="ei">📭</span><h3>Ҳанӯз кор вуҷуд надорад</h3></div>
      <?php else: ?>
      <div class="works-stack">
        <?php foreach($recent as $w): ?>
        <div class="wi approved fu">
          <div class="wi-ico"><?= work_icon($w['type_name']??'') ?></div>
          <div class="wi-body">
            <h3><a href="<?= url('view.php?id='.$w['id']) ?>"><?= e($w['title']) ?></a></h3>
            <div class="wi-meta">
              <span>👤 <?= e($w['author_name']) ?></span>
              <?php if($w['fac_name']): ?><span>🏫 <?= e($w['fac_name']) ?></span><?php endif; ?>
              <span>📅 <?= $w['year'] ?></span>
              <span>👁️ <?= $w['views'] ?></span>
              <span>⬇️ <?= $w['downloads'] ?></span>
            </div>
          </div>
          <div class="wi-right">
            <span class="badge b-ok">✓ Тасдиқ</span>
            <a href="<?= url('view.php?id='.$w['id']) ?>" class="btn btn-blue btn-sm">Дидан</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Панели ёрдамчӣ -->
    <div>
      <!-- Оморномаи намудҳо -->
      <div class="card fu">
        <div class="card-head">📊 Аз рӯи намудҳо</div>
        <?php foreach($by_type as $bt): ?>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid var(--c-border)">
          <span style="font-size:14px"><?= e($bt['name']) ?></span>
          <strong style="font-size:15px;color:var(--c-brand)"><?= $bt['cnt'] ?></strong>
        </div>
        <?php endforeach; ?>
        <?php if(empty($by_type)): ?>
        <p style="color:var(--c-muted);font-size:13px;text-align:center;padding:16px 0">Маълумот вуҷуд надорад</p>
        <?php endif; ?>
      </div>

      <?php if(!logged_in()): ?>
      <!-- CTA -->
      <div class="card" style="background:linear-gradient(135deg,#1d4ed8,#2563eb);border:none;color:#fff">
        <div style="font-family:var(--font-h);font-size:18px;font-weight:800;margin-bottom:10px">🎓 Ба система дохил шавед</div>
        <p style="opacity:.85;font-size:14px;margin-bottom:18px">Барои бор кардан ва идораи корҳои илмӣ</p>
        <a href="<?= url('register.php') ?>" class="btn btn-gold btn-block" style="margin-bottom:8px">Қайд шудан</a>
        <a href="<?= url('login.php') ?>" class="btn btn-block" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25)">Дохил шудан</a>
      </div>
      <?php else: ?>
      <!-- Тугмачаи зуд -->
      <div class="card fu">
        <div class="card-head">⚡ Амалиётҳои зуд</div>
        <div style="display:flex;flex-direction:column;gap:10px">
          <a href="<?= url('upload.php') ?>" class="btn btn-blue btn-block">⬆️ Кори нав бор кардан</a>
          <a href="<?= url('works.php') ?>" class="btn btn-ghost btn-block">📄 Тамоми архив</a>
          <a href="<?= url('profile.php') ?>" class="btn btn-ghost btn-block">👤 Профили ман</a>
        </div>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
