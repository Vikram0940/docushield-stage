/* ============================================================================
 * TrackChanges Core (CKEditor 4 Inline Track-Changes Engine)
 * ============================================================================
 *  - Manual insert / delete / replace buttons
 *  - Automatic tracking for typing / delete / paste (divarea / inline)
 * ============================================================================
 */

(function () {
  'use strict';

  /* --------------------------------------------------------------------------
   * Global Namespace
   * ------------------------------------------------------------------------ */
  if (!window.TrackChanges) window.TrackChanges = {};

  /* --------------------------------------------------------------------------
   * Utility Helpers
   * ------------------------------------------------------------------------ */
  function newThreadId() {
    return 'tc_' + Date.now().toString(36);
  }

  function insertSpan(html, cls) {
    const el = new CKEDITOR.dom.element('span');
    el.setAttributes({ class: cls });
    el.setHtml(html);
    return el;
  }

  function refreshRepo(editor) {
    if (window.Repo && Repo.setContent) {
      Repo.setContent(editor.getData());
    }
  }

  /* --------------------------------------------------------------------------
   * Manual TrackChanges (toolbar / button actions)
   * ------------------------------------------------------------------------ */
  function wrapSelection(editor, html, type) {
    const sel = editor.getSelection();
    if (!sel) return;
    const range = sel.getRanges()[0];
    if (!range) return;

    const threadId = newThreadId();
    const classes = {
      insert: 'ckc-change ckc-insert',
      delete: 'ckc-change ckc-delete',
      replace: 'ckc-change ckc-replace',
    };

    if (type === 'insert') {
      const el = insertSpan(html || '[Inserted]', classes.insert);
      el.setAttribute('data-ckc-thread', threadId);
      range.insertNode(el);
      editor.getSelection().selectElement(el);
    }

    else if (type === 'delete') {
      const frag = range.extractContents();
      const el = new CKEDITOR.dom.element('span');
      el.setAttributes({
        class: classes.delete,
        'data-ckc-thread': threadId,
      });
      el.append(frag);
      range.insertNode(el);
      editor.getSelection().selectElement(el);
    }
    
    else if (type === 'replace') {
      const oldFrag = range.extractContents();
      const oldEl = new CKEDITOR.dom.element('span');
      oldEl.setAttributes({ class: classes.delete });
      oldEl.append(oldFrag);

      const newEl = insertSpan(html || '[Replacement]', classes.insert);
      const wrap = new CKEDITOR.dom.element('span');
      wrap.setAttributes({
        class: classes.replace,
        'data-ckc-thread': threadId,
      });

      wrap.append(newEl);
      wrap.appendText(' ');
      wrap.append(oldEl);
      range.insertNode(wrap);
      editor.getSelection().selectElement(newEl);
    }

    refreshRepo(editor);
  }

  // Expose manual actions globally
  window.TrackChanges.proposeInsert = function (editor, text) {
    wrapSelection(editor, text || 'Inserted text', 'insert');
  };
  window.TrackChanges.proposeDelete = function (editor) {
    wrapSelection(editor, '', 'delete');
  };
  window.TrackChanges.proposeReplace = function (editor, text) {
    wrapSelection(editor, text || 'Replacement text', 'replace');
  };

})();
  

/* ============================================================================
 * Auto-Tracking Engine (for Inline + Divarea)
 * ============================================================================
 * Detects new typed characters and wraps them with <span class="ckc-insert">
 * ============================================================================
 */
(function () {

  // -------------------------------------------------------------
  // Setup for a given editor
  // -------------------------------------------------------------
  function activateInsertTracker(editor) {
    console.log('%c[TC] Activating Insert Tracker → ' + editor.name, 'color:orange');

    editor.once('contentDom', function () {
      const el = editor.editable().$;
      const doc = el.ownerDocument;

      // ---------------------- Helpers ---------------------------
      const isPrintableKey = e => (
        !(e.ctrlKey || e.metaKey || e.altKey) &&
        !['Backspace', 'Delete', 'Enter'].includes(e.key) &&
        e.key && e.key.length === 1
      );

      const isInsideInsert = () => {
        const sel = doc.getSelection();
        if (!sel.rangeCount) return false;
        let node = sel.getRangeAt(0).startContainer;
        while (node && node !== el) {
          if (node.nodeType === 1 && node.classList.contains('ckc-insert')) return true;
          node = node.parentNode;
        }
        return false;
      };

      const placeCaretAfter = node => {
        const sel = doc.getSelection();
        const r = doc.createRange();
        r.setStartAfter(node);
        r.collapse(true);
        sel.removeAllRanges();
        sel.addRange(r);
      };

      // ---------------------- Core Logic -------------------------
      function wrapJustTyped() {
        const sel = doc.getSelection();
        if (!sel.rangeCount) return;
        const range = sel.getRangeAt(0);
        if (range.startContainer.nodeType !== 3) return;

        const textNode = range.startContainer;
        const parent = textNode.parentNode;
        if (parent.classList && parent.classList.contains('ckc-insert')) return;

        const text = textNode.nodeValue;
        const offset = range.startOffset;
        if (!text || offset === 0) return;

        const before = text.slice(0, offset - 1);
        const char = text.slice(offset - 1, offset);
        const after = text.slice(offset);

        const span = doc.createElement('span');
        span.className = 'ckc-change ckc-insert';
        span.textContent = char;

        const frag = doc.createDocumentFragment();
        if (before) frag.appendChild(doc.createTextNode(before));
        frag.appendChild(span);
        if (after) frag.appendChild(doc.createTextNode(after));
        parent.replaceChild(frag, textNode);

        placeCaretAfter(span);
        console.log('%c[TC] Inserted → "' + char + '"', 'color:lime');

        if (window.TrackActions && typeof TrackActions.ensureInsertThread === 'function') {
          TrackActions.ensureInsertThread(editor, span);
        }

      }

      // ---------------------- Listeners --------------------------
      el.addEventListener('keydown', e => {
        el._tcPrintable = isPrintableKey(e);
      });

      el.addEventListener('input', e => {
        if (!el._tcPrintable) return;
        if (isInsideInsert()) return;
        wrapJustTyped();
      });

      console.log('%c[TC] Insert Tracker Active ✅', 'color:lime');
    });
  }

  // -------------------------------------------------------------
  // Activate for existing editors and future ones
  // -------------------------------------------------------------
  Object.values(CKEDITOR.instances).forEach(activateInsertTracker);
  CKEDITOR.on('instanceReady', evt => activateInsertTracker(evt.editor));

})();


