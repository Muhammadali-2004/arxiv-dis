<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$page_title = 'Профили ман';
$db = db();
$uid = $_SESSION['uid'];
$err = ''; $ok = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name  = trim($_POST['name']??'');
    $group = trim($_POST['group']??'');
    $pass  = $_POST['pass']??'';
    $conf  = $_POST['conf']??'';
    if (!$name) $err='Ном ҳатмист';
    elseif ($pass&&strlen($pass)<6) $err='Парол аз 6 аломат кам';
    elseif ($pass&&$pass!==$conf) $err='Паролҳо мувофиқ нестанд';
    else {
        if ($pass) $db->prepare("UPDATE users SET full_name=:n,group_name=:g,password_hash=:h WHERE id=:i")->execute([':n'=>$name,':g'=>$group,':h'=>password_hash($pass,PASSWORD_DEFAULT),':i'=>$uid]);
        else       $db->prepare("UPDATE users SET full_name=:n,group_name=:g WHERE id=:i")->execute([':n'=>$name,':g'=>$group,':i'=>$uid]);
        $_SESSION['uname']=$name; $ok='Профил нав карда шуд';
    }
}
$st=$db->prepare("SELECT * FROM users WHERE id=:i"); $st->execute([':i'=>$uid]); $user=$st->fetch();
$mw=$db->prepare("SELECT sw.*,wt.name type_name FROM scientific_works sw LEFT JOIN work_types wt ON wt.id=sw.work_type_id WHERE sw.author_id=:i ORDER BY sw.uploaded_at DESC"); $mw->execute([':i'=>$uid]); $my_works=$mw->fetchAll();
include 'includes/header.php';
?>

<div class="page-wrap" style="max-width:960px">
  <div class="pg-head">
    <div>
      <h2>👤 Профили ман</h2>
      <p><span class="badge b-<?= e($user['role']) ?>"><?= e($user['role']) ?></span></p>
    </div>
    <a href="<?= url('upload.php') ?>" class="btn btn-blue">⬆️ Кори нав</a>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:22px">
    <div>
      <div class="card">
        <div class="card-head">✏️ Маълумоти шахсӣ</div>
        <?php if($err): ?><div class="alert alert-err">⚠️ <?= e($err) ?></div><?php endif; ?>
        <?php if($ok):  ?><div class="alert alert-ok">✅ <?= e($ok) ?></div><?php endif; ?>
        <form method="POST">
          <div style="display:flex;flex-direction:column;gap:14px">
            <div class="fg">
              <label>👤 Ном ва насаб</label>
              <input name="name" class="fc" value="<?= e($user['full_name']) ?>" required>
            </div>
            <div class="fg">
              <label>📧 Email</label>
              <input class="fc" value="<?= e($user['email']) ?>" disabled style="opacity:.6">
            </div>
            <div class="fg">
              <label>👥 Гурӯҳ</label>
              <input name="group" class="fc" value="<?= e($user['group_name']??'') ?>" placeholder="ТИ-401">
            </div>
            <div class="fg">
              <label>🔒 Парол нав (ихтиёрӣ)</label>
              <input type="password" name="pass" class="fc" placeholder="Холӣ монед">
            </div>
            <div class="fg">
              <label>🔒 Тасдиқи парол</label>
              <input type="password" name="conf" class="fc" placeholder="Такрор кунед">
            </div>
            <button class="btn btn-blue btn-block">💾 Нигоҳ доштан</button>
          </div>
        </form>
      </div>
    </div>

    <div>
      <div class="card" style="margin-bottom:16px">
        <div class="card-head">📊 Оморномаи ман</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div style="text-align:center;padding:18px;background:var(--c-bg);border-radius:var(--r3)">
            <b style="font-family:var(--font-h);font-size:28px;font-weight:900"><?= count($my_works) ?></b>
            <div style="font-size:12.5px;color:var(--c-muted);margin-top:4px">Корҳои ман</div>
          </div>
          <div style="text-align:center;padding:18px;background:var(--c-bg);border-radius:var(--r3)">
            <b style="font-family:var(--font-h);font-size:28px;font-weight:900"><?= count(array_filter($my_works,fn($w)=>$w['status']==='approved')) ?></b>
            <div style="font-size:12.5px;color:var(--c-muted);margin-top:4px">Тасдиқ шуда</div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head">📄 Корҳои ман</div>
        <?php if(empty($my_works)): ?>
        <div class="empty" style="padding:30px">
          <span class="ei" style="font-size:48px">📭</span>
          <h3 style="font-size:16px">Ҳанӯз кор вуҷуд надорад</h3>
          <a href="<?= url('upload.php') ?>" class="btn btn-blue" style="margin-top:12px">⬆️ Бор кардан</a>
        </div>
        <?php else: ?>
        <?php foreach($my_works as $w): ?>
        <div style="padding:11px 0;border-bottom:1px solid var(--c-border);display:flex;justify-content:space-between;align-items:center;gap:10px">
          <div style="min-width:0">
            <a href="<?= url('view.php?id='.$w['id']) ?>" style="font-size:14px;font-weight:700;color:var(--c-brand);text-decoration:none;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block">
              <?= e(mb_strimwidth($w['title'],0,42,'…')) ?>
            </a>
            <div style="font-size:12px;color:var(--c-muted);margin-top:2px">
              <?= e($w['type_name']??'') ?> · <?= $w['year'] ?> · 👁️ <?= $w['views'] ?>
            </div>
          </div>
          <span class="badge b-<?= $w['status']==='approved'?'ok':($w['status']==='pending'?'pend':'rej') ?>" style="flex-shrink:0"><?= $w['status'] ?></span>
        </div>
        <?php endforeach; ?>
        <div style="margin-top:14px">
          <a href="<?= url('upload.php') ?>" class="btn btn-blue btn-sm">⬆️ Кори нав бор кардан</a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
