<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Архиви Корҳо';
$db = db();

$q       = trim($_GET['q']     ?? '');
$fac     = intval($_GET['fac'] ?? 0);
$typ     = intval($_GET['typ'] ?? 0);
$yr      = intval($_GET['yr']  ?? 0);
$sort    = in_array($_GET['s']??'',['new','old','views','dl']) ? $_GET['s'] : 'new';
$page    = max(1, intval($_GET['p']??1));
$per     = 10;

$where  = ["sw.status='approved'"];
$params = [];

if ($q) {
    $where[] = "(LOWER(sw.title) LIKE LOWER(:q) OR LOWER(sw.author_name) LIKE LOWER(:q2) OR LOWER(sw.keywords) LIKE LOWER(:q3))";
    $params += [':q'=>"%$q%", ':q2'=>"%$q%", ':q3'=>"%$q%"];
}
if ($fac) { $where[]=  "sw.faculty_id=:fac";  $params[':fac']=$fac; }
if ($typ) { $where[] = "sw.work_type_id=:typ"; $params[':typ']=$typ; }
if ($yr)  { $where[] = "sw.year=:yr";          $params[':yr']=$yr;  }
$wsql = implode(' AND ', $where);
$order = match($sort){ 'old'=>'sw.uploaded_at ASC','views'=>'sw.views DESC','dl'=>'sw.downloads DESC', default=>'sw.uploaded_at DESC' };

$cnt_stmt = $db->prepare("SELECT COUNT(*) FROM scientific_works sw WHERE $wsql");
$cnt_stmt->execute($params);
$total     = (int)$cnt_stmt->fetchColumn();
$pages     = max(1, ceil($total/$per));
$page      = min($page, $pages);
$offset    = ($page-1)*$per;

$stmt = $db->prepare("SELECT sw.*,wt.name type_name,f.name fac_name FROM scientific_works sw LEFT JOIN work_types wt ON wt.id=sw.work_type_id LEFT JOIN faculties f ON f.id=sw.faculty_id WHERE $wsql ORDER BY $order LIMIT :lim OFFSET :off");
foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
$stmt->bindValue(':lim',$per,PDO::PARAM_INT);
$stmt->bindValue(':off',$offset,PDO::PARAM_INT);
$stmt->execute();
$works = $stmt->fetchAll();

