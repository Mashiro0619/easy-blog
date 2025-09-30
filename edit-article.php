<?php

declare(strict_types=1);
session_start();
if (empty($_SESSION['uid'])) {
  header('Location: login.php');
  exit;
}
?>
<!doctype html>
<html lang="zh-CN">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>管理 · 文章与链接</title>

  <!-- Toast UI Editor 样式 -->
  <link id="tuiCss" rel="stylesheet" href="https://uicdn.toast.com/editor/latest/toastui-editor.min.css">
  <noscript>
    <link rel="stylesheet" href="https://unpkg.com/@toast-ui/editor/dist/toastui-editor.min.css">
  </noscript>

  <style>
    :root {
      --bg: #fff;
      --card: #f8f8f8;
      --text: #0b0b0b;
      --muted: #7a7a7a;
      --accent: #111;
      --border: #e7e7e7;
      --radius: 12px;
      --editor-height: 480px;
      /* 默认高度 */
      --sidebar-width: 360px;
      /* 左侧链接栏宽度 */
      --gap: 24px;
    }

    [data-theme="dark"] {
      --bg: #0b0b0b;
      --card: #0f0f0f;
      --text: #f3f3f3;
      --muted: #9b9b9b;
      --accent: #fff;
      --border: #222;
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
      font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Helvetica Neue", Roboto, "PingFang SC", "Microsoft Yahei", sans-serif;
      background: var(--bg);
      color: var(--text);
      -webkit-font-smoothing: antialiased;
      padding: 28px 20px;
      display: flex;
      justify-content: flex-start;
      align-items: flex-start;
    }

    .wrap {
      width: 100%;
      max-width: none;
      display: grid;
      grid-template-columns: minmax(240px, var(--sidebar-width)) minmax(0, 1fr);
      gap: var(--gap);
      align-items: start;
    }

    @media (max-width:1080px) {
      .wrap {
        grid-template-columns: 1fr;
      }
    }

    header.appbar {
      grid-column: 1 / -1;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 6px;
    }

    .brand {
      display: flex;
      gap: 14px;
      align-items: center;
    }

    .logo {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      background: linear-gradient(135deg, #111, #333);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-weight: 700;
      font-size: 16px;
    }

    h1.title {
      font-size: 18px;
      margin: 0;
      letter-spacing: -0.2px;
    }

    .top-actions {
      display: flex;
      gap: 10px;
      align-items: center;
    }

    .btn {
      background: var(--accent);
      color: var(--bg);
      border: none;
      padding: 10px 14px;
      border-radius: 10px;
      cursor: pointer;
      font-weight: 600;
    }

    .btn.ghost {
      background: transparent;
      color: var(--muted);
      border: 1px solid var(--border);
    }

    .delbtn {
      background: red;
      color: white;
      border: none;
      padding: 10px 14px;
      border-radius: 10px;
      cursor: pointer;
      font-weight: 600;
    }


    .card {
      background: var(--card);
      border-radius: var(--radius);
      padding: 16px;
      border: 1px solid var(--border);
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
      width: 100%;
      min-width: 0;
    }

    /* 左侧 Links */
    .links-panel {
      display: flex;
      flex-direction: column;
      gap: 12px;
      padding: 14px;
    }

    .links-panel h3 {
      margin: 0 0 6px 0;
      font-size: 15px
    }

    .links-form {
      display: grid;
      gap: 8px;
    }

    .links-form input {
      width: 100%;
      padding: 8px 10px;
      border-radius: 8px;
      border: 1px solid var(--border);
      background: transparent;
      color: var(--text);
    }

    .links-actions {
      display: flex;
      gap: 8px;
      align-items: center;
    }

    table.links {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
      margin-top: 6px;
    }

    table.links th,
    table.links td {
      text-align: left;
      padding: 8px;
      border-bottom: 1px solid var(--border);
      vertical-align: middle;
    }

    table.links input {
      width: 100%;
      padding: 6px 8px;
      border-radius: 8px;
      border: 1px solid var(--border)
    }

    .links-row-btn {
      display: flex;
      gap: 8px;
      align-items: center;
    }

    /* 右侧 Article */
    .editor-panel {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .panel-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      margin-bottom: 6px;
    }

    .panel-header .meta {
      font-size: 13px;
      color: var(--muted)
    }

    label.small {
      display: block;
      margin-bottom: 6px;
      color: var(--muted);
      font-size: 13px;
      font-weight: 600;
    }

    input[type="text"],
    input[type="number"] {
      width: 100%;
      padding: 10px 12px;
      border-radius: 9px;
      border: 1px solid var(--border);
      background: transparent;
      color: var(--text);
      font-size: 14px;
    }

    .two-col {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }

    /* 编辑器区域：底部 resizer */
    .editor-wrap {
      border-radius: 10px;
      overflow: visible;
      border: 1px solid var(--border);
      background: var(--bg);
      position: relative;
      display: flex;
      flex-direction: column;
      min-width: 0;
    }

    #editor {
      height: var(--editor-height);
      min-height: 240px;
    }

    .resizer {
      height: 10px;
      cursor: ns-resize;
      display: flex;
      align-items: center;
      justify-content: center;
      user-select: none;
      border-top: 1px dashed var(--border);
      background: linear-gradient(180deg, rgba(0, 0, 0, 0.02), transparent);
    }

    .resizer .dot {
      width: 36px;
      height: 4px;
      border-radius: 4px;
      background: var(--muted);
      opacity: 0.6
    }

    .editor-controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
    }

    .actions {
      display: flex;
      gap: 8px;
      justify-content: flex-end;
      margin-top: 8px;
      align-items: center;
    }

    .muted {
      color: var(--muted)
    }

    footer.hint {
      margin-top: 12px;
      font-size: 13px;
      color: var(--muted)
    }

    /* 屏幕阅读器隐藏文本 */
    .sr-only {
      position: absolute !important;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
      border: 0;
    }

    /* =======================
   强制编辑器白底黑字 + 圆角 + 工具栏可见
   ====================== */
    .force-light {
      background: #fff !important;
      color: #000 !important;
      border-radius: 10px !important;
    }

    /* 编辑器整体（WYSIWYG 区 + Markdown 编辑区） */
    .force-light .tui-editor,
    .force-light .tui-editor-defaultUI,
    .force-light .tui-editor-contents,
    .force-light .tui-toolbar,
    .force-light .te-preview-container,
    .force-light .te-md-preview,
    .force-light .tui-editor-md-preview,
    .force-light .tui-editor-md-container,
    .force-light .CodeMirror,
    .force-light .CodeMirror-scroll,
    .force-light .CodeMirror pre,
    .force-light .cm-s-default,
    .force-light .cm-s-default .CodeMirror-gutters,
    .force-light .cm-s-default .CodeMirror-lines,
    .force-light .toastui-editor-contents pre,
    .force-light .toastui-editor-contents code,
    .force-light .toastui-editor-md-code pre,
    .force-light .toastui-editor-md-code code {
      background: #fff !important;
      color: #000 !important;
      border-color: #e7e7e7 !important;
      border-radius: 10px !important;
    }

    /* CodeMirror gutter 背景 */
    .force-light .CodeMirror-gutters {
      background: #fff !important;
      border-right: 1px solid #eee !important;
      border-radius: 10px !important;
    }

    /* 工具栏按钮和图标 */
    .force-light .tui-toolbar button,
    .force-light .tui-toolbar .tui-toolbar-icons,
    .force-light .tui-toolbar button svg,
    .force-light .tui-toolbar .tui-toolbar-icons svg {
      color: #111 !important;
      fill: #111 !important;
      stroke: #111 !important;
      opacity: 1 !important;
    }

    /* Markdown 内容内的代码和表格背景 */
    .force-light .tui-editor-contents pre,
    .force-light .tui-editor-contents code {
      background: #f6f6f6 !important;
      color: #111 !important;
      border-radius: 6px !important;
    }

    /* Markdown preview 代码块 */
    .force-light .toastui-editor-md-code pre,
    .force-light .toastui-editor-md-code code {
      background: #f6f6f6 !important;
      color: #111 !important;
      border-radius: 6px !important;
    }


    @media (max-width:1080px) {
      .links-panel {
        order: 2
      }

      .editor-panel {
        order: 1
      }
    }

    @media (max-width:560px) {
      #editor {
        height: 320px;
      }
    }


    /* ===== responsive ToastUI toolbar (wrap + swipe) =====*/

    @media (max-width: 700px) {

      .tui-toolbar,
      .tui-editor-toolbar,
      .toastui-editor-toolbar,
      .tui-editor-defaultUI .tui-toolbar {
        display: flex !important;
        flex-wrap: wrap !important;
        /* 优先换行（多行显示） */
        align-items: center !important;
        gap: 6px !important;
        overflow-x: auto !important;
        /* 作为后备，允许横向滑动 */
        -webkit-overflow-scrolling: touch !important;
        touch-action: pan-x !important;
        white-space: nowrap !important;
      }

      .tui-toolbar .tui-toolbar-group,
      .tui-toolbar .tui-toolbar-icons,
      .tui-editor-toolbar .tui-toolbar-group {
        flex: 0 0 auto !important;
        /* 不允许子项压缩 */
      }

      .tui-toolbar button,
      .tui-toolbar .tui-toolbar-icons button {
        flex: 0 0 auto !important;
        min-width: 34px !important;
        padding: 6px 8px !important;
        font-size: 13px !important;
      }

      .tui-toolbar::-webkit-scrollbar {
        display: none;
      }
    }

    
  </style>
