/* ============================================================================
 * CKEditor 4 Comments Plugin - Review Mode Patch
 * - Runs only when editor.config.reviewMode === true
 * - Locks document editing but keeps comments fully interactive
 * - Adds "Review Mode" badges (sidebar + editor)
 * - Enables reverse scroll: sidebar comment → highlight in editor
 * ============================================================================
 */

(function () {
  'use strict';

  // --- Inject minimal styles once -------------------------------------------
  (function injectStylesOnce() {
    if (document.getElementById('comments-review-style')) return;
    var css = `
      .review-mode-banner {
        background: #fff3cd;
        border-bottom: 1px solid #ffe8a1;
        color: #7a5c00;
        text-align: center;
        padding: 6px 10px;
        font-weight: 600;
        font-size: 12px;
      }
      .review-mode-chip {
        position: absolute;
        top: 8px;
        right: 8px;
        background: rgba(255,243,205,0.97);
        border: 1px solid #ffe8a1;
        color: #7a5c00;
        border-radius: 14px;
        padding: 2px 10px;
        font-size: 12px;
        font-weight: 600;
        z-index: 10;
        pointer-events: none; /* purely informational */
      }
      .ckc-flash {
        animation: ckcFlash 1.1s ease-in-out 1;
        outline: 2px solid #ffd54f;
        outline-offset: 2px;
      }
      @keyframes ckcFlash {
        0%   { background-color: rgba(255, 235, 59, 0.35); }
        50%  { background-color: rgba(255, 235, 59, 0.9); }
        100% { background-color: rgba(255, 235, 59, 0.35); }
      }
    `;
    var style = document.createElement('style');
    style.id = 'comments-review-style';
    style.textContent = css;
    document.head.appendChild(style);
  })();

  // --- Helper: ensure the yellow banner exists at top of #comments-panel ----
  function ensureSidebarBanner(panel) {
    if (!panel) return;
    if (!panel.querySelector('.review-mode-banner')) {
      var badge = document.createElement('div');
      badge.className = 'review-mode-banner';
      badge.textContent = '🔒 Review Mode: Document is locked, comments enabled';
      panel.prepend(badge);
    }
  }

  // --- Helper: add the "Review Mode" chip inside the editor UI --------------
  function ensureEditorChip(editor) {
    try {
      var contentsSpace = editor.ui.space('contents'); // CKEDITOR.dom.element
      if (!contentsSpace) return;
      var holder = contentsSpace.$; // native DOM
      if (!holder) return;

      // Ensure the container can anchor an absolute child
      var prevPos = holder.style.position;
      if (!prevPos || prevPos === 'static') holder.style.position = 'relative';

      if (!holder.querySelector('.review-mode-chip')) {
        var chip = document.createElement('div');
        chip.className = 'review-mode-chip';
        chip.textContent = '🔒 Review Mode';
        holder.appendChild(chip);
      }
    } catch (e) {}
  }

  // --- Helper: find threadId for a clicked sidebar card ---------------------
  function findThreadIdFromSidebarEl(el) {
    if (!el) return null;

    // Prefer explicit data-thread if present
    var threadAttr = el.getAttribute('data-thread');
    if (threadAttr) return threadAttr;

    // Fallback: map via Repo by uid (comment or reply id)
    try {
      if (typeof Repo === 'undefined') return null;
      var targetUid = el.getAttribute('data-uid');
      if (!targetUid) return null;

      var threads = Repo.getAllThreads ? Repo.getAllThreads() : [];
      for (var i = 0; i < threads.length; i++) {
        var t = threads[i];
        var comments = (t.comments || []);
        for (var j = 0; j < comments.length; j++) {
          var c = comments[j];
          if (String(c.id) === String(targetUid)) return t.id;
          var reps = (c.replies || []);
          for (var k = 0; k < reps.length; k++) {
            if (String(reps[k].id) === String(targetUid)) return t.id;
          }
        }
      }
    } catch (e) {}
    return null;
  }

  // --- Helper: scroll editor to the first anchor of a thread ----------------
  function scrollEditorToThread(editor, threadId) {
    if (!threadId) return;
    try {
      // Works for both iframe & div modes
      var doc = editor.document; // CKEDITOR.dom.document
      if (!doc) return;

      var anchor = doc.findOne('.ckc-anchor[data-ckc-thread="' + threadId + '"]');
      if (!anchor) return;

      var node = anchor.$; // native
      if (!node) return;

      // Smooth scroll within the editable area
      try {
        node.scrollIntoView({ behavior: 'smooth', block: 'center' });
      } catch (e) {
        node.scrollIntoView(true);
      }

      // Flash the highlight
      node.classList.add('ckc-flash');
      setTimeout(function () { node.classList.remove('ckc-flash'); }, 1200);
    } catch (e) {}
  }

  // --- Main: apply when instances are ready ---------------------------------
  CKEDITOR.on('instanceReady', function (evt) {
    var editor = evt.editor;
    if (!editor || !editor.config || !editor.config.reviewMode) return;

    // Lock content (but keep comments outside editor interactive)
    editor.setReadOnly(true);

    // Optional: hide toolbar for cleaner viewer look
    try {
      var top = editor.ui.space('top');
      if (top) top.hide();
    } catch (e) {}

    // Badges
    ensureEditorChip(editor);

    var panel = document.getElementById('comments-panel');
    ensureSidebarBanner(panel);

    // Keep the banner persistent across re-renders
    if (panel && typeof MutationObserver !== 'undefined') {
      var mo = new MutationObserver(function () { ensureSidebarBanner(panel); });
      mo.observe(panel, { childList: true, subtree: false });
    }

    // Reverse scroll: clicking a sidebar comment → scroll to highlight
    if (panel) {
      panel.addEventListener('click', function (e) {
        var card = e.target.closest && e.target.closest('.previous-comment');
        if (!card) return;
        var threadId = findThreadIdFromSidebarEl(card);
        if (!threadId) return;
        scrollEditorToThread(editor, threadId);
      });
    }

    // Allow creating new threads by temporarily lifting readOnly
    // (keeps your "Add Comment" flow usable in review mode)
    var cmd = editor.getCommand && editor.getCommand('addCommentThread');
    if (cmd && typeof cmd.exec === 'function') {
      var originalExec = cmd.exec;
      cmd.exec = function (ed) {
        var wasReadOnly = ed.readOnly;
        if (wasReadOnly) ed.setReadOnly(false);
        try {
          originalExec.call(cmd, ed);
        } finally {
          if (wasReadOnly) ed.setReadOnly(true);
        }
      };
    }

    // Log for sanity
    try { console.log('[CommentsPlugin] Review Mode patch applied for', editor.name); } catch (e) {}
  });
})();
