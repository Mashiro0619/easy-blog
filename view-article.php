<?php

declare(strict_types=1);
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>
<!doctype html>
<html lang="zh-CN">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>查看文章</title>
  <style>
    :root {
      --bg: #fff;
      --text: #000;
      --muted: #555;
      --card: #fafafa;
      --border: #e0e0e0;
    }

    [data-theme="dark"] {
      --bg: #111;
      --text: #eee;
      --muted: #aaa;
      --card: #1a1a1a;
      --border: #333;
    }

    * {
      box-sizing: border-box
    }

    body {
      margin: 0;
      background: var(--bg);
      color: var(--text);
      font: 16px/1.6 system-ui, sans-serif;
      -webkit-tap-highlight-color: transparent;
      overflow-x: hidden;
    }

    header {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      padding: 12px 16px;
      border-bottom: 1px solid var(--border);
      background: var(--bg);
      z-index: 5
    }

    .back {

      display: inline-flex;
      align-items: flex-start;
      justify-content: center;
      width: 40px;
      padding: 6px;
      /* 触控目标大小，可改 44px */
      height: 40px;
      border: none;
      border-radius: 8px;
      background: transparent;
      color: var(--text);
      cursor: pointer;
      transition: transform .08s ease, background .12s ease;
      /* ← 调整按钮与标题第一行对齐 */
      -webkit-tap-highlight-color: transparent;
    }

    .back svg {
      width: 20px;
      height: 20px;
      display: block;
    }

    .back:hover {
      background: rgba(0, 0, 0, 0.04);
      transform: translateY(-1px);
    }

    .back:active {
      transform: translateY(0);
    }

    .back:focus-visible {
      outline: none;
      box-shadow: 0 0 0 3px rgba(100, 150, 255, 0.18);
      border-radius: 8px;
    }


    .back:active {
      transform: scale(.96)
    }

    .title {
      font-size: 1.1rem;
      font-weight: 700;
      margin: 0;
      flex: 1;
      white-space: normal;
      /* 允许换行 */
      word-break: break-word;
      /* 英文或长URL也能断开 */
      overflow-wrap: anywhere;
    }


    .article {
      padding: 30px;
      max-width: 780px;
      margin: 0 auto;
      animation: fade .25s ease
    }

    @keyframes fade {
      from {
        opacity: 0;
        transform: translateY(6px)
      }

      to {
        opacity: 1;
        transform: translateY(0)
      }
    }

    .meta {
      display: flex;
      align-items: center;
      gap: 8px;
      color: var(--muted);
      font-size: .9rem;
      flex-wrap: wrap
    }

    .rev-wrap {
      position: relative;
      display: inline-flex;
      align-items: center
    }

    .rev-btn {
      cursor: pointer;
      padding: 4px 8px;
      border-radius: 8px;
      background: var(--card);
      transition: transform .12s ease;
    }

    .rev-btn:active {
      transform: scale(.96)
    }

    /* 浮窗动画 */
    @keyframes popIn {
      0% {
        opacity: 0;
        transform: scale(0.8) translateY(-6px);
      }

      60% {
        opacity: 1;
        transform: scale(1.05) translateY(2px);
      }

      100% {
        transform: scale(1) translateY(0);
      }
    }

    @keyframes popOut {
      0% {
        opacity: 1;
        transform: scale(1) translateY(0);
      }

      100% {
        opacity: 0;
        transform: scale(0.8) translateY(-6px);
      }
    }

    /* 浮窗动画类 */
    .revisions.show {
      opacity: 1;
      transform: translateY(0);
      display: block;
      animation: popIn .28s cubic-bezier(.4, 0, .2, 1) forwards;
    }

    .revisions.hide {
      animation: popOut .18s ease forwards;
    }



    /* 浮窗（JS 定位，必要时改为“下方”模式避免遮挡点赞按钮） */
    .revisions {
      opacity: 0;
      transform: translateY(6px);
      transition: opacity .2s ease, transform .2s ease;
      display: none;
      position: fixed;
      background: var(--bg);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 10px;
      box-shadow: 0 10px 28px rgba(0, 0, 0, .18);
      min-width: 220px;
      max-width: 92vw;
      white-space: normal;
      word-break: break-word;
      z-index: 20;
    }


    .like {
      margin: 10px 0 20px;
    }

    .like button {
      padding: 8px 12px;
      border: none;
      border-radius: 8px;
      background: var(--text);
      color: var(--bg);
      cursor: pointer;
      transition: transform .12s ease, opacity .2s ease
    }

    .like button:active {
      transform: scale(.97)
    }

    .like button[disabled] {
      opacity: .55;
      cursor: not-allowed
    }

    .article-content img {
      max-width: 100%;
      height: auto
    }
  </style>


  <link rel="stylesheet" href="https://uicdn.toast.com/editor/latest/toastui-editor.min.css" />
  <link rel="stylesheet" href="https://uicdn.toast.com/editor/latest/toastui-editor-viewer.min.css" />

  <style>
    /* dark mode for Toast UI Viewer */

    /* 放大 Viewer 内所有文本 */
    .toastui-editor-contents {
      font-size: 1.125rem !important;
      /* 这里可以改成你想要的大小，例如 18px、20px */
      line-height: 1.8 !important;
      /* 行高略大，阅读更舒适 */
    }

    /* 正文自动换行 */
    .toastui-editor-contents {
      white-space: normal !important;
      word-break: break-word !important;
      overflow-wrap: anywhere !important;
    }

    /* 让很长的代码行也换行（不需要则删掉） */
    .toastui-editor-contents pre {
      white-space: pre-wrap !important;
      word-break: break-word;
      overflow-wrap: anywhere;
    }


    /* 可选：标题也单独调整 
    .toastui-editor-contents h1 {
      font-size: 2.2rem !important;
    }

    .toastui-editor-contents h2 {
      font-size: 2rem !important;
    }

    .toastui-editor-contents h3 {
      font-size: 1.8rem !important;
    }

    .toastui-editor-contents h4 {
      font-size: 1.6rem !important;
    }

    .toastui-editor-contents h5 {
      font-size: 1.4rem !important;
    }

    .toastui-editor-contents h6 {
      font-size: 1.2rem !important;
    }
*/


    [data-theme="dark"] .toastui-editor-contents,
    [data-theme="dark"] .toastui-editor-contents * {
      color: #eee !important;
      /* 默认文本颜色 */
    }

    /* 链接 */
    [data-theme="dark"] .toastui-editor-contents a {
      color: #8ab4f8 !important;
    }

    /* blockquote 引用 */
    [data-theme="dark"] .toastui-editor-contents blockquote {
      color: #ccc !important;
      border-left-color: #555 !important;
    }

    /* 表格边框和文字 */
    [data-theme="dark"] .toastui-editor-contents table,
    [data-theme="dark"] .toastui-editor-contents th,
    [data-theme="dark"] .toastui-editor-contents td {
      border-color: #444 !important;
    }

    /* 代码块背景和文字 */
    [data-theme="dark"] .toastui-editor-contents pre {
      background: #1a1a1a !important;
      color: #eee !important;
    }

    /* 夜间模式下 Inline Code */
    [data-theme="dark"] .toastui-editor-contents code {
      background: #333 !important;
      /* 深色背景 */
      color: #ffd966 !important;
      /* 黄色文本，更易辨认 */
      padding: 2px 4px;
      border-radius: 4px;
    }

    .feedback-card {
      display: inline-block;
      /* 让它像按钮一样 */
      width: 2rem;
      height: 2rem;
      line-height: 2rem;
      /* 图标垂直居中 */
      text-align: center;
      /* 图标水平居中 */
      border-radius: 8px;
      /* 圆角 */
      background-color: transparent;
      /* 背景透明 */
      color: #333;
      /* 图标颜色 */
      font-size: 18px;
      /* 图标大小 */
      text-decoration: none;
      /* 去掉链接下划线 */
      border: none;
      /* 去掉默认边框 */
      box-shadow: none;
      /* 去掉阴影 */
      transition: transform 0.2s ease;
    }

    .feedback-card:hover {
      background-color: rgba(255, 255, 255, 0.4);
      /* 悬停时稍微更亮 */
      transform: translateY(-2px);
      /* 轻微上浮 */
    }


  </style>