</head>

<body>
  <div class="wrap" id="wrap">
    <header class="appbar" style="grid-column:1/-1">
      <div class="brand">
        <!--<div class="logo">Mashiro</div>-->
        <div>
          <h1 class="title">内容管理</h1>
          <div class="muted" style="font-size:13px">文章与跳转链接 — 管理后台</div>
        </div>
      </div>

      <div class="top-actions">
        <button class="btn ghost" onclick="location.href='logout.php'">退出</button>
        <button class="btn" id="themeToggle">切换主题</button>
      </div>
    </header>

    <!-- 左侧：跳转链接 -->
    <aside class="card links-panel" id="linksPanel" aria-labelledby="linksHeading">
      <h3 id="linksHeading">跳转链接管理</h3>

      <div class="links-form" role="form" aria-label="添加跳转链接表单">
        <label for="linkTitleInput" class="muted" style="font-size:13px">标题</label>
        <input id="linkTitleInput" name="linkTitle" type="text" placeholder="标题（例如：GitHub）" />

        <label for="linkUrlInput" class="muted" style="font-size:13px">URL</label>
        <input id="linkUrlInput" name="linkUrl" type="text" placeholder="https://example.com" />

        <div class="links-actions">
          <label for="linkIconInput" class="sr-only">图标路径</label>
          <input id="linkIconInput" name="linkIcon" type="text" placeholder="icon.png" style="flex:1" />

          <label for="linkSortInput" class="sr-only">排序</label>
          <input id="linkSortInput" name="linkSort" type="number" value="0" style="width:86px" />

          <button class="btn" id="addLinkBtn">添加</button>
        </div>
      </div>

      <div style="margin-top:8px;max-height:68vh;overflow:auto">
        <table class="links" id="linksTable" aria-describedby="linksHeading">
          <thead>
            <tr>
              <th style="width:44px">ID</th>
              <th>标题</th>
              <th>URL</th>
              <th style="width:120px">操作</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan="4" class="muted">加载中…</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!--<div class="muted" style="font-size:13px;margin-top:10px">注意：所有修改会通过 <code>api.php</code> 提交。</div> -->
    </aside>

    <!-- 右侧：文章 -->
    <main class="card editor-panel" id="articlePanel" aria-labelledby="articleHeading">
      <div class="panel-header">
        <div>
          <div style="font-weight:700">文章编辑</div>
          <div class="meta muted">写作 · 发布 · 管理</div>
        </div>
        <div class="panel-actions"></div>
      </div>

      <div class="two-col" role="form" aria-labelledby="articleHeading">
        <div>
          <label for="articleId" class="small">文章 ID（留空新建）</label>
          <input id="articleId" name="articleId" type="text" placeholder="例如：12">
        </div>
        <div>
          <label for="summary" class="small">摘要（可选）</label>
          <input id="summary" name="summary" type="text" placeholder="简短摘要">
        </div>
      </div>

      <div>
        <label for="title" class="small">标题</label>
        <input id="title" name="title" type="text" placeholder="请输入文章标题">
      </div>

      <div>
        <label class="small">正文（拖拽底部边缘改变高度）</label>
        <div class="editor-wrap" role="region" aria-label="文章正文编辑器">
          <!-- 注意：给 editor 容器加上 force-light，内部会强制白色样式，不会随页面主题改变 -->
          <div id="editor" class="force-light" aria-multiline="true"></div>
          <div class="resizer" id="editorResizer" title="拖动改变高度，双击重置">
            <div class="dot" aria-hidden="true"></div>
          </div>
        </div>
      </div>

      <div class="editor-controls">
        <div class="muted" style="font-size:13px">编辑器高度：<span id="heightValue">480px</span></div>
        <div style="display:flex;gap:8px;align-items:center">
          <button class="btn ghost" id="heightReset">重置高度</button>
          <!-- 新增：滚动联动切换 -->
          <button class="btn ghost" id="syncToggle" title="开启/关闭编辑器与预览的滚动联动">滚动联动：开</button>
        </div>
      </div>

      <div class="actions" role="group" aria-label="文章操作按钮">
        <button class="btn ghost" id="loadBtn">加载文章</button>
        <button class="btn" id="saveBtn">保存文章</button>
        <button class="delbtn" id="deleteBtn">删除文章</button>
      </div>

      <!--<footer class="hint muted">图片上传会发送到 <code>upload.php</code> 并插入文章；接口返回格式需为 JSON `{ok: true, url: "..."}` -->
      </footer>
    </main>
  </div>

  <!-- Toast UI 脚本 -->
  <script>
    (function ensureTuiAssets() {
      const css = document.getElementById('tuiCss');
      if (css) css.addEventListener('error', () => {
        const alt = document.createElement('link');
        alt.rel = 'stylesheet';
        alt.href = 'https://unpkg.com/@toast-ui/editor/dist/toastui-editor.min.css';
        document.head.appendChild(alt);
      });
    })();
  </script>
  <script src="https://uicdn.toast.com/editor/latest/toastui-editor-all.min.js" onerror="
    (function(){
      var s=document.createElement('script');
      s.src='https://unpkg.com/@toast-ui/editor/dist/toastui-editor-all.min.js';
      document.head.appendChild(s);
    })();
  "></script>

  <script>
    /* -----------------------
     工具函数
     ----------------------- */
    const $ = s => document.querySelector(s);
    const qa = s => document.querySelectorAll(s);
    async function fetchJson(url, opts) {
      const res = await fetch(url, opts);
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const j = await res.json().catch(() => {
        throw new Error('返回非 JSON')
      });
      if (!j.ok) throw new Error(j.msg || '接口错误');
      return j.data;
    }

    /* -----------------------
       主题切换（保留）
       ----------------------- */
    (function() {
      const stored = localStorage.getItem('theme');
      if (stored) document.documentElement.setAttribute('data-theme', stored);
      else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        document.documentElement.setAttribute('data-theme', 'dark');
      }
      $('#themeToggle').addEventListener('click', () => {
        const cur = document.documentElement.getAttribute('data-theme') || 'light';
        const next = cur === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
      });
    })();

    /* -----------------------
       Editor 初始化 + 底部 resizer + 滚动联动（Scroll Sync）
       ----------------------- */
    let editor = null;
    let scrollSyncEnabled = true; // 默认开
    let scrollHandlersAttached = false;
    let scrollSyncObserver = null;

    function initEditorIfNeeded() {
      if (window.toastui && toastui.Editor && !editor) {
        editor = new toastui.Editor({
          el: document.querySelector('#editor'),
          initialEditType: 'wysiwyg',
          previewStyle: 'vertical',
          height: getEditorHeightString(),
          usageStatistics: false,
          hooks: {
            addImageBlobHook: (blob, callback) => {
              const fd = new FormData();
              fd.append('file', blob);
              fetch('upload.php', {
                  method: 'POST',
                  body: fd
                })
                .then(r => r.json())
                .then(j => {
                  if (j && j.ok && j.url) callback(j.url, blob.name || 'image');
                  else alert('图片上传失败');
                })
                .catch(() => alert('图片上传失败'));
              return false;
            }
          },
          toolbarItems: [
            ['heading', 'bold', 'italic', 'strike'],
            ['hr', 'quote'],
            ['ul', 'ol', 'task', 'indent', 'outdent'],
            ['table', 'link', 'image'],
            ['code', 'codeblock']
          ]
        });

        // 立即尝试绑定滚动联动（如果处于 Markdown+Preview 模式需要监听 DOM 变化）
        observeEditorForSync();
      }
    }

    function getEditorHeight() {
      return parseInt(getComputedStyle(document.documentElement).getPropertyValue('--editor-height')) || 480;
    }

    function getEditorHeightString() {
      return getEditorHeight() + 'px';
    }

    function setEditorHeight(h) {
      if (!h || h < 200) h = 200;
      document.documentElement.style.setProperty('--editor-height', h + 'px');
      $('#heightValue').textContent = h + 'px';
      try {
        if (editor && typeof editor.setHeight === 'function') editor.setHeight(h + 'px');
      } catch (e) {}
      const ed = document.getElementById('editor');
      if (ed) ed.style.height = h + 'px';
    }

    // resizer 底部拖拽（和之前逻辑类似）
    (function attachBottomResizer() {
      let dragging = false;
      let startY = 0;
      let startH = 0;
      const resizer = document.getElementById('editorResizer');
      if (!resizer) return;

      function onStart(e) {
        e.preventDefault();
        dragging = true;
        startY = (e.touches ? e.touches[0].clientY : e.clientY);
        startH = getEditorHeight();
        document.body.style.cursor = 'ns-resize';
        document.body.style.userSelect = 'none';
      }

      function onMove(e) {
        if (!dragging) return;
        const curY = (e.touches ? e.touches[0].clientY : e.clientY);
        const dy = curY - startY;
        const nextH = Math.max(200, startH + dy);
        setEditorHeight(nextH);
      }

      function onEnd() {
        if (!dragging) return;
        dragging = false;
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
      }
      resizer.addEventListener('mousedown', onStart);
      resizer.addEventListener('touchstart', onStart, {
        passive: false
      });
      window.addEventListener('mousemove', onMove);
      window.addEventListener('touchmove', onMove, {
        passive: false
      });
      window.addEventListener('mouseup', onEnd);
      window.addEventListener('touchend', onEnd);
      // double-click resets
      resizer.addEventListener('dblclick', () => setEditorHeight(480));
      // reset button
      const resetBtn = document.getElementById('heightReset');
      if (resetBtn) resetBtn.addEventListener('click', () => setEditorHeight(480));
    })();

    /* -----------------------
       滚动联动实现（健壮版）
       - 会尝试定位 CodeMirror 的滚动容器与 Markdown preview 容器（兼容不同版本 class）
       - 支持双向同步并用 flag 防止循环
       - 使用 MutationObserver 监听 editor DOM 变化（用户切换编辑模式时重新绑定）
       ----------------------- */
    function locateScrollElements() {
      const container = document.getElementById('editor');
      if (!container) return {
        cm: null,
        preview: null
      };

      // CodeMirror / markdown 编辑区常见滚动容器选择器
      const cmCandidates = [
        '.CodeMirror-scroll', // CodeMirror v5
        '.cm-editor .cm-scroller', // CodeMirror v6 (some builds)
        '.toastui-editor-md-code .CodeMirror-scroll',
        '.toastui-editor-md-code .cm-editor .cm-scroller'
      ];
      let cmEl = null;
      for (const sel of cmCandidates) {
        cmEl = container.querySelector(sel);
        if (cmEl) break;
      }

      // preview 区常见选择器
      const previewCandidates = [
        '.te-md-preview', // newer examples
        '.tui-editor-md-preview',
        '.te-preview-container',
        '.tui-editor-contents' // fallback: WYSIWYG area / preview area
      ];
      let previewEl = null;
      for (const sel of previewCandidates) {
        previewEl = container.querySelector(sel);
        if (previewEl) break;
      }

      // If nothing found, still try to find general scrollable areas inside editor
      if (!cmEl) {
        cmEl = container.querySelector('textarea, pre, .ProseMirror, .CodeMirror') || null;
      }
      if (!previewEl) {
        // maybe the preview is a div with overflow
        const maybe = container.querySelector('div[contenteditable=false]') || container.querySelector('.preview') || null;
        previewEl = maybe;
      }

      return {
        cm: cmEl,
        preview: previewEl
      };
    }

    function attachScrollSyncHandlers() {
      if (scrollHandlersAttached) return;
      const {
        cm,
        preview
      } = locateScrollElements();
      if (!cm || !preview) return; // 需要两个元素才能工作

      let syncing = false;
      const clamp = v => Math.max(0, Math.min(1, v));

      function syncPreviewFromEditor() {
        if (!scrollSyncEnabled) return;
        if (syncing) return;
        try {
          syncing = true;
          const sTop = cm.scrollTop;
          const sMax = cm.scrollHeight - cm.clientHeight;
          const pct = sMax > 0 ? sTop / sMax : 0;
          const pMax = preview.scrollHeight - preview.clientHeight;
          preview.scrollTop = Math.round(pct * (pMax > 0 ? pMax : 0));
        } finally {
          setTimeout(() => syncing = false, 20);
        }
      }

      function syncEditorFromPreview() {
        if (!scrollSyncEnabled) return;
        if (syncing) return;
        try {
          syncing = true;
          const sTop = preview.scrollTop;
          const sMax = preview.scrollHeight - preview.clientHeight;
          const pct = sMax > 0 ? sTop / sMax : 0;
          const pMax = cm.scrollHeight - cm.clientHeight;
          cm.scrollTop = Math.round(pct * (pMax > 0 ? pMax : 0));
        } finally {
          setTimeout(() => syncing = false, 20);
        }
      }

      cm.addEventListener('scroll', syncPreviewFromEditor, {
        passive: true
      });
      preview.addEventListener('scroll', syncEditorFromPreview, {
        passive: true
      });

      scrollHandlersAttached = true;
      // remember for potential cleanup (if needed)
      return {
        cm,
        preview,
        remove: () => {
          cm.removeEventListener('scroll', syncPreviewFromEditor);
          preview.removeEventListener('scroll', syncEditorFromPreview);
          scrollHandlersAttached = false;
        }
      };
    }

    function observeEditorForSync() {
      // 如果已经创建 observer，先断开
      if (scrollSyncObserver) {
        scrollSyncObserver.disconnect();
        scrollSyncObserver = null;
        scrollHandlersAttached = false;
      }

      const target = document.getElementById('editor');
      if (!target) return;

      // 先尝试直接attach（如果当前就是 markdown+preview）
      const attached = attachScrollSyncHandlers();
      if (attached) return;

      // 否则监听 DOM 变化，等待 preview/code DOM 出现（例如用户切到 markdown 模式）
      scrollSyncObserver = new MutationObserver((mutations) => {
        // 每次变化尝试 attach（attachScrollSyncHandlers 内部会避免重复绑定）
        attachScrollSyncHandlers();
      });
      scrollSyncObserver.observe(target, {
        childList: true,
        subtree: true,
        attributes: false
      });
    }

    /* -----------------------
       页面绑定与加载
       ----------------------- */
    document.addEventListener('DOMContentLoaded', () => {
      const tryInit = () => {
        if (window.toastui && toastui.Editor) {
          initEditorIfNeeded();
          setEditorHeight(getEditorHeight());
        } else {
          setTimeout(tryInit, 120);
        }
      };
      tryInit();
      bindUI();
      loadLinks(); // 立即加载左侧链接
    });

    function bindUI() {
      $('#loadBtn').addEventListener('click', onLoadArticle);
      $('#saveBtn').addEventListener('click', onSaveArticle);
      $('#deleteBtn').addEventListener('click', onDeleteArticle);
      $('#addLinkBtn').addEventListener('click', onAddLink);
      $('#linksTable').addEventListener('click', onLinksTableClick);

      // 滚动联动按钮
      const syncBtn = $('#syncToggle');
      if (syncBtn) {
        syncBtn.addEventListener('click', () => {
          scrollSyncEnabled = !scrollSyncEnabled;
          syncBtn.textContent = `滚动联动：${scrollSyncEnabled ? '开' : '关'}`;
        });
      }
    }

    /* -----------------------
       Article 操作（保持不变）
       ----------------------- */
    async function onLoadArticle() {
      const raw = $('#articleId').value.trim();
      const id = parseInt(raw || '0', 10);
      if (!id) return alert('请输入文章 ID');
      try {
        initEditorIfNeeded();
        const d = await fetchJson(`api.php?action=get_article&id=${encodeURIComponent(id)}`);
        $('#title').value = d.title || '';
        $('#summary').value = d.summary || '';
        editor.setHTML(d.body || '');
        articlePanel.scrollIntoView({
          behavior: 'smooth',
          block: 'center'
        });
      } catch (e) {
        alert('加载失败：' + e.message);
      }
    }

    async function onSaveArticle() {
      initEditorIfNeeded();
      const idRaw = $('#articleId').value.trim();
      const id = parseInt(idRaw || '0', 10);
      const title = $('#title').value.trim();
      const summary = $('#summary').value.trim();
      if (!title) return alert('标题必填');
      const bodyHtml = editor.getHTML();
      const form = new FormData();
      form.append('action', 'save_article');
      if (id) form.append('id', String(id));
      form.append('title', title);
      form.append('summary', summary);
      form.append('body', bodyHtml);

      try {
        const res = await fetch('api.php', {
          method: 'POST',
          body: form
        });
        const j = await res.json();
        if (j.ok) {
          alert('保存成功，文章ID：' + j.data.id);
          $('#articleId').value = j.data.id;
        } else {
          alert('保存失败：' + j.msg);
        }
      } catch (e) {
        alert('保存失败：' + e.message);
      }
    }

    async function onDeleteArticle() {
      const idRaw = $('#articleId').value.trim();
      const id = parseInt(idRaw || '0', 10);
      if (!id) return alert('请输入文章 ID');
      if (!confirm('确认删除该文章？')) return;
      const form = new FormData();
      form.append('action', 'delete_article');
      form.append('id', String(id));
      try {
        const res = await fetch('api.php', {
          method: 'POST',
          body: form
        });
        const j = await res.json();
        if (j.ok) {
          alert('删除成功');
          $('#articleId').value = '';
          $('#title').value = '';
          $('#summary').value = '';
          if (editor) editor.setHTML('');
        } else {
          alert('删除失败：' + j.msg);
        }
      } catch (e) {
        alert('删除失败：' + e.message);
      }
    }

    /* -----------------------
       Links CRUD（动态生成的 inputs 带 id/name/label）
       ----------------------- */
    async function loadLinks() {
      try {
        const data = await fetchJson('api.php?action=list_links');
        const tb = document.querySelector('#linksTable tbody');
        tb.innerHTML = '';
        data.forEach(r => {
          const tr = document.createElement('tr');
          tr.dataset.id = String(r.id);
          const titleId = `link_title_${r.id}`;
          const urlId = `link_url_${r.id}`;
          tr.innerHTML = `
          <td>${r.id}</td>
          <td>
            <label class="sr-only" for="${titleId}">链接标题 ${r.id}</label>
            <input id="${titleId}" name="${titleId}" class="link-title" aria-label="链接标题 ${r.id}" value="${escapeHtml(r.title || '')}">
          </td>
          <td>
            <label class="sr-only" for="${urlId}">链接 URL ${r.id}</label>
            <input id="${urlId}" name="${urlId}" class="link-url" aria-label="链接 URL ${r.id}" value="${escapeHtml(r.url || '')}">
          </td>
          <td>
            <div class="links-row-btn">
              <button class="btn small-btn" data-act="save" data-id="${r.id}">保存</button>
              <button class="btn ghost small-btn" data-act="del" data-id="${r.id}">删除</button>
            </div>
          </td>
        `;
          tb.appendChild(tr);
        });
      } catch (e) {
        const tb = document.querySelector('#linksTable tbody');
        tb.innerHTML = `<tr><td colspan="4" class="muted">加载失败：${e.message}</td></tr>`;
      }
    }

    async function onAddLink() {
      const title = ($('#linkTitleInput').value || '').trim();
      const url = ($('#linkUrlInput').value || '').trim();
      const icon = ($('#linkIconInput').value || '').trim() || '';
      const sort = parseInt(($('#linkSortInput').value || '0'), 10) || 0;
      if (!title || !url) return alert('标题与 URL 必填');
      const form = new FormData();
      form.append('action', 'add_link');
      form.append('title', title);
      form.append('url', url);
      form.append('icon_path', icon);
      form.append('sort_order', String(sort));
      try {
        const r = await fetch('api.php', {
          method: 'POST',
          body: form
        });
        const j = await r.json();
        if (j.ok) {
          $('#linkTitleInput').value = '';
          $('#linkUrlInput').value = '';
          $('#linkIconInput').value = '';
          $('#linkSortInput').value = '0';
          loadLinks();
        } else {
          alert('添加失败：' + j.msg);
        }
      } catch (e) {
        alert('添加失败：' + e.message);
      }
    }

    async function onLinksTableClick(e) {
      const btn = e.target.closest('button');
      if (!btn) return;
      const act = btn.dataset.act;
      const id = parseInt(btn.dataset.id || '0', 10);
      const tr = btn.closest('tr');
      if (act === 'save') {
        const title = tr.querySelector('.link-title').value;
        const url = tr.querySelector('.link-url').value;
        const form = new FormData();
        form.append('action', 'update_link');
        form.append('id', String(id));
        form.append('title', title);
        form.append('url', url);
        try {
          const r = await fetch('api.php', {
            method: 'POST',
            body: form
          });
          const j = await r.json();
          alert(j.ok ? '已保存' : '保存失败：' + j.msg);
        } catch (e) {
          alert('保存失败：' + e.message);
        }
      } else if (act === 'del') {
        if (!confirm('确定删除该链接？')) return;
        const form = new FormData();
        form.append('action', 'delete_link');
        form.append('id', String(id));
        try {
          const r = await fetch('api.php', {
            method: 'POST',
            body: form
          });
          const j = await r.json();
          if (j.ok) tr.remove();
          else alert('删除失败：' + j.msg);
        } catch (e) {
          alert('删除失败：' + e.message);
        }
      }
    }

    /* -----------------------
       小工具
       ----------------------- */
    function escapeHtml(s) {
      return String(s || '').replace(/[&<>"']/g, c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      } [c]));
    }
  </script>
</body>

</html>