$faculties = $db->query("SELECT * FROM faculties ORDER BY name")->fetchAll();
$types     = $db->query("SELECT * FROM work_types ORDER BY name")->fetchAll();
$years     = $db->query("SELECT DISTINCT year FROM scientific_works WHERE status='approved' ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN);

function qurl(array $extra=[]): string {
    $p = array_merge($_GET, $extra);
    unset($p['p']);
    return url('works.php?'.http_build_query(array_filter($p,fn($v)=>$v!==''&&$v!=='0')));
}

include 'includes/header.php';
?>

<div class="page-wrap">
  <div class="pg-head">
    <div>
      <h2>📄 Архиви Корҳои Илмӣ</h2>
      <p><?= number_format($total) ?> кор ёфт шуд</p>
    </div>
    <?php if(logged_in()): ?>
    <a href="<?= url('upload.php') ?>" class="btn btn-blue">⬆️ Кор бор кардан</a>
    <?php endif; ?>
  </div>

  <!-- SEARCH -->
  <div class="search-panel">
    <form method="GET" action="<?= url('works.php') ?>">
      <div class="search-top">
        <div class="fg" style="flex:1">
          <label>🔍 Ҷустуҷӯ</label>
          <input name="q" class="fc" value="<?= e($q) ?>" placeholder="Номи кор, муаллиф, калидвожа...">
        </div>
        <button type="submit" class="btn btn-blue" style="margin-top:20px">Ёфтан</button>
        <?php if($q||$fac||$typ||$yr): ?>
        <a href="<?= url('works.php') ?>" class="btn btn-ghost" style="margin-top:20px">✕ Тоза</a>
        <?php endif; ?>
      </div>
      <div class="search-grid">
        <div class="fg">
          <label>🏫 Факулта</label>
          <select name="fac" class="fc">
            <option value="">Ҳама</option>
            <?php foreach($faculties as $f): ?>
            <option value="<?= $f['id'] ?>" <?= $fac==$f['id']?'selected':'' ?>><?= e($f['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label>📁 Намуд</label>
          <select name="typ" class="fc">
            <option value="">Ҳама</option>
            <?php foreach($types as $t): ?>
            <option value="<?= $t['id'] ?>" <?= $typ==$t['id']?'selected':'' ?>><?= e($t['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label>📅 Сол</label>
          <select name="yr" class="fc">
            <option value="">Ҳама</option>
            <?php foreach($years as $y): ?>
            <option value="<?= $y ?>" <?= $yr==$y?'selected':'' ?>><?= $y ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label>⬆️ Тартиб</label>
          <select name="s" class="fc">
            <option value="new"   <?= $sort==='new'  ?'selected':'' ?>>Навтарин</option>
            <option value="old"   <?= $sort==='old'  ?'selected':'' ?>>Кӯҳнатарин</option>
            <option value="views" <?= $sort==='views'?'selected':'' ?>>Зиёд дида шуда</option>
            <option value="dl"    <?= $sort==='dl'   ?'selected':'' ?>>Зиёд зеркашӣ</option>
          </select>
        </div>
        <button type="submit" class="btn btn-blue" style="margin-top:20px">Татбиқ</button>
      </div>
    </form>
  </div>

  <!-- RESULTS -->
  <?php if(empty($works)): ?>
  <div class="empty">
    <span class="ei">🔍</span>
    <h3>Кор ёфт нашуд</h3>
    <p>Параметрҳои ҷустуҷӯро тағйир диҳед</p>
    <a href="<?= url('works.php') ?>" class="btn btn-blue" style="margin-top:20px">Ҳама корҳо</a>
  </div>
  <?php else: ?>
  <div class="works-stack">
    <?php foreach($works as $w): ?>
    <div class="wi approved fu">
      <div class="wi-ico"><?= work_icon($w['type_name']??'') ?></div>
      <div class="wi-body">
        <h3><a href="<?= url('view.php?id='.$w['id']) ?>"><?= e($w['title']) ?></a></h3>
        <div class="wi-meta">
          <span>👤 <?= e($w['author_name']) ?></span>
          <?php if($w['fac_name']): ?><span>🏫 <?= e($w['fac_name']) ?></span><?php endif; ?>
          <?php if($w['type_name']): ?><span>📁 <?= e($w['type_name']) ?></span><?php endif; ?>
          <span>📅 <?= $w['year'] ?></span>
          <span>👁️ <?= $w['views'] ?></span>
          <span>⬇️ <?= $w['downloads'] ?></span>
        </div>
        <?php if($w['keywords']): ?>
        <div class="wi-tags">
          <?php foreach(explode(',',$w['keywords']) as $kw): ?>
          <a class="wi-tag" href="<?= url('works.php?q='.urlencode(trim($kw))) ?>"><?= e(trim($kw)) ?></a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
      <div class="wi-right">
        <span class="badge b-ok">✓ Тасдиқ</span>
        <a href="<?= url('view.php?id='.$w['id']) ?>" class="btn btn-blue btn-sm">👁️ Дидан</a>
        <?php if($w['file_path']): ?>
        <a href="<?= url('download.php?id='.$w['id']) ?>" class="btn btn-ok btn-sm">⬇️ PDF</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- PAGER -->
  <?php if($pages>1): ?>
  <div class="pager">
    <?php if($page>1): ?><a href="<?= qurl(['p'=>$page-1]) ?>">‹</a><?php endif; ?>
    <?php for($i=max(1,$page-2);$i<=min($pages,$page+2);$i++): ?>
    <?php if($i==$page): ?><span class="cur"><?= $i ?></span>
    <?php else: ?><a href="<?= qurl(['p'=>$i]) ?>"><?= $i ?></a><?php endif; ?>
    <?php endfor; ?>
    <?php if($page<$pages): ?><a href="<?= qurl(['p'=>$page+1]) ?>">›</a><?php endif; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
