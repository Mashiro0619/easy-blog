<?php

declare(strict_types=1); ?>
<!doctype html>
<html lang="zh-CN">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>我的博客 · 首页</title>
  <style>
    :root {
      --bg: #fff;
      --text: #000;
      --muted: #555;
      --card: #fafafa;
      --border: #e0e0e0;
      --btn: #000;
    }

    [data-theme="dark"] {
      --bg: #111;
      --text: #eee;
      --muted: #aaa;
      --card: #1a1a1a;
      --border: #333;
      --btn: #fff;
    }

    * {
      box-sizing: border-box
    }

    html,
    body {
      height: 100%
    }

    body {
      margin: 0;
      background: var(--bg);
      color: var(--text);
      font: 16px/1.6 system-ui, sans-serif;
      display: flex;
      min-height: 100vh;
      -webkit-tap-highlight-color: transparent;
    }

    a {
      text-decoration: none;
      color: inherit;
      -webkit-tap-highlight-color: transparent;
    }

    /* 侧边栏（左贴边） */
    .sidebar {
      flex: 0 0 20%;
      display: flex;
      flex-direction: column;
      padding: 16px;
      border-right: 2px solid var(--border);
      background: var(--bg);
      min-height: 100vh;
      position: relative;
      overflow: hidden;
    }

    .topbar {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 10px
    }

    .menu-btn {
      background: transparent;
      border: none;
      cursor: pointer;
      font-size: 1.2rem;
      color: var(--text);
      padding: 8px 10px;
      border-radius: 10px;
      transition: background .25s ease, transform .12s ease, box-shadow .25s ease
    }

    .menu-btn:hover {
      background: var(--card);
      box-shadow: 0 8px 20px rgba(0, 0, 0, .12)
    }

    .menu-btn:active {
      transform: scale(.96)
    }

    .search {
      flex: 1;
      border: 1px solid var(--border);
      background: var(--bg);
      padding: 10px;
      border-radius: 10px;
      color: var(--text);
      transition: box-shadow .25s ease
    }

    .search:focus {
      outline: none;
      box-shadow: 0 0 0 3px rgba(0, 0, 0, .06)
    }


    .feedback-card:hover {
      background-color: rgba(255, 255, 255, 0.4);
      /* 悬停时稍微更亮 */
      transform: translateY(-2px);
      /* 轻微上浮 */
    }

    /* 面板容器：绝对铺满侧栏，做切换动画 */
    .panel-wrap {
      position: relative;
      flex: 1;
      overflow: hidden
    }

    .panel {
      position: absolute;
      inset: 0;
      overflow: auto;
      padding-right: 2px;
      opacity: 0;
      transform: translateX(12px);
      pointer-events: none;
      transition: opacity .28s ease, transform .28s ease;
    }

    /* 仅隐藏“文章列表”面板的滚动条（桌面端） */
    @media (min-width: 769px) {
      #panelPosts {
        padding-right: 0;
        /* 覆盖 .panel 中的 padding-right: 2px，避免右侧空白 */
        scrollbar-width: none;
        /* Firefox 隐藏滚动条 */
      }

      #panelPosts::-webkit-scrollbar {
        /* Chrome / Safari / Edge 隐藏滚动条 */
        display: none;
      }
    }


    .panel.active {
      opacity: 1;
      transform: translateX(0);
      pointer-events: auto
    }

    .section-title {
      margin: 6px 0 8px;
      color: var(--muted);
      font-size: .9rem
    }

    /* —— 文章列表 —— */
    #postList {
      position: relative
    }

    #postList .item {
      position: relative;
      padding: 12px 4px 12px 12px;
      cursor: pointer;
      overflow: hidden;
      user-select: none;
      -webkit-user-select: none;
    }

    /* 分割线 与侧栏同宽 */
    #postList .item+.item {
      border-top: 1px solid var(--border)
    }

    /* 左侧选中竖线 */
    #postList .item::before {
      content: "";
      position: absolute;
      left: 0;
      top: 8px;
      bottom: 8px;
      width: 3px;
      background: var(--text);
      transform: scaleY(0);
      transform-origin: top;
      transition: transform .22s ease;
      border-radius: 2px;
    }

    #postList .item.selected::before {
      transform: scaleY(1);
    }

    .ripple {
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
      opacity: .25;
      background: currentColor;
      transform: scale(0);
      animation: ripple .6s ease-out forwards;
    }

    @keyframes ripple {
      to {
        transform: scale(16);
        opacity: 0
      }
    }

    #postList h3 {
      margin: 0 0 6px;
      font-size: 1rem;
      white-space: normal;
      /* 允许换行 */
      word-break: break-word;
      /* 英文或长URL也能断开 */
      overflow-wrap: anywhere;
    }


    #postList .excerpt {
      font-size: .9rem;
      color: var(--muted);
      margin: 0 0 6px;
      word-break: break-word
    }

    #postList .date {
      font-size: .8rem;
      color: var(--muted)
    }

    /* 功能/链接 */
    .func-item,
    .link-item {
      padding: 16px;
      border-radius: 16px;
      cursor: pointer;
      transition: background .18s ease, box-shadow .18s ease, transform .12s ease
    }

    .func-item:hover,
    .link-item:hover {
      background: var(--card);
      box-shadow: 0 10px 24px rgba(0, 0, 0, .14)
    }

    .link-item {
      display: flex;
      align-items: center;
      gap: 8px
    }

    .link-item img {
      width: 16px;
      height: 16px
    }

    .spacer {
      height: 8px
    }

    .links-footer {
      position: absolute;
      left: 16px;
      right: 16px;
      bottom: 12px
    }

    .links-wrap {
      display: flex;
      flex-direction: column;
      gap: 10px
    }




    /* 主内容区 —— 调整：适度增大与侧栏间距 */
    .main {
      flex: 1;
      min-width: 0;
      padding: 24px 40px 48px 16px;
      /* 左侧从 0 ➜ 16px，略增间距 */
    }

    /* 引导：未选择文章时显示（桌面端） */
    .intro {
      display: flex;
      align-items: center;
      justify-content: center;
      height: calc(100vh - 120px);
      color: var(--muted);
      font-size: 1.1rem;
      text-align: center;
      opacity: .9;
    }

    .article {
      display: none;
      max-width: 880px;
      margin: 0 40px 0 24px;
      /* 左侧从 12px ➜ 24px，略增间距 */
      opacity: 0;
      transform: translateY(8px);
      transition: opacity .28s ease, transform .28s ease
    }

    .article.active {
      display: block;
      opacity: 1;
      transform: translateY(0)
    }

    .article h1 {
      font-size: 1.8rem;
      margin: 0 0 6px
    }

    .meta {
      display: flex;
      align-items: center;
      gap: 8px;
      color: var(--muted);
      font-size: .9rem
    }

    .rev-wrap {
      position: relative;
      display: inline-flex;
      align-items: center
    }

    .rev-btn {
      cursor: pointer;
      padding: 4px 8px;
      border-radius: 10px;
      background: var(--card);
      transition: transform .12s ease, box-shadow .2s ease
    }

    .rev-btn:hover {
      box-shadow: 0 8px 20px rgba(0, 0, 0, .12)
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
      display: block;
      animation: popIn .28s cubic-bezier(.4, 0, .2, 1) forwards;
    }

    .revisions.hide {
      animation: popOut .18s ease forwards;
    }




    /* 浮窗：由 JS 计算定位；这里仅做基础样式 */
    .revisions {
      display: none;
      position: absolute;
      padding: 10px;
      border: 1px solid var(--border);
      background: var(--bg);
      border-radius: 12px;
      box-shadow: 0 16px 36px rgba(0, 0, 0, .22);
      z-index: 10;
      min-width: 240px;
      max-width: 40vw;
      white-space: normal;
      word-break: break-word
    }

    .like {
      margin: 12px 0 20px
    }

    .like button {
      padding: 8px 12px;
      border: none;
      border-radius: 10px;
      background: var(--text);
      color: var(--bg);
      cursor: pointer;
      transition: transform .12s ease, opacity .2s ease, box-shadow .2s ease
    }

    .like button:hover {
      box-shadow: 0 12px 28px rgba(0, 0, 0, .18)
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

    /* 右侧正文区标题自动换行 */
    #aTitle {
      font-size: 1.6rem;
      /* 你想要的标题大小，可以保持默认不写 */
      margin: 0 0 20px;
      /* 底部间距 */
      white-space: normal;
      /* 允许换行 */
      word-break: break-word;
      /* 英文/长词断开 */
      overflow-wrap: anywhere;
    }


    /* 正文自动换行，长英文/长URL也能断开 */
    .article-content,
    .toastui-editor-contents {
      white-space: normal;
      word-break: break-word;
      overflow-wrap: anywhere;
    }

    /* 如文章里有很长的代码行：也进行换行
   ——如果你不想让代码换行，可以删掉这一段 */
    .toastui-editor-contents pre {
      white-space: pre-wrap !important;
      word-break: break-word;
      overflow-wrap: anywhere;
    }

    /* 彻底禁止页面出现横向滚动条 */
    body {
      overflow-x: hidden;
    }


    /* 手机端：主页仅显示侧边栏；隐藏底部灰线；确保面板区域高度 */
    @media (max-width:768px) {
      body {
        flex-direction: column
      }

      .sidebar {
        flex: unset;
        width: 100%;
        min-height: auto;
        border-right: none;
        border-bottom: none;
        /* 去除底部分割线 */
      }

      .panel-wrap {
        min-height: calc(100dvh - 120px);
      }

      .main {
        display: none
      }

      .revisions {
        max-width: 90vw;
        min-width: 140px;
        display: none;
        position: absolute;
        padding: 10px;
        border: 1px solid var (--border);
        background: var (--bg);
        border-radius: 12px;
        box-shadow: 0 16px 36px rgba(0, 0, 0, .22);
        z-index: 10;
        white-space: normal;
        word-break: break-word;
      }
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
/*
    .feedback-card:hover {
      transform: translateY(-2px);
      悬停时轻微上浮 
      可选：改变图标颜色增加交互感 
      color: #007bff;
    }*/
  </style>



</head>

<body>
  <aside class="sidebar">
    <div class="topbar">
      <button class="menu-btn" id="menuToggle" title="切换功能/文章">☰</button>
      <input id="search" class="search" type="search" placeholder="搜索标题…" aria-label="搜索" />
    </div>

    <div class="panel-wrap">
      <!-- 文章面板 -->
      <div id="panelPosts" class="panel active" aria-hidden="false">
        <div class="section-title">文章</div>
        <div id="postList">
          <div class="item">
            <h3>加载中…</h3>
            <p class="excerpt">请稍候</p>
          </div>
        </div>
      </div>

      <!-- 功能面板（显示功能时隐藏搜索框；跳转链接仅在此处出现） -->
      <div id="panelFunctions" class="panel" aria-hidden="true">
        <div class="section-title">功能</div>
        <div class="func-item" id="themeToggle">切换主题</div>
        <!-- 
        <a class="func-item" href="edit-article.php">进入管理（新建/编辑文章、管理链接）</a>
  -->
        <div class="spacer"></div>
        <div class="links-footer">
          <div class="section-title"></div>
          <!-- 跳转链接顶部文本 -->
          <div id="linksContainer" class="links-wrap">
            <div class="link-item">加载中…</div>
          </div>
        </div>
      </div>
    </div>
  </aside>

  <main class="main">
    <!-- 桌面端未选择文章时的引导 -->
    <div id="intro" class="intro">👋 欢迎阅读，请从左侧选择一篇文章</div>

    <article id="articleView" class="article">
      <h1 id="aTitle">请选择文章</h1>
      <div class="meta" id="aMeta" style="display:none;">
        <span>创建于 <span id="aCreated"></span></span>
        <span id="aArticleId"></span> <!-- Placeholder for article ID -->  
        <span class="rev-wrap">
          <span class="rev-btn" id="revToggle">⋯</span>
          <div class="revisions" id="revList"></div>
        </span>
        <button class="feedback-card" id="feedbackBtn">✉️</button>
        <!-- 反馈按钮 -->
      </div>




      <div class="like" id="likeWrap" style="display:none;">
        <button id="likeBtn">👍 点赞 (0)</button>
      </div>
      <div id="aContent" class="article-content toastui-editor-contents"></div>
    </article>
  </main>

  <script>
    /* ========== 主题 ========== */
    (function initTheme() {
      const saved = localStorage.getItem('theme');
      if (saved) document.documentElement.setAttribute('data-theme', saved);
      else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
        document.documentElement.setAttribute('data-theme', 'light');
      }
    })();

    /* 工具 */
    const isMobile = () => window.matchMedia('(max-width:768px)').matches;
    async function fetchJson(url, opt) {
      const res = await fetch(url, opt);
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const j = await res.json().catch(() => {
        throw new Error('JSON 解析失败');
      });
      if (!j.ok) throw new Error(j.msg || '接口错误');
      return j.data;
    }

    /* ========== 侧栏面板切换动画 ========== */
    const panelPosts = document.getElementById('panelPosts');
    const panelFunctions = document.getElementById('panelFunctions');
    const searchEl = document.getElementById('search');
    const menuBtn = document.getElementById('menuToggle');

    function showPanel(panelToShow) {
      const panels = [panelPosts, panelFunctions];
      panels.forEach(p => {
        const active = (p === panelToShow);
        p.classList.toggle('active', active);
        p.setAttribute('aria-hidden', active ? 'false' : 'true');
      });
      // 搜索框仅在文章面板显示
      searchEl.style.display = (panelToShow === panelPosts) ? '' : 'none';
    }

    menuBtn.addEventListener('click', () => {
      const showingPosts = panelPosts.classList.contains('active');
      showPanel(showingPosts ? panelFunctions : panelPosts);
    });

    /* ========== 主题切换 ========== */
    document.getElementById('themeToggle').addEventListener('click', () => {
      const cur = document.documentElement.getAttribute('data-theme') || 'light';
      const next = cur === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', next);
      localStorage.setItem('theme', next);
    });

    /* ========== 列表 & 波纹（手机端"按压波纹"） ========== */
    const postList = document.getElementById('postList');

    function createRippleAt(e, host, stronger) {
      const rect = host.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height) * (stronger ? 1.25 : 1);
      const cx = ('touches' in e && e.touches.length) ? e.touches[0].clientX : e.clientX;
      const cy = ('touches' in e && e.touches.length) ? e.touches[0].clientY : e.clientY;
      const x = cx - rect.left - size / 2;
      const y = cy - rect.top - size / 2;
      const span = document.createElement('span');
      span.className = 'ripple';
      span.style.width = span.style.height = size + 'px';
      span.style.left = x + 'px';
      span.style.top = y + 'px';
      host.appendChild(span);
      span.addEventListener('animationend', () => span.remove());
    }

    function bindItemInteractions(item) {
      let isTouchActive = false;
      item.addEventListener('click', (e) => {
        if (!isTouchActive) createRippleAt(e, item, false);
      });
      item.addEventListener('touchstart', (e) => {
        isTouchActive = true;
        createRippleAt(e, item, true);
      }, {
        passive: true
      });
      item.addEventListener('touchend', () => {
        setTimeout(() => isTouchActive = false, 80);
      }, {
        passive: true
      });
    }

    /* 加载文章列表 */
    async function loadArticles() {
      try {
        const data = await fetchJson('api.php?action=list_articles');
        data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        postList.innerHTML = '';
        data.forEach(row => {
          const item = document.createElement('div');
          item.className = 'item';
          item.dataset.id = row.id;
          item.dataset.title = row.title;
          item.innerHTML = `
        <h3>${row.title}</h3>
        <p class="excerpt">${row.summary ? row.summary : ''}</p>
        <div class="date">${(row.created_at||'').slice(0,16)}</div>
      `;
          bindItemInteractions(item);
          item.addEventListener('click', () => {
            document.querySelectorAll('#postList .item.selected').forEach(n => n.classList.remove('selected'));
            item.classList.add('selected');
            if (isMobile()) location.href = `view-article.php?id=${encodeURIComponent(row.id)}`;
            else renderArticle(row.id);
          });
          postList.appendChild(item);
        });
      } catch (e) {
        postList.innerHTML = '<div class="item"><h3>加载失败</h3><p class="excerpt">请检查 api.php 或数据库连接。</p></div>';
      }
    }

    /* 搜索 */
    searchEl.addEventListener('input', () => {
      const q = searchEl.value.toLowerCase();
      [...postList.children].forEach(item => {
        const hay = (item.dataset.title || '').toLowerCase();
        item.style.display = hay.includes(q) ? 'block' : 'none';
      });
    });

    /* 跳转链接（仅在功能面板里显示） */
    const linksBox = document.getElementById('linksContainer');
    async function loadLinks() {
      try {
        const data = await fetchJson('api.php?action=list_links');
        linksBox.innerHTML = '';
        data.forEach(link => {
          const a = document.createElement('a');
          a.href = link.url;
          a.target = '_blank';
          a.rel = 'noopener';
          a.className = 'link-item';
          a.innerHTML = `<img src="${link.icon_path || 'icon.png'}" alt="icon"><span>${link.title}</span>`;
          linksBox.appendChild(a);
        });
      } catch (e) {
        linksBox.innerHTML = '<div class="link-item">加载链接失败</div>';
      }
    }

