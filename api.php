<?php

declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/config.php';

function pdo(): PDO
{
    global $config;
    static $pdo = null;
    if ($pdo === null) {
        $db = $config['db'];
        $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset={$db['charset']}";
        $pdo = new PDO($dsn, $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }
    return $pdo;
}
function out(bool $ok, $data = null, string $msg = ''): never
{
    echo json_encode(['ok' => $ok, 'data' => $data, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}
function ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}
function need_login(): void
{
    if (empty($_SESSION['uid'])) out(false, null, '未登录');
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {

        /* ------ 公开接口 ------ */
        case 'list_articles':
            $st = pdo()->query("SELECT id,title,summary,created_at FROM articles ORDER BY id DESC");
            out(true, $st->fetchAll(PDO::FETCH_ASSOC));

        case 'get_article':
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) out(false, null, 'id 无效');
            $pdo = pdo();
            $st = $pdo->prepare("SELECT id,title,summary,body,likes,created_at,updated_at FROM articles WHERE id=?");
            $st->execute([$id]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) out(false, null, '文章不存在');
            $rv = $pdo->prepare("SELECT modified_at FROM article_revisions WHERE article_id=? ORDER BY modified_at DESC");
            $rv->execute([$id]);
            $row['revisions'] = $rv->fetchAll(PDO::FETCH_COLUMN);
            $hash = hash('sha256', ip());
            $ck = $pdo->prepare("SELECT 1 FROM article_likes WHERE article_id=? AND ip_hash=? LIMIT 1");
            $ck->execute([$id, $hash]);
            $row['likedByMe'] = (bool)$ck->fetchColumn();
            out(true, $row);

        case 'like_article':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) out(false, null, 'id 无效');
            $pdo = pdo();
            $hash = hash('sha256', ip());
            $now = (new DateTime('now'))->format('Y-m-d H:i:00');
            $ck = $pdo->prepare("SELECT 1 FROM article_likes WHERE article_id=? AND ip_hash=? LIMIT 1");
            $ck->execute([$id, $hash]);
            if ($ck->fetchColumn()) {
                $q = $pdo->prepare("SELECT likes FROM articles WHERE id=?");
                $q->execute([$id]);
                out(true, ['likes' => (int)$q->fetchColumn(), 'alreadyLiked' => true]);
            }
            $pdo->beginTransaction();
            try {
                $pdo->prepare("INSERT INTO article_likes (article_id,ip_hash,created_at) VALUES (?,?,?)")
                    ->execute([$id, $hash, $now]);
                $pdo->prepare("UPDATE articles SET likes=likes+1 WHERE id=?")->execute([$id]);
                $q = $pdo->prepare("SELECT likes FROM articles WHERE id=?");
                $q->execute([$id]);
                $likes = (int)$q->fetchColumn();
                $pdo->commit();
                out(true, ['likes' => $likes, 'alreadyLiked' => false]);
            } catch (Throwable $e) {
                $pdo->rollBack();
                $q = $pdo->prepare("SELECT likes FROM articles WHERE id=?");
                $q->execute([$id]);
                out(true, ['likes' => (int)$q->fetchColumn(), 'alreadyLiked' => true]);
            }

        case 'list_links':
            $st = pdo()->query("SELECT id,title,url,icon_path,sort_order FROM links ORDER BY sort_order ASC, id ASC");
            out(true, $st->fetchAll(PDO::FETCH_ASSOC));

            /* ------ 登录接口 ------ */
        case 'login':
            $username = trim($_POST['username'] ?? '');
            $password = (string)($_POST['password'] ?? '');
            if ($username === '' || $password === '') out(false, null, '参数缺失');
            $st = pdo()->prepare("SELECT id, pass_hash FROM users WHERE username=? LIMIT 1");
            $st->execute([$username]);
            $u = $st->fetch(PDO::FETCH_ASSOC);
            if (!$u || !password_verify($password, $u['pass_hash'])) {
                out(false, null, '账号或密码错误');
            }
            $_SESSION['uid'] = (int)$u['id'];
            $_SESSION['username'] = $username;
            out(true, ['username' => $username]);

        case 'logout':
            session_destroy();
            out(true);

            /* ------ 需登录接口（写操作） ------ */
        case 'save_article':
            need_login();
            $id = (int)($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $summary = trim($_POST['summary'] ?? '');
            $body = (string)($_POST['body'] ?? '');
            if ($title === '') out(false, null, '标题必填');
            $pdo = pdo();
            $now = (new DateTime('now'))->format('Y-m-d H:i:00');
            if ($id > 0) {
                $pdo->prepare("UPDATE articles SET title=?,summary=?,body=?,updated_at=? WHERE id=?")
                    ->execute([$title, $summary, $body, $now, $id]);
                $pdo->prepare("INSERT INTO article_revisions (article_id,modified_at) VALUES (?,?)")
                    ->execute([$id, $now]);
                out(true, ['id' => $id]);
            } else {
                $pdo->prepare("INSERT INTO articles (title,summary,body,likes,created_at,updated_at) VALUES (?,?,?,?,?,?)")
                    ->execute([$title, $summary, $body, 0, $now, $now]);
                $newId = (int)$pdo->lastInsertId();
                $pdo->prepare("INSERT INTO article_revisions (article_id,modified_at) VALUES (?,?)")
                    ->execute([$newId, $now]);
                out(true, ['id' => $newId]);
            }

        case 'delete_article':
            need_login(); // 确保用户已登录
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) out(false, null, '无效的文章ID');

            $pdo = pdo();

            // 执行删除操作
            $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                out(true, null, '文章删除成功');
            } else {
                out(false, null, '文章未找到或已被删除');
            }
            break;



        case 'add_link':
            need_login();
            $title = trim($_POST['title'] ?? '');
            $url = trim($_POST['url'] ?? '');
            $icon = trim($_POST['icon_path'] ?? 'icon.png');
            $sort = (int)($_POST['sort_order'] ?? 0);
            if ($title === '' || $url === '') out(false, null, '标题与URL必填');
            pdo()->prepare("INSERT INTO links (title,url,icon_path,sort_order) VALUES (?,?,?,?)")
                ->execute([$title, $url, $icon, $sort]);
            out(true, ['id' => (int)pdo()->lastInsertId()]);

        case 'update_link':
            need_login();
            $id = (int)($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $url = trim($_POST['url'] ?? '');
            $icon = trim($_POST['icon_path'] ?? 'icon.png');
            $sort = (int)($_POST['sort_order'] ?? 0);
            if ($id <= 0) out(false, null, 'id 无效');
            if ($title === '' || $url === '') out(false, null, '标题与URL必填');
            pdo()->prepare("UPDATE links SET title=?,url=?,icon_path=?,sort_order=? WHERE id=?")
                ->execute([$title, $url, $icon, $sort, $id]);
            out(true);

        case 'delete_link':
            need_login();
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) out(false, null, 'id 无效');
            pdo()->prepare("DELETE FROM links WHERE id=?")->execute([$id]);
            out(true);

        case 'upload_image':
            // 复用 upload.php 的逻辑
            require __DIR__ . '/upload.php';
            exit; // upload.php 已经输出 JSON，这里直接结束

            out(false, null, '未知 action');
    }
} catch (Throwable $e) {
    out(false, null, '服务异常：' . $e->getMessage());
}
