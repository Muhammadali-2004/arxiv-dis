<?php
require_once __DIR__ . '/includes/auth.php';
$db = db();
$id = intval($_GET['id']??0);
if (!$id) { header('Location: '.url('works.php')); exit; }

$st = $db->prepare("SELECT sw.*,wt.name type_name,f.name fac_name FROM scientific_works sw LEFT JOIN work_types wt ON wt.id=sw.work_type_id LEFT JOIN faculties f ON f.id=sw.faculty_id WHERE sw.id=:id AND (sw.status='approved' OR :adm=1)");
$st->execute([':id'=>$id,':adm'=>is_teacher()?1:0]);
$w = $st->fetch();
if (!$w) { header('Location: '.url('works.php')); exit; }

if ($w['status']==='approved') {
    $db->prepare("UPDATE scientific_works SET views=views+1 WHERE id=:id")->execute([':id'=>$id]);
    $w['views']++;
}
$page_title = $w['title'];
include 'includes/header.php';
?>

<div class="page-wrap" style="max-width:960px">
  <!-- Breadcrumb -->
  <div style="font-size:13px;color:var(--c-muted);margin-bottom:22px;display:flex;align-items:center;gap:6px">
    <a href="<?= url('index.php') ?>" style="color:var(--c-brand)">Асосӣ</a> ›
    <a href="<?= url('works.php') ?>" style="color:var(--c-brand)">Архив</a> ›
    <span><?= e(mb_strimwidth($w['title'],0,50,'…')) ?></span>
  </div>

  <?php if($w['status']==='pending'): ?>
  <div class="alert alert-warn">⏳ Ин кор дар интизори тасдиқи администратор аст</div>
  <?php elseif($w['status']==='rejected'): ?>
  <div class="alert alert-err">❌ Ин кор рад карда шудааст</div>
  <?php endif; ?>

  <div class="card" style="padding:32px">
    <div style="display:grid;grid-template-columns:1fr auto;gap:24px;align-items:start">
      <div>
        <!-- Бейджҳо -->
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px">
          <span class="badge b-<?= $w['status']==='approved'?'ok':($w['status']==='pending'?'pend':'rej') ?>">
            <?= $w['status']==='approved'?'✅ Тасдиқ':($w['status']==='pending'?'⏳ Интизор':'❌ Рад') ?>
          </span>
          <?php if($w['type_name']): ?>
          <span class="badge b-student"><?= e($w['type_name']) ?></span>
          <?php endif; ?>
        </div>

        <h1 style="font-family:var(--font-h);font-size:22px;font-weight:900;color:var(--c-head);line-height:1.35;margin-bottom:20px">
          <?= e($w['title']) ?>
        </h1>

        <!-- Маълумот -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:14px">
          <div style="display:flex;flex-direction:column;gap:3px">
            <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--c-muted)">Муаллиф</span>
            <strong><?= e($w['author_name']) ?></strong>
          </div>
          <?php if($w['supervisor']): ?>
          <div style="display:flex;flex-direction:column;gap:3px">
            <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--c-muted)">Роҳбар</span>
            <strong><?= e($w['supervisor']) ?></strong>
          </div>
          <?php endif; ?>
          <?php if($w['fac_name']): ?>
          <div style="display:flex;flex-direction:column;gap:3px">
            <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--c-muted)">Факулта</span>
            <strong><?= e($w['fac_name']) ?></strong>
          </div>
          <?php endif; ?>
          <div style="display:flex;flex-direction:column;gap:3px">
            <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--c-muted)">Сол</span>
            <strong><?= $w['year'] ?></strong>
          </div>
          <?php if($w['group_name']): ?>
          <div style="display:flex;flex-direction:column;gap:3px">
            <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--c-muted)">Гурӯҳ</span>
            <strong><?= e($w['group_name']) ?></strong>
          </div>
          <?php endif; ?>
          <div style="display:flex;flex-direction:column;gap:3px">
            <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--c-muted)">Бор шуд</span>
            <strong><?= date('d.m.Y',strtotime($w['uploaded_at'])) ?></strong>
          </div>
        </div>

        <?php if($w['keywords']): ?>
        <div style="margin-top:18px">
          <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--c-muted);margin-bottom:8px">Калидвожаҳо</div>
          <div style="display:flex;flex-wrap:wrap;gap:6px">
            <?php foreach(explode(',',$w['keywords']) as $kw): ?>
            <a class="wi-tag" href="<?= url('works.php?q='.urlencode(trim($kw))) ?>"><?= e(trim($kw)) ?></a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Статистика -->
      <div style="display:flex;flex-direction:column;gap:12px;min-width:150px">
        <div style="text-align:center;background:var(--c-bg);border-radius:var(--r3);padding:18px">
          <div style="font-size:30px;font-weight:900;font-family:var(--font-h)"><?= number_format($w['views']) ?></div>
          <div style="font-size:12px;color:var(--c-muted);margin-top:4px">👁️ Дида шуд</div>
        </div>
        <div style="text-align:center;background:var(--c-bg);border-radius:var(--r3);padding:18px">
          <div style="font-size:30px;font-weight:900;font-family:var(--font-h)"><?= number_format($w['downloads']) ?></div>
          <div style="font-size:12px;color:var(--c-muted);margin-top:4px">⬇️ Зеркашӣ</div>
        </div>
        <?php if($w['file_size']): ?>
        <div style="text-align:center;background:var(--c-bg);border-radius:var(--r3);padding:14px">
          <div style="font-size:15px;font-weight:700"><?= filesize_human($w['file_size']) ?></div>
          <div style="font-size:12px;color:var(--c-muted);margin-top:2px">📁 Андоза</div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if($w['description']): ?>
    <div style="margin-top:26px;padding-top:22px;border-top:2px solid var(--c-border)">
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--c-muted);margin-bottom:10px">📝 Тавсиф / Аннотатсия</div>
      <p style="font-size:15px;line-height:1.8;color:var(--c-body)"><?= nl2br(e($w['description'])) ?></p>
    </div>
    <?php endif; ?>

    <!-- Тугмачаҳо -->
    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:26px;padding-top:22px;border-top:2px solid var(--c-border)">
      <?php if($w['file_path']&&$w['status']==='approved'): ?>
      <a href="<?= url('download.php?id='.$w['id']) ?>" class="btn btn-ok btn-lg">
        ⬇️ Зеркашии файл <?= $w['file_size']?'('.filesize_human($w['file_size']).')':'' ?>
      </a>
      <?php endif; ?>
      <?php if(is_admin()): ?>
        <?php if($w['status']==='pending'): ?>
        <a href="<?= url('admin.php?do=approve&id='.$w['id']) ?>" class="btn btn-ok" onclick="return confirm('Тасдиқ?')">✅ Тасдиқ</a>
        <a href="<?= url('admin.php?do=reject&id='.$w['id']) ?>" class="btn btn-red" onclick="return confirm('Рад?')">❌ Рад</a>
        <?php endif; ?>
        <a href="<?= url('admin.php?do=del&id='.$w['id']) ?>" class="btn btn-red" onclick="return confirm('Ҳаqiqatan ҳазф?')">🗑️ Ҳазф</a>
      <?php endif; ?>
      <a href="<?= url('works.php') ?>" class="btn btn-ghost">← Бозгашт</a>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