/* 反馈按钮 */
    document.getElementById("feedbackBtn").addEventListener("click", () => {
      const email = "mashiro070619@gmail.com";
      const subject = encodeURIComponent("网站/文章反馈");
      const body = encodeURIComponent("对于文章或网站的反馈，请在此填写：\n\n");
      window.location.href = `mailto:${email}?subject=${subject}&body=${body}`;
    });

    /* 文章渲染 + 点赞 + 修改时间浮窗（JS 定位防溢出 & 可反复关闭） */
    async function renderArticle(id) {
      try {
        // 隐藏引导，展示文章容器
        const intro = document.getElementById('intro');
        if (intro) intro.style.display = 'none';

        const view = document.getElementById('articleView');
        view.classList.add('active');
        document.getElementById('aTitle').textContent = '加载中…';
        document.getElementById('aMeta').style.display = 'none';
        document.getElementById('likeWrap').style.display = 'none';
        document.getElementById('aContent').innerHTML = '';

        const d = await fetchJson(`api.php?action=get_article&id=${encodeURIComponent(id)}`);
        document.getElementById('aTitle').textContent = d.title;
        document.getElementById('aCreated').textContent = d.created_at.slice(0, 16);
        document.getElementById('aArticleId').textContent = `ID: ${d.id}`;
        document.getElementById('aContent').innerHTML = d.body || '';
        document.getElementById('aMeta').style.display = 'flex';
        document.getElementById('likeWrap').style.display = 'block';

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

        const revList = document.getElementById('revList');
        revList.innerHTML = (d.revisions && d.revisions.length) ?
          `<div style="font-weight:600;margin-bottom:6px">修改时间</div>` + d.revisions.map(t => `<div>${t.slice(0,16)}</div>`).join('') :
          '<div>暂无修改记录</div>';

        const toggle = document.getElementById('revToggle');

        function positionPopover() {
          revList.style.display = 'block';
          const pr = revList.getBoundingClientRect();
          const vw = window.innerWidth,
            vh = window.innerHeight;

          let left, top;

          // 判断是否为移动端
          if (window.matchMedia('(max-width:768px)').matches) {
            // 移动端逻辑：保持原有的智能定位
            const br = toggle.getBoundingClientRect();
            // 默认右侧
            left = br.right + 8;
            top = br.top;

            // 右侧出屏则左侧
            if (left + pr.width > vw - 8) left = Math.max(8, br.left - 8 - pr.width);

            // 垂直修正
            if (top + pr.height > vh - 8) top = vh - 8 - pr.height;
            if (top < 8) top = 8;

            revList.style.left = Math.round(left) + 'px';
            revList.style.top = Math.round(top) + 'px';
          } else {

            left = toggle.offsetWidth + 10;
            top = toggle.offsetHeight / 2; // 与按钮顶部对齐

            // 确保不会超出右边界
            if (left + pr.width > vw - 8) {
              left = vw - 8 - pr.width;
            }

            // 垂直位置调整：确保在视口内
            if (top + pr.height > vh - 8) {
              top = vh - 8 - pr.height;
            }
            if (top < 8) {
              top = 8;
            }

            revList.style.left = Math.round(left) + 'px';
            revList.style.top = Math.round(top) + 'px';

          }



        }

        toggle.onclick = (e) => {
          e.stopPropagation();
          if (revList.classList.contains('show')) {
            // 隐藏动画
            revList.classList.remove('show');
            revList.classList.add('hide');
            revList.addEventListener('animationend', function handler() {
              revList.style.display = 'none';
              revList.classList.remove('hide');
              revList.removeEventListener('animationend', handler);
            });
          } else {
            // 位置 + 显示
            positionPopover();
            revList.classList.add('show');
          }
        };

        // 点击空白关闭浮窗，也带动画
        document.addEventListener('click', (ev) => {
          if (revList.classList.contains('show')) {
            const inPopover = revList.contains(ev.target);
            const inToggle = toggle.contains(ev.target);
            if (!inPopover && !inToggle) {
              revList.classList.remove('show');
              revList.classList.add('hide');
              revList.addEventListener('animationend', function handler() {
                revList.style.display = 'none';
                revList.classList.remove('hide');
                revList.removeEventListener('animationend', handler);
              });
            }
          }
        });



      } catch (e) {
        alert('加载文章失败：' + e.message);
      }
    }

    loadArticles();
    loadLinks();
  </script>
</body>

</html>