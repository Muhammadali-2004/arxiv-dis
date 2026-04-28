<?php
require_once __DIR__ . '/includes/auth.php';
if (logged_in()) { header('Location: '.url('index.php')); exit; }

$err = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email = trim($_POST['email']??'');
    $pass  = $_POST['pass']??'';
    if (!$email||!$pass) { $err='Ҳама майдонҳоро пур кунед'; }
    else {
        $st = db()->prepare("SELECT * FROM users WHERE email=:e AND is_active=true");
        $st->execute([':e'=>$email]);
        $user = $st->fetch();
        if ($user && password_verify($pass, $user['password_hash'])) {
            $_SESSION['uid']   = $user['id'];
            $_SESSION['uname'] = $user['full_name'];
            $_SESSION['role']  = $user['role'];
            $_SESSION['email'] = $user['email'];
            header('Location: '.url(($_GET['back']??'index.php'))); exit;
        }
        $err = 'Email ё парол нодуруст аст';
    }
}
?>
<!DOCTYPE html>
<html lang="tg">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Дохил шудан — Архиви Корҳои Илмӣ</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<div class="auth-bg">
  <div class="auth-box">
    <div class="auth-logo">
      <div class="auth-ico">📚</div>
      <h2>Дохил шудан</h2>
      <p>Системаи Иттилоотии ДИС ДДТТ</p>
    </div>

    <?php if($err): ?>
    <div class="alert alert-err">⚠️ <?= e($err) ?></div>
    <?php endif; ?>
    <?php if(isset($_GET['reg'])): ?>
    <div class="alert alert-ok">✅ Муваффақона қайд шудед! Дохил шавед.</div>
    <?php endif; ?>

    <form method="POST">
      <div class="fg" style="margin-bottom:14px">
        <label>📧 Email</label>
        <input type="email" name="email" class="fc" value="<?= e($_POST['email']??'') ?>" placeholder="shuma@dis.tj" required>
      </div>
      <div class="fg" style="margin-bottom:22px">
        <label>🔒 Парол</label>
        <input type="password" name="pass" class="fc" placeholder="Паролатонро ворид кунед" required>
      </div>
      <button class="btn btn-blue btn-block btn-lg">🚪 Дохил шудан</button>
    </form>

    <div style="text-align:center;margin-top:22px;font-size:14px;color:var(--c-muted)">
      Ҳисоб надоред?
      <a href="<?= url('register.php') ?>" style="font-weight:700;color:var(--c-brand)">Қайд шавед</a>
    </div>

    <div style="margin-top:20px;padding:14px;background:var(--c-bg);border-radius:var(--r2);font-size:12.5px;color:var(--c-muted)">
      <strong style="color:var(--c-sub)">🔑 Намунаи ворид шудан (тест):</strong><br>
      Admin: <code>admin@dis.tj</code> / <code>password</code>
    </div>

    <div style="text-align:center;margin-top:16px">
      <a href="<?= url('index.php') ?>" style="font-size:13px;color:var(--c-muted)">← Ба саҳифаи асосӣ</a>
    </div>
  </div>
</div>
</body></html>
