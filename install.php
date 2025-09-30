<?php

/**
 * 一键安装（PHP 8.1）
 * - 建库/表
 * - 生成 config.php
 * - 创建 /uploads 目录
 * - 初始化管理员账号（admin）
 */

declare(strict_types=1);

function h(string $s): string
{
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$step = $_POST['step'] ?? 'form';
$msg = '';

if ($step === 'install') {
  $dbHost = trim($_POST['db_host'] ?? '127.0.0.1');
  $dbPort = trim($_POST['db_port'] ?? '3306');
  $dbName = trim($_POST['db_name'] ?? '');
  $dbUser = trim($_POST['db_user'] ?? '');
  $dbPass = (string)($_POST['db_pass'] ?? '');
  $adminPass = (string)($_POST['admin_pass'] ?? '');
  $dropExisting = isset($_POST['drop_existing']);
  $seedDemo = isset($_POST['seed_demo']);

  if ($dbName === '' || $dbUser === '' || $adminPass === '') {
    $msg = '数据库名、用户名、管理员密码为必填。';
    $step = 'form';
  } else {
    try {
      $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      ]);
      $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
      $pdo->exec("USE `{$dbName}`");
      $pdo->exec("SET default_storage_engine=INNODB");

      if ($dropExisting) {
        $pdo->exec("DROP TABLE IF EXISTS article_likes");
        $pdo->exec("DROP TABLE IF EXISTS article_revisions");
        $pdo->exec("DROP TABLE IF EXISTS links");
        $pdo->exec("DROP TABLE IF EXISTS articles");
        $pdo->exec("DROP TABLE IF EXISTS users");
      }

      $pdo->exec("
                CREATE TABLE IF NOT EXISTS users (
                  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  username VARCHAR(64) NOT NULL UNIQUE,
                  pass_hash VARCHAR(255) NOT NULL,
                  created_at DATETIME NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

      $pdo->exec("
                CREATE TABLE IF NOT EXISTS articles (
                  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  title VARCHAR(255) NOT NULL,
                  summary TEXT NULL,
                  body MEDIUMTEXT NULL,
                  likes INT UNSIGNED NOT NULL DEFAULT 0,
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

      $pdo->exec("
                CREATE TABLE IF NOT EXISTS article_revisions (
                  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  article_id INT UNSIGNED NOT NULL,
                  modified_at DATETIME NOT NULL,
                  CONSTRAINT fk_revisions_article
                    FOREIGN KEY (article_id) REFERENCES articles(id)
                    ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

      $pdo->exec("
                CREATE TABLE IF NOT EXISTS links (
                  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  title VARCHAR(255) NOT NULL,
                  url VARCHAR(1024) NOT NULL,
                  icon_path VARCHAR(255) DEFAULT 'icon.png',
                  sort_order INT NOT NULL DEFAULT 0
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

      $pdo->exec("
                CREATE TABLE IF NOT EXISTS article_likes (
                  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  article_id INT UNSIGNED NOT NULL,
                  ip_hash CHAR(64) NOT NULL,
                  created_at DATETIME NOT NULL,
                  CONSTRAINT fk_likes_article
                    FOREIGN KEY (article_id) REFERENCES articles(id)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                  UNIQUE KEY uniq_article_ip (article_id, ip_hash)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

      // 管理员
      $now = (new DateTime('now'))->format('Y-m-d H:i:00');
      $hash = password_hash($adminPass, PASSWORD_DEFAULT);
      // 若已存在 admin 则更新密码；否则创建
      $st = $pdo->prepare("SELECT id FROM users WHERE username='admin' LIMIT 1");
      $st->execute();
      if ($st->fetchColumn()) {
        $pdo->prepare("UPDATE users SET pass_hash=?, created_at=? WHERE username='admin'")
          ->execute([$hash, $now]);
      } else {
        $pdo->prepare("INSERT INTO users (username, pass_hash, created_at) VALUES ('admin', ?, ?)")
          ->execute([$hash, $now]);
      }

      if ($seedDemo) {
        $pdo->exec("DELETE FROM articles");
        $pdo->exec("DELETE FROM article_revisions");
        $pdo->exec("DELETE FROM links");
        $now = (new DateTime('now'))->format('Y-m-d H:i:00');
        $pdo->exec("
                    INSERT INTO articles (title, summary, body, likes, created_at, updated_at) VALUES
                    ('欢迎使用我的博客', '快速验证示例', '<p>这是 <strong>示例</strong> 文章，包含 <em>斜体</em>、<a href=\"#\" target=\"_blank\" rel=\"noopener\">链接</a> 与图片：<br><img src=\"https://youke1.picui.cn/s1/2025/08/31/68b3404d74f18.jpg\" alt=\"demo\"></p>', 0, '{$now}', '{$now}'),
                    ('第二篇文章', '摘要文字', '<p>本网站由Mashiro编写，开源，Github: Mashiro520</p>', 0, '{$now}', '{$now}')
                ");
        $pdo->exec("INSERT INTO article_revisions (article_id, modified_at) VALUES (1,'{$now}'),(2,'{$now}')");
        $pdo->exec("
                    INSERT INTO links (title, url, icon_path, sort_order) VALUES
                    ('GitHub', 'https://github.com', 'icon.png', 10),
                    ('PHP 文档', 'https://www.php.net', 'icon.png', 20)
                ");
      }

      // 写 config.php
      $config = <<<PHP
<?php
declare(strict_types=1);
return [
  'db' => [
    'host' => '{$dbHost}',
    'port' => '{$dbPort}',
    'name' => '{$dbName}',
    'user' => '{$dbUser}',
    'pass' => '{$dbPass}',
    'charset' => 'utf8mb4',
  ],
  'upload' => [
    'dir' => __DIR__ . '/uploads',
    'base' => 'uploads',    // 访问路径前缀
    'maxBytes' => 5 * 1024 * 1024, // 5MB
    'allowed' => ['image/jpeg','image/png','image/gif','image/webp']
  ],
];
PHP;
      file_put_contents(__DIR__ . '/config.php', $config);

      // 创建 uploads 目录
      $upDir = __DIR__ . '/uploads';
      if (!is_dir($upDir)) mkdir($upDir, 0775, true);
      // 简易 .htaccess（可选）
      if (!file_exists($upDir . '/.htaccess')) {
        file_put_contents($upDir . '/.htaccess', "Options -Indexes\n");
      }

      $step = 'done';
    } catch (Throwable $e) {
      $msg = '安装失败：' . h($e->getMessage());
      $step = 'form';
    }
  }
}
?>
<!doctype html>
<html lang="zh-CN">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>安装 · 博客</title>
  <style>
    :root {
      --bg: #fff;
      --text: #000;
      --border: #e0e0e0;
      --card: #fafafa
    }

    body {
      margin: 0;
      background: var(--bg);
      color: var(--text);
      font: 16px/1.6 system-ui, sans-serif;
      padding: 24px
    }

    .wrap {
      max-width: 760px;
      margin: 0 auto
    }

    h1 {
      margin: 0 0 16px
    }

    .card {
      background: var(--card);
      padding: 16px;
      border: 1px solid var(--border);
      border-radius: 12px;
      animation: fade .28s ease
    }

    @keyframes fade {
      from {
        opacity: 0;
        transform: translateY(8px)
      }

      to {
        opacity: 1;
        transform: translateY(0)
      }
    }

    label {
      display: block;
      margin: .5rem 0 .25rem;
      font-weight: 600
    }

    input[type=text],
    input[type=password] {
      width: 100%;
      padding: 10px;
      border: 1px solid var(--border);
      border-radius: 10px;
      background: #fff
    }

    .row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px
    }

    .btn {
      margin-top: 12px;
      padding: 10px 16px;
      border: none;
      border-radius: 12px;
      background: #000;
      color: #fff;
      cursor: pointer;
      transition: transform .12s ease, box-shadow .2s ease
    }

    .btn:hover {
      box-shadow: 0 10px 28px rgba(0, 0, 0, .18)
    }

    .btn:active {
      transform: scale(.98)
    }

    .msg {
      color: #b00;
      margin-bottom: 10px
    }

    .ok {
      color: #090;
      margin-bottom: 10px
    }

    a {
      color: inherit
    }
  </style>
</head>

<body>
  <div class="wrap">
    <h1>博客安装</h1>
    <?php if ($step === 'form'): ?>
      <?php if ($msg): ?><div class="msg"><?= $msg ?></div><?php endif; ?>
      <form method="post" class="card">
        <input type="hidden" name="step" value="install">
        <label>数据库主机</label><input name="db_host" value="<?= h($_POST['db_host'] ?? '127.0.0.1') ?>">
        <div class="row">
          <div><label>端口</label><input name="db_port" value="<?= h($_POST['db_port'] ?? '3306') ?>"></div>
          <div><label>数据库名</label><input name="db_name" value="<?= h($_POST['db_name'] ?? '') ?>" required></div>
        </div>
        <div class="row">
          <div><label>用户名</label><input name="db_user" value="<?= h($_POST['db_user'] ?? '') ?>" required></div>
          <div><label>密码</label><input name="db_pass" type="password" value="<?= h($_POST['db_pass'] ?? '') ?>"></div>
        </div>
        <label>管理员密码（账号固定为 <b>admin</b>）</label>
        <input name="admin_pass" type="password" required>
        <label><input type="checkbox" name="drop_existing"> 删除已存在的表（重置安装）</label>
        <label><input type="checkbox" name="seed_demo"> 插入示例数据（可选）</label>
        <button class="btn" type="submit">开始安装</button>
      </form>
    <?php else: ?>
      <div class="ok">安装完成！已生成 <code>config.php</code>、管理员账号 <code>admin</code>，并创建 <code>/uploads</code>。</div>
      <div class="card">
        <ul>
          <li><a href="index.php">打开首页 index.php</a></li>
          <li><a href="login.php">前往登录（编辑权限）</a></li>
          <li><a href="edit-article.php">进入管理（需登录）</a></li>
        </ul>
      </div>
    <?php endif; ?>
  </div>
</body>

</html>