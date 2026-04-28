<?php
require_once __DIR__ . '/includes/auth.php';
require_admin();
$page_title = 'Панели Идора';
$db = db();
$tab = $_GET['tab']??'dash';
$msg = $_GET['msg']??'';

// Actions
if (!empty($_GET['do'])&&!empty($_GET['id'])) {
    $id = intval($_GET['id']);
    $uid = $_SESSION['uid'];
    switch($_GET['do']) {
        case 'approve':
            $db->prepare("UPDATE scientific_works SET status='approved',approved_at=NOW(),approved_by=:u WHERE id=:i")->execute([':u'=>$uid,':i'=>$id]);
            header('Location: '.url('admin.php?tab=pend&msg=ok')); exit;
        case 'reject':
            $db->prepare("UPDATE scientific_works SET status='rejected' WHERE id=:i")->execute([':i'=>$id]);
            header('Location: '.url('admin.php?tab=pend&msg=rej')); exit;
        case 'del':
            $r = $db->prepare("SELECT file_path FROM scientific_works WHERE id=:i");
            $r->execute([':i'=>$id]); $row=$r->fetch();
            if ($row&&$row['file_path']) @unlink(UPLOAD_DIR.$row['file_path']);
            $db->prepare("DELETE FROM scientific_works WHERE id=:i")->execute([':i'=>$id]);
            header('Location: '.url('admin.php?msg=del')); exit;
        case 'block':   $db->prepare("UPDATE users SET is_active=false WHERE id=:i")->execute([':i'=>$id]); header('Location: '.url('admin.php?tab=users')); exit;
        case 'unblock': $db->prepare("UPDATE users SET is_active=true  WHERE id=:i")->execute([':i'=>$id]); header('Location: '.url('admin.php?tab=users')); exit;
    }
}

$stats = [
    'all'   => $db->query("SELECT COUNT(*) FROM scientific_works")->fetchColumn(),
    'pend'  => $db->query("SELECT COUNT(*) FROM scientific_works WHERE status='pending'")->fetchColumn(),
    'appr'  => $db->query("SELECT COUNT(*) FROM scientific_works WHERE status='approved'")->fetchColumn(),
    'rej'   => $db->query("SELECT COUNT(*) FROM scientific_works WHERE status='rejected'")->fetchColumn(),
    'users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'dl'    => $db->query("SELECT COALESCE(SUM(downloads),0) FROM scientific_works")->fetchColumn(),
];
include 'includes/header.php';
?>

