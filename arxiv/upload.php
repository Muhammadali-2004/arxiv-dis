<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$page_title = 'Бор кардани кор';
$db = db();
$err = ''; $ok = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $title   = trim($_POST['title']??'');
    $author  = trim($_POST['author']??'');
    $sup     = trim($_POST['sup']??'');
    $fac_id  = intval($_POST['fac']??0);
    $typ_id  = intval($_POST['typ']??0);
    $year    = intval($_POST['year']??date('Y'));
    $group   = trim($_POST['group']??'');
    $kw      = trim($_POST['kw']??'');
    $desc    = trim($_POST['desc']??'');

    if (!$title||!$author||!$typ_id) { $err='Майдонҳои ҳатмиро пур кунед'; }
    elseif (empty($_FILES['file']['name'])) { $err='Файлро интихоб кунед'; }
    else {
        $f   = $_FILES['file'];
        $ext = strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
        if (!in_array($ext,['pdf','doc','docx'])) $err='Танҳо PDF, DOC, DOCX';
        elseif ($f['size']>MAX_SIZE) $err='Файл аз 20MB зиёд аст';
        elseif ($f['error']) $err='Хатои бор кардан';
        else {
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR,0755,true);
            $fname = uniqid('w_').time().'.'.$ext;
            if (move_uploaded_file($f['tmp_name'],UPLOAD_DIR.$fname)) {
                $u = current_user();
                $db->prepare("INSERT INTO scientific_works(title,author_id,author_name,supervisor,faculty_id,work_type_id,year,group_name,keywords,description,file_path,file_name,file_size,status)VALUES(:t,:aid,:a,:s,:f,:tp,:y,:g,:kw,:d,:fp,:fn,:fs,'pending')")
                   ->execute([':t'=>$title,':aid'=>$u['id'],':a'=>$author,':s'=>$sup,':f'=>$fac_id?:null,':tp'=>$typ_id,':y'=>$year,':g'=>$group,':kw'=>$kw,':d'=>$desc,':fp'=>$fname,':fn'=>$f['name'],':fs'=>$f['size']]);
                $ok='Кори илмӣ бо муваффақият бор шуд! Пас аз тасдиқи администратор нашр мешавад.';
            } else $err='Хатои нигоҳдории файл';
        }
    }
}
$faculties = $db->query("SELECT * FROM faculties ORDER BY name")->fetchAll();
$types     = $db->query("SELECT * FROM work_types ORDER BY name")->fetchAll();
$user      = current_user();
include 'includes/header.php';
?>

<div class="page-wrap" style="max-width:820px">
  <div class="pg-head">
    <div><h2>⬆️ Бор кардани Кори Илмӣ</h2><p>PDF ё Word файлро бо маълумот бор кунед</p></div>
  </div>

  <?php if($err): ?><div class="alert alert-err">⚠️ <?= e($err) ?></div><?php endif; ?>
  <?php if($ok): ?>
  <div class="alert alert-ok">
    ✅ <?= e($ok) ?>
    <a href="<?= url('works.php') ?>" style="margin-left:12px;font-weight:700">Архив →</a>
  </div>
  <?php endif; ?>

  <div class="card">
    <form method="POST" enctype="multipart/form-data" id="uploadForm">

      <!-- DROP ZONE -->
      <div id="dz" class="drop-zone" onclick="document.getElementById('fi').click()">
        <span class="dz-ico">📄</span>
        <h4>Файлро интихоб кунед ё ин ҷо кашед</h4>
        <p>PDF · DOC · DOCX — то 20 МБ</p>
      </div>
      <input type="file" id="fi" name="file" accept=".pdf,.doc,.docx" style="display:none">
      <div class="file-preview" id="fp">✅ <span id="fname"></span></div>

      <br>
      <div class="fgrid">
        <div class="fg fcol">
          <label>📌 Номи кор *</label>
          <input name="title" class="fc" value="<?= e($_POST['title']??'') ?>" placeholder="Унвони пурраи кор" required>
        </div>
        <div class="fg">
          <label>👤 Муаллиф *</label>
          <input name="author" class="fc" value="<?= e($_POST['author']??$user['name']) ?>" placeholder="Фамилия Ном" required>
        </div>
        <div class="fg">
          <label>👨‍🏫 Роҳбар</label>
          <input name="sup" class="fc" value="<?= e($_POST['sup']??'') ?>" placeholder="Проф. Раҳимов Б.И.">
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
          <label>📁 Намуди кор *</label>
          <select name="typ" class="fc" required>
            <option value="">Интихоб кунед</option>
            <?php foreach($types as $t): ?>
            <option value="<?= $t['id'] ?>" <?= ($_POST['typ']??'')==$t['id']?'selected':'' ?>><?= e($t['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label>📅 Сол *</label>
          <select name="year" class="fc" required>
            <?php for($y=date('Y');$y>=2015;$y--): ?>
            <option value="<?= $y ?>" <?= ($_POST['year']??date('Y'))==$y?'selected':'' ?>><?= $y ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="fg">
          <label>👥 Гурӯҳ</label>
          <input name="group" class="fc" value="<?= e($_POST['group']??'') ?>" placeholder="ТИ-401">
        </div>
        <div class="fg">
          <label>🏷️ Калидвожаҳо</label>
          <input name="kw" class="fc" value="<?= e($_POST['kw']??'') ?>" placeholder="PHP, PostgreSQL, архив">
        </div>
        <div class="fg fcol">
          <label>📝 Тавсиф (аннотатсия)</label>
          <textarea name="desc" class="fc" rows="4" placeholder="Мухтасари кор..."><?= e($_POST['desc']??'') ?></textarea>
        </div>
      </div>

      <div style="display:flex;gap:12px;margin-top:22px">
        <button type="submit" class="btn btn-blue btn-lg" style="flex:1;justify-content:center">⬆️ Бор кардан</button>
        <a href="<?= url('works.php') ?>" class="btn btn-ghost btn-lg">Бекор</a>
      </div>
    </form>
  </div>
</div>

<script>
const dz=document.getElementById('dz'),fi=document.getElementById('fi'),fp=document.getElementById('fp'),fn=document.getElementById('fname');
fi.addEventListener('change',show);
dz.addEventListener('dragover',e=>{e.preventDefault();dz.classList.add('over')});
dz.addEventListener('dragleave',()=>dz.classList.remove('over'));
dz.addEventListener('drop',e=>{e.preventDefault();dz.classList.remove('over');fi.files=e.dataTransfer.files;show()});
function show(){if(!fi.files[0])return;fn.textContent=fi.files[0].name+' ('+(fi.files[0].size/1048576).toFixed(1)+' MB)';fp.style.display='flex'}
</script>

<?php include 'includes/footer.php'; ?>
