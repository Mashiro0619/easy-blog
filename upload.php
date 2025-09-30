<?php
declare(strict_types=1);
/**
 * 图片上传端点
 * - 需登录
 * - MIME 检测：finfo -> mime_content_type -> getimagesize -> 扩展名兜底
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['uid'])) {
  echo json_encode(['ok'=>false,'msg'=>'未登录']); exit;
}

$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) {
  echo json_encode(['ok'=>false,'msg'=>'配置缺失，请先运行 install.php']); exit;
}
$config = require $configFile;
$conf = $config['upload'] ?? null;
if (!$conf) {
  echo json_encode(['ok'=>false,'msg'=>'上传配置缺失']); exit;
}

function ext_from_name(string $name): string {
  $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
  return preg_replace('/[^a-z0-9]/','',$ext);
}

try{
  if (empty($_FILES['file'])) throw new RuntimeException('未收到文件');
  $f = $_FILES['file'];
  if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('上传错误：'.$f['error']);
  if ($f['size'] > ($conf['maxBytes'] ?? (10*1024*1024))) throw new RuntimeException('文件过大');

  // 逐级判断 MIME
  $mime = null;
  if (function_exists('finfo_open')) {
    $fi = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($fi, $f['tmp_name']) ?: null;
    if ($fi) finfo_close($fi);
  }
  if (!$mime && function_exists('mime_content_type')) {
    $mime = @mime_content_type($f['tmp_name']) ?: null;
  }
  if (!$mime) {
    $info = @getimagesize($f['tmp_name']);
    if ($info && !empty($info['mime'])) $mime = $info['mime'];
  }
  if (!$mime) {
    // 最后兜底：根据扩展名推断
    $ext = ext_from_name($f['name']);
    $map = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp'];
    $mime = $map[$ext] ?? null;
  }
  if (!$mime) throw new RuntimeException('无法判断文件类型');

  $allowed = $conf['allowed'] ?? ['image/jpeg','image/png','image/gif','image/webp'];
  if (!in_array($mime, $allowed, true)) throw new RuntimeException('不支持的文件类型：'.$mime);

  $ext = match($mime){
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
    default => ext_from_name($f['name']) ?: 'bin'
  };

  $dir = rtrim($conf['dir'] ?? (__DIR__ . '/uploads'), '/');
  $base= rtrim($conf['base'] ?? 'uploads', '/');

  if (!is_dir($dir)) {
    if (!mkdir($dir, 0775, true) && !is_dir($dir)) throw new RuntimeException('无法创建上传目录');
  }

  $name = bin2hex(random_bytes(8)).'.'.$ext;
  $dest = $dir . '/' . $name;
  if (!move_uploaded_file($f['tmp_name'], $dest)) throw new RuntimeException('保存失败');

  $url = $base . '/' . $name;
  echo json_encode(['ok'=>true, 'url'=>$url]); exit;

} catch (Throwable $e) {
  echo json_encode(['ok'=>false, 'msg'=>$e->getMessage()]); exit;
}
