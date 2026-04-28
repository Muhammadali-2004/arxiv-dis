<?php
require_once __DIR__ . '/includes/auth.php';
$db = db();
$id = intval($_GET['id']??0);
if (!$id) { header('Location: '.url('works.php')); exit; }

$st = $db->prepare("SELECT * FROM scientific_works WHERE id=:i AND status='approved'");
$st->execute([':i'=>$id]); $w=$st->fetch();
if (!$w||!$w['file_path']) { header('Location: '.url('works.php')); exit; }

$path = UPLOAD_DIR.$w['file_path'];
if (!file_exists($path)) { header('Location: '.url('works.php')); exit; }

$db->prepare("UPDATE scientific_works SET downloads=downloads+1 WHERE id=:i")->execute([':i'=>$id]);
$u = current_user();
$db->prepare("INSERT INTO download_logs(work_id,user_id,ip_address)VALUES(:w,:u,:ip)")->execute([':w'=>$id,':u'=>$u['id']??null,':ip'=>$_SERVER['REMOTE_ADDR']]);

$mime = mime_content_type($path)?:'application/octet-stream';
$safe = preg_replace('/[^a-zA-Z0-9._-]/','_',$w['file_name']?:$w['file_path']);
header('Content-Type: '.$mime);
header('Content-Disposition: attachment; filename="'.$safe.'"');
header('Content-Length: '.filesize($path));
header('Cache-Control: no-cache');
readfile($path); exit;