/* ============================================================================
 * TrackActions (Approve / Deny integration)
 * ============================================================================
 */

(function () {
  'use strict';

  // --------------------------------------------------------------------------
  // Get active editor safely
  // --------------------------------------------------------------------------
  function getActiveEditor() {
    // Prefer focused editor
    for (const id in CKEDITOR.instances) {
      const ed = CKEDITOR.instances[id];
      if (ed.focusManager && ed.focusManager.hasFocus) return ed;
    }
    return Object.values(CKEDITOR.instances)[0] || null;
  }

  // --------------------------------------------------------------------------
  // Helpers
  // --------------------------------------------------------------------------
  function unwrapSpanKeepText(editor, span) {
    const text = new CKEDITOR.dom.text(span.getText(), editor.document);
    span.insertBeforeMe(text);
    span.remove();
  }

  function markThreadStatus(threadId, status) {
    const threads = Repo.getAllThreads?.() || [];
    const thread = threads.find(t => t.id === threadId);
    if (!thread) return;

    thread.status = status;
    if (thread.comments?.length) {
      const last = thread.comments[thread.comments.length - 1];
      last.content = (last.content || '') + ` <em>(${status.toUpperCase()})</em>`;
    }

    const editor = getActiveEditor();
    if (editor && Repo.setContent) Repo.setContent(editor.getData());
    if (typeof renderComments === 'function') renderComments();
    console.log(`[Repo] Thread ${threadId} → ${status}`);
  }

  function ensureInsertThread(editor, spanEl) {
      if (!spanEl || !spanEl.setAttribute) return;
      if (spanEl.getAttribute('data-ckc-thread')) return; // already linked

      const threadId = 'tc_' + Date.now().toString(36);
      spanEl.setAttribute('data-ckc-thread', threadId);
      spanEl.setAttribute('data-change', 'insert');
      spanEl.setAttribute('data-status', 'pending');

      if (typeof Repo !== 'undefined') {
        Repo.createThread({ threadId });
        Repo.addComment(threadId, {
          author: editor.config.trackChanges?.author?.name || 'Anonymous',
          avatar: editor.config.trackChanges?.author?.avatar || 'https://i.pravatar.cc/40?img=12',
          content: `Inserted text: "${spanEl.textContent.trim()}"`
        });
        if (typeof renderComments === 'function') renderComments();
      }

      console.log('%c[TrackChanges] Created insert thread:', 'color:orange', threadId);
    }
    

  // --------------------------------------------------------------------------
  // Main actions
  // --------------------------------------------------------------------------
  window.TrackActions = window.TrackActions || {};
  window.TrackActions.ensureInsertThread = ensureInsertThread;

  window.TrackActions.approve = function (editorOrNull, threadId) {
    const editor = editorOrNull || getActiveEditor();
    if (!editor) return console.warn('[TrackActions] No editor instance found.');

    const span = editor.document.findOne(`span[data-ckc-thread="${threadId}"]`);
    if (!span) return console.warn('[TrackActions] Span not found for', threadId);

    unwrapSpanKeepText(editor, span);
    markThreadStatus(threadId, 'approved');
    if (Repo.saveVersion) Repo.saveVersion(editor.getData());

    console.log('%c[TrackActions] Approved', 'color:green', threadId);
  };

  window.TrackActions.deny = function (editorOrNull, threadId) {
    const editor = editorOrNull || getActiveEditor();
    if (!editor) return console.warn('[TrackActions] No editor instance found.');

    const span = editor.document.findOne(`span[data-ckc-thread="${threadId}"]`);
    if (!span) return console.warn('[TrackActions] Span not found for', threadId);

    span.remove();
    markThreadStatus(threadId, 'rejected');
    if (Repo.saveVersion) Repo.saveVersion(editor.getData());

    console.log('%c[TrackActions] Rejected', 'color:red', threadId);
  };
})();