</head>

<body>
  <header>
    <button class="back" onclick="history.back()" aria-label="返回">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">
        <polyline points="15 18 9 12 15 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></polyline>
      </svg>
    </button>

    <h1 class="title" id="aTitle">加载中…</h1>
  </header>
  <main class="article">
    <div class="meta">
      <span>创建于 <span id="aCreated">—</span></span>
      <span id="aArticleId"></span> <!-- Placeholder for article ID -->
      <span class="rev-wrap">
        <span class="rev-btn" id="revToggle">⋯</span>
        <div class="revisions" id="revList"></div>
      </span>
      <button class="feedback-card" id="feedbackBtn">✉️</button>
        <!-- 反馈按钮 -->
    </div>

    <div class="like">
      <button id="likeBtn">👍 点赞 (0)</button>
    </div>

    <div id="aContent" class="article-content toastui-editor-contents"></div>

  </main>

  <script>
    (function() {
      const saved = localStorage.getItem('theme');
      if (saved) document.documentElement.setAttribute('data-theme', saved);
      else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
        document.documentElement.setAttribute('data-theme', 'dark');
      }
    })();

    const id = <?= json_encode($id) ?>;

    async function fetchJson(url, opt) {
      const res = await fetch(url, opt);
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const j = await res.json().catch(() => {
        throw new Error('JSON 解析失败');
      });
      if (!j.ok) throw new Error(j.msg || '接口错误');
      return j.data;
    }

    /** 定位浮窗：优先右侧；若窄屏或会遮住点赞按钮，则放到按钮下方（不遮挡） */
    function positionPopoverSmart(triggerEl, popEl) {
      popEl.style.display = 'block';
      const br = triggerEl.getBoundingClientRect();
      const pr = popEl.getBoundingClientRect();
      const like = document.getElementById('likeBtn').getBoundingClientRect();
      const vw = window.innerWidth,
        vh = window.innerHeight;

      const isMobile = vw < 768;

      // 默认右侧
      let left = br.right + 8;
      let top = br.top;

      const willOverflowRight = left + pr.width > vw - 8;
      const willCoverLike = !(pr.bottom < like.top || pr.top > like.bottom) &&
        (left < like.right && (left + pr.width) > like.left);

      // 移动端或右侧不合适 -> 下方展开
      if (isMobile || willOverflowRight || vw < 480 || willCoverLike) {
        left = Math.max(8, Math.min(vw - pr.width - 8, br.left));
        top = br.bottom + 8;
      }

      if (top + pr.height > vh - 8) top = vh - 8 - pr.height;
      if (top < 8) top = 8;

      popEl.style.left = Math.round(left) + 'px';
      popEl.style.top = Math.round(top) + 'px';
    }

    /* 反馈*/
    document.getElementById("feedbackBtn").addEventListener("click", () => {
      const email = "mashiro070619@gmail.com";
      const subject = encodeURIComponent("网站/文章反馈");
      const body = encodeURIComponent("请对于文章或网站的反馈，请在此填写：\n\n");
      window.location.href = `mailto:${email}?subject=${subject}&body=${body}`;
    });

    async function loadArticle() {
      try {
        if (!id) throw new Error('未指定文章');
        const d = await fetchJson(`api.php?action=get_article&id=${encodeURIComponent(id)}`);
        document.getElementById('aTitle').textContent = d.title;
        document.getElementById('aCreated').textContent = d.created_at.slice(0, 16);
        document.getElementById('aArticleId').textContent = `ID: ${d.id}`;
        document.getElementById('aContent').innerHTML = d.body || '';

        const likeBtn = document.getElementById('likeBtn');
        likeBtn.textContent = `👍 点赞 (${d.likes})`;
        likeBtn.disabled = !!d.likedByMe;
        likeBtn.onclick = async () => {
          try {
            const form = new FormData();
            form.append('action', 'like_article');
            form.append('id', String(id));
            const r = await fetch('api.php', {
              method: 'POST',
              body: form
            });
            const jj = await r.json();
            if (jj.ok) {
              likeBtn.textContent = `👍 点赞 (${jj.data.likes})`;
              if (jj.data.alreadyLiked) likeBtn.disabled = true;
            } else {
              alert(jj.msg || '点赞失败');
            }
          } catch (err) {
            alert('点赞失败');
          }
        };

        const rev = document.getElementById('revList');
        rev.innerHTML = (d.revisions && d.revisions.length) ?
          '<div style="font-weight:600;margin-bottom:6px">修改时间</div>' + d.revisions.map(t => '<div>' + t.slice(0, 16) + '</div>').join('') :
          '<div>暂无修改记录</div>';

        const toggle = document.getElementById('revToggle');

        function showPopover(el) {
          el.classList.add('show');
        }

        function hidePopover(el) {
          el.classList.remove('show');
        }

        toggle.onclick = (e) => {
          e.stopPropagation();
          if (rev.classList.contains('show')) hidePopover(rev);
          else {
            positionPopoverSmart(toggle, rev);
            showPopover(rev);
          }
        };


        // 全局点击空白关闭（非 once）：避免“第二次无法关闭”
        document.addEventListener('click', (ev) => {
          if (getComputedStyle(rev).display !== 'none') {
            const inPopover = rev.contains(ev.target);
            const inToggle = toggle.contains(ev.target);
            if (!inPopover && !inToggle) rev.style.display = 'none';
          }
        });

        window.addEventListener('resize', () => {
          if (getComputedStyle(rev).display !== 'none') positionPopoverSmart(toggle, rev);
        });


        window.addEventListener('resize', () => {
          if (getComputedStyle(rev).display !== 'none') positionPopoverSmart(toggle, rev);
        });

      } catch (e) {
        document.getElementById('aTitle').textContent = e.message || '加载失败';
      }
    }
    loadArticle();
  </script>
</body>

</html>