<?php
declare(strict_types=1);
session_start();
if (!empty($_SESSION['uid'])) { header('Location: edit-article.php'); exit; }
?>
<!doctype html>
<html lang="zh-CN">
<head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>登录</title>
<style>
:root{--bg:#fff;--text:#000;--muted:#666;--border:#e0e0e0;--card:#fafafa}
[data-theme="dark"]{--bg:#111;--text:#eee;--muted:#aaa;--border:#333;--card:#1a1a1a}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font:16px/1.6 system-ui,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh}
.card{width:100%;max-width:420px;background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px;animation:float .35s ease}
@keyframes float{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
h1{margin:0 0 12px;font-size:1.2rem}
label{display:block;margin:.5rem 0 .25rem;font-weight:600}
input{width:100%;padding:10px;border:1px solid var(--border);border-radius:10px;background:#fff;color:#000}
[data-theme="dark"] input{background:#161616;color:#eee}
.btn{width:100%;margin-top:12px;padding:10px 16px;border:none;border-radius:12px;background:#000;color:#fff;cursor:pointer;transition:transform .12s ease, box-shadow .2s ease}
.btn:hover{box-shadow:0 12px 28px rgba(0,0,0,.18)}
.btn:active{transform:scale(.98)}
.msg{color:#b00;margin-top:8px;min-height:1.2em}
a{color:inherit}
</style>
</head>
<body>
<div class="card">
  <h1>管理员登录</h1>
  <form id="form">
    <label>用户名</label>
    <input name="username" value="admin" autocomplete="username">
    <label>密码</label>
    <input name="password" type="password" autocomplete="current-password">
    <button class="btn" type="submit">登录</button>
  </form>
  <div class="msg" id="msg"></div>
  <div style="margin-top:10px"><a href="index.php">返回首页</a></div>
</div>
<script>
(function(){const t=localStorage.getItem('theme'); if(t) document.documentElement.setAttribute('data-theme',t);})();
document.getElementById('form').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const fd = new FormData(e.target);
  fd.append('action','login');
  const res = await fetch('api.php',{method:'POST', body:fd});
  const j = await res.json();
  if(j.ok){ location.href = 'edit-article.php'; }
  else document.getElementById('msg').textContent = j.msg || '登录失败';
});
</script>
</body>
</html>