<div class="page-wrap">
  <div class="pg-head">
    <div><h2>⚙️ Панели Идора</h2><p>Идоракунии система</p></div>
  </div>

  <?php if($msg==='ok'): ?><div class="alert alert-ok">✅ Кор тасдиқ карда шуд</div><?php endif; ?>
  <?php if($msg==='rej'): ?><div class="alert alert-warn">❌ Кор рад карда шуд</div><?php endif; ?>
  <?php if($msg==='del'): ?><div class="alert alert-err">🗑️ Кор ҳазф карда шуд</div><?php endif; ?>

  <div class="with-sidebar">
    <!-- Sidebar -->
    <div class="sidebar">
      <span class="sb-lbl">Менюи идора</span>
      <ul class="sb-menu">
        <li><a href="<?= url('admin.php?tab=dash') ?>" <?= $tab==='dash'?'class="on"':'' ?>>📊 Умумӣ</a></li>
        <li>
          <a href="<?= url('admin.php?tab=pend') ?>" <?= $tab==='pend'?'class="on"':'' ?>>
            ⏳ Интизор
            <?php if($stats['pend']>0): ?><span class="sb-cnt"><?= $stats['pend'] ?></span><?php endif; ?>
          </a>
        </li>
        <li><a href="<?= url('admin.php?tab=all') ?>" <?= $tab==='all'?'class="on"':'' ?>>📄 Ҳама корҳо</a></li>
        <li><a href="<?= url('admin.php?tab=users') ?>" <?= $tab==='users'?'class="on"':'' ?>>👥 Корбарон</a></li>
      </ul>
    </div>

    <!-- Мӯҳтаво -->
    <div>

      <?php if($tab==='dash'): ?>
      <!-- Дашборд -->
      <div class="stats-row">
        <div class="stat-box"><div class="sb-ic">📄</div><div class="sb-val"><b><?= $stats['all'] ?></b><span>Ҷамъи корҳо</span></div></div>
        <div class="stat-box wn"><div class="sb-ic">⏳</div><div class="sb-val"><b><?= $stats['pend'] ?></b><span>Интизор</span></div></div>
        <div class="stat-box ok"><div class="sb-ic">✅</div><div class="sb-val"><b><?= $stats['appr'] ?></b><span>Тасдиқ</span></div></div>
        <div class="stat-box er"><div class="sb-ic">❌</div><div class="sb-val"><b><?= $stats['rej'] ?></b><span>Рад</span></div></div>
        <div class="stat-box"><div class="sb-ic">👥</div><div class="sb-val"><b><?= $stats['users'] ?></b><span>Корбарон</span></div></div>
        <div class="stat-box ok"><div class="sb-ic">⬇️</div><div class="sb-val"><b><?= $stats['dl'] ?></b><span>Зеркашӣ</span></div></div>
      </div>
      <!-- Охирин корҳо -->
      <div class="card">
        <div class="card-head">🆕 Охирин корҳои бор шуда</div>
        <div class="tbl-wrap"><table class="dtbl">
          <thead><tr><th>Ном</th><th>Муаллиф</th><th>Намуд</th><th>Ҳолат</th><th>Сана</th><th>Амал</th></tr></thead>
          <tbody>
          <?php foreach($db->query("SELECT sw.*,wt.name type_name FROM scientific_works sw LEFT JOIN work_types wt ON wt.id=sw.work_type_id ORDER BY sw.uploaded_at DESC LIMIT 10")->fetchAll() as $r): ?>
          <tr>
            <td><?= e(mb_strimwidth($r['title'],0,38,'…')) ?></td>
            <td><?= e($r['author_name']) ?></td>
            <td><?= e($r['type_name']??'—') ?></td>
            <td><span class="badge b-<?= $r['status']==='approved'?'ok':($r['status']==='pending'?'pend':'rej') ?>"><?= $r['status'] ?></span></td>
            <td><?= date('d.m.Y',strtotime($r['uploaded_at'])) ?></td>
            <td style="display:flex;gap:5px">
              <a href="<?= url('view.php?id='.$r['id']) ?>" class="btn btn-blue btn-sm">👁️</a>
              <?php if($r['status']==='pending'): ?>
              <a href="<?= url('admin.php?do=approve&id='.$r['id']) ?>" class="btn btn-ok btn-sm" onclick="return confirm('Тасдиқ?')">✅</a>
              <a href="<?= url('admin.php?do=reject&id='.$r['id']) ?>"  class="btn btn-red btn-sm" onclick="return confirm('Рад?')">❌</a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table></div>
      </div>

      <?php elseif($tab==='pend'): ?>
      <div class="card">
        <div class="card-head">⏳ Дар интизори тасдиқ (<?= $stats['pend'] ?>)</div>
        <?php $rows=$db->query("SELECT sw.*,wt.name type_name,f.name fac_name FROM scientific_works sw LEFT JOIN work_types wt ON wt.id=sw.work_type_id LEFT JOIN faculties f ON f.id=sw.faculty_id WHERE sw.status='pending' ORDER BY sw.uploaded_at ASC")->fetchAll(); ?>
        <?php if(empty($rows)): ?>
        <div class="empty"><span class="ei">🎉</span><h3>Ҳама тафтиш шуд!</h3></div>
        <?php else: ?>
        <div class="works-stack">
          <?php foreach($rows as $r): ?>
          <div class="wi pending">
            <div class="wi-ico"><?= work_icon($r['type_name']??'') ?></div>
            <div class="wi-body">
              <h3><a href="<?= url('view.php?id='.$r['id']) ?>"><?= e($r['title']) ?></a></h3>
              <div class="wi-meta">
                <span>👤 <?= e($r['author_name']) ?></span>
                <span>📁 <?= e($r['type_name']??'—') ?></span>
                <span>📅 <?= $r['year'] ?></span>
                <span>🕐 <?= time_ago($r['uploaded_at']) ?></span>
              </div>
            </div>
            <div class="wi-right">
              <a href="<?= url('admin.php?do=approve&id='.$r['id']) ?>" class="btn btn-ok btn-sm" onclick="return confirm('Тасдиқ?')">✅ Тасдиқ</a>
              <a href="<?= url('admin.php?do=reject&id='.$r['id']) ?>"  class="btn btn-red btn-sm" onclick="return confirm('Рад?')">❌ Рад</a>
              <a href="<?= url('view.php?id='.$r['id']) ?>" class="btn btn-ghost btn-sm">👁️</a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <?php elseif($tab==='all'): ?>
      <div class="card">
        <div class="card-head">📄 Ҳамаи корҳо (<?= $stats['all'] ?>)</div>
        <div class="tbl-wrap"><table class="dtbl">
          <thead><tr><th>#</th><th>Ном</th><th>Муаллиф</th><th>Намуд</th><th>Сол</th><th>Ҳолат</th><th>Амал</th></tr></thead>
          <tbody>
          <?php foreach($db->query("SELECT sw.*,wt.name type_name FROM scientific_works sw LEFT JOIN work_types wt ON wt.id=sw.work_type_id ORDER BY sw.uploaded_at DESC LIMIT 50")->fetchAll() as $r): ?>
          <tr>
            <td style="color:var(--c-muted)"><?= $r['id'] ?></td>
            <td><?= e(mb_strimwidth($r['title'],0,36,'…')) ?></td>
            <td><?= e($r['author_name']) ?></td>
            <td><?= e($r['type_name']??'—') ?></td>
            <td><?= $r['year'] ?></td>
            <td><span class="badge b-<?= $r['status']==='approved'?'ok':($r['status']==='pending'?'pend':'rej') ?>"><?= $r['status'] ?></span></td>
            <td style="display:flex;gap:5px;white-space:nowrap">
              <a href="<?= url('view.php?id='.$r['id']) ?>" class="btn btn-blue btn-sm">👁️</a>
              <a href="<?= url('admin.php?do=del&id='.$r['id']) ?>" class="btn btn-red btn-sm" onclick="return confirm('Ҳазф?')">🗑️</a>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table></div>
      </div>

      <?php elseif($tab==='users'): ?>
      <div class="card">
        <div class="card-head">👥 Корбарон (<?= $stats['users'] ?>)</div>
        <div class="tbl-wrap"><table class="dtbl">
          <thead><tr><th>Ном</th><th>Email</th><th>Нақш</th><th>Гурӯҳ</th><th>Ҳолат</th><th>Сана</th><th>Амал</th></tr></thead>
          <tbody>
          <?php foreach($db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll() as $u): ?>
          <tr>
            <td><?= e($u['full_name']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><span class="badge b-<?= $u['role'] ?>"><?= $u['role'] ?></span></td>
            <td><?= e($u['group_name']??'—') ?></td>
            <td><?= $u['is_active']?'<span class="badge b-ok">✓ Фаъол</span>':'<span class="badge b-rej">✗ Блок</span>' ?></td>
            <td><?= date('d.m.Y',strtotime($u['created_at'])) ?></td>
            <td>
              <?php if($u['id']!==$_SESSION['uid']): ?>
              <?php if($u['is_active']): ?>
              <a href="<?= url('admin.php?do=block&id='.$u['id'].'&tab=users') ?>" class="btn btn-red btn-sm" onclick="return confirm('Блок?')">🔒</a>
              <?php else: ?>
              <a href="<?= url('admin.php?do=unblock&id='.$u['id'].'&tab=users') ?>" class="btn btn-ok btn-sm">🔓</a>
              <?php endif; ?>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table></div>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
