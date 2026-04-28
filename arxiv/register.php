<?php
require_once __DIR__ . '/includes/auth.php';
if (logged_in()) { header('Location: '.url('index.php')); exit; }

$err = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name  = trim($_POST['name']??'');
    $email = trim($_POST['email']??'');
    $pass  = $_POST['pass']??'';
    $conf  = $_POST['conf']??'';
    $group = trim($_POST['group']??'');
    $fac   = intval($_POST['fac']??0);

    if (!$name||!$email||!$pass)      $err='Майдонҳои ҳатмиро пур кунед';
    elseif (!filter_var($email,FILTER_VALIDATE_EMAIL)) $err='Email нодуруст';
    elseif (strlen($pass)<6)          $err='Парол аз 6 аломат кам набошад';
    elseif ($pass!==$conf)            $err='Паролҳо мувофиқ нестанд';
    else {
        $db = db();
        if ($db->prepare("SELECT id FROM users WHERE email=:e")->execute([':e'=>$email]) &&
            $db->query("SELECT id FROM users WHERE email='".addslashes($email)."'")->fetchColumn()) {
            $err='Ин email аллакай қайд шудааст';
        } else {
            $fn = '';
            if ($fac) {
                $fs = $db->prepare("SELECT name FROM faculties WHERE id=:i");
                $fs->execute([':i'=>$fac]);
                $fn = $fs->fetchColumn()?:'';
            }
            $db->prepare("INSERT INTO users(full_name,email,password_hash,role,group_name,faculty) VALUES(:n,:e,:h,'student',:g,:f)")
               ->execute([':n'=>$name,':e'=>$email,':h'=>password_hash($pass,PASSWORD_DEFAULT),':g'=>$group,':f'=>$fn]);
            header('Location: '.url('login.php?reg=1')); exit;
        }
    }
}
$faculties = db()->query("SELECT * FROM faculties ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tg">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Қайд шудан — Архиви Корҳои Илмӣ</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<div class="auth-bg">
  <div class="auth-box" style="max-width:500px">
    <div class="auth-logo">
      <div class="auth-ico">📚</div>
      <h2>Қайд шудан</h2>
      <p>Системаи Иттилоотии ДИС ДДТТ</p>
    </div>

    <?php if($err): ?>
    <div class="alert alert-err">⚠️ <?= e($err) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="fgrid">
        <div class="fg fcol">
          <label>👤 Ном ва насаб *</label>
          <input type="text" name="name" class="fc" value="<?= e($_POST['name']??'') ?>" placeholder="Фамилия Ном Номи падар" required>
        </div>
        <div class="fg fcol">
          <label>📧 Email *</label>
          <input type="email" name="email" class="fc" value="<?= e($_POST['email']??'') ?>" placeholder="shuma@dis.tj" required>
        </div>
        <div class="fg">
          <label>🏫 Факулта</label>
          <select name="fac" class="fc">
            <option value="">Интихоб кунед</option>
            <?php foreach($faculties as $f): ?>
            <option value="<?= $f['id'] ?>" <?= ($_POST['fac']??'')==$f['id']?'selected':'' ?>><?= e($f['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label>👥 Гурӯҳ</label>
          <input type="text" name="group" class="fc" value="<?= e($_POST['group']??'') ?>" placeholder="ТИ-301">
        </div>
        <div class="fg">
          <label>🔒 Парол * (мин. 6)</label>
          <input type="password" name="pass" class="fc" placeholder="Паролатонро ворид кунед" required>
        </div>
        <div class="fg">
          <label>🔒 Тасдиқи парол *</label>
          <input type="password" name="conf" class="fc" placeholder="Такрор кунед" required>
        </div>
      </div>
      <button class="btn btn-blue btn-block btn-lg" style="margin-top:20px">✅ Қайд шудан</button>
    </form>

    <div style="text-align:center;margin-top:20px;font-size:14px;color:var(--c-muted)">
      Аллакай ҳисоб доред?
      <a href="<?= url('login.php') ?>" style="font-weight:700;color:var(--c-brand)">Дохил шавед</a>
    </div>
  </div>
</div>
</body></html>
