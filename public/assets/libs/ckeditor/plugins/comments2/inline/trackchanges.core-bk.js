/* ============================================================================
 * TrackChanges Core (CKEditor 4 Inline Track-Changes Engine)
 * ============================================================================
 * Handles both manual + automatic insert / delete / replace proposals.
 *  - Manual buttons call TrackChanges.proposeInsert/Delete/Replace()
 *  - Auto mode wraps user typing as .ckc-insert, deletions as .ckc-delete
 * Integrates with Repo for thread creation + auto-save.
 * ============================================================================
 */

(function () {
  'use strict';

  if (!window.TrackChanges) window.TrackChanges = {};

  // --------------------------------------------------------------------------
  // Helpers
  // --------------------------------------------------------------------------
  function newThreadId() {
    return 'tc_' + Date.now().toString(36);
  }

  // --------------------------------------------------------------------------
  // Core wrapper (manual mode)
  // --------------------------------------------------------------------------
  function wrapSelection(editor, html, type) {
    const sel = editor.getSelection();
    if (!sel) return;
    const range = sel.getRanges()[0];
    if (!range) return;

    const threadId = newThreadId();

    // Common class map (no inline styles)
    const CLASS_INSERT = 'ckc-change ckc-insert';
    const CLASS_DELETE = 'ckc-change ckc-delete';
    const CLASS_REPLACE = 'ckc-change ckc-replace';

    // --------------------------------------------------
    // INSERT
    // --------------------------------------------------
    if (type === 'insert') {
      const insertSpan = new CKEDITOR.dom.element('span');
      insertSpan.setAttributes({
        'data-ckc-thread': threadId,
        'class': CLASS_INSERT
      });
      insertSpan.setHtml(html || '[Inserted]');
      range.insertNode(insertSpan);
      editor.getSelection().selectElement(insertSpan);
    }

    // --------------------------------------------------
    // DELETE
    // --------------------------------------------------
    else if (type === 'delete') {
      const fragment = range.extractContents();
      const delSpan = new CKEDITOR.dom.element('span');
      delSpan.setAttributes({
        'data-ckc-thread': threadId,
        'class': CLASS_DELETE
      });
      delSpan.append(fragment);
      range.insertNode(delSpan);
      editor.getSelection().selectElement(delSpan);
    }

    // --------------------------------------------------
    // REPLACE
    // --------------------------------------------------
    else if (type === 'replace') {
      const oldFragment = range.extractContents();

      // old text (red strike)
      const oldSpan = new CKEDITOR.dom.element('span');
      oldSpan.setAttributes({
        'class': CLASS_DELETE
      });
      oldSpan.append(oldFragment);

      // new text (green highlight)
      const newSpan = new CKEDITOR.dom.element('span');
      newSpan.setAttributes({
        'class': CLASS_INSERT
      });
      newSpan.setHtml(html || '[Replacement]');

      const wrapper = new CKEDITOR.dom.element('span');
      wrapper.setAttributes({
        'data-ckc-thread': threadId,
        'class': CLASS_REPLACE
      });
      wrapper.append(newSpan);
      wrapper.appendText(' ');
      wrapper.append(oldSpan);

      range.insertNode(wrapper);
      editor.getSelection().selectElement(newSpan);
    }

    // --------------------------------------------------
    // Thread + Repo Integration
    // --------------------------------------------------
    if (window.Repo && typeof Repo.createThread === 'function') {
      Repo.createThread({
        threadId,
        userId: 'TrackChange',
        author: 'TrackChange System'
      });
      Repo.addComment(threadId, {
        author: 'System',
        content:
          type === 'insert'
            ? 'Insertion proposed.'
            : type === 'delete'
            ? 'Deletion proposed.'
            : 'Replacement proposed.'
      });
    }

    if (Repo && Repo.setContent) Repo.setContent(editor.getData());
    console.log(`✅ ${type.toUpperCase()} proposed [${threadId}]`);
  }

  // --------------------------------------------------------------------------
  // Public API (manual)
  // --------------------------------------------------------------------------
  window.TrackChanges.proposeInsert = function (editor, text) {
    wrapSelection(editor, text || 'Inserted text', 'insert');
  };

  window.TrackChanges.proposeDelete = function (editor) {
    wrapSelection(editor, '', 'delete');
  };

  window.TrackChanges.proposeReplace = function (editor, newText) {
    wrapSelection(editor, newText || 'Replacement text', 'replace');
  };

  // --------------------------------------------------------------------------
  // Auto-Tracking Engine (typing / deletion)
  // --------------------------------------------------------------------------
  CKEDITOR.on('instanceReady', function (evt) {
    const editor = evt.editor;
    const IS_OWNER = false; // TODO: later use editor.config.isOwner

    if (IS_OWNER) return; // skip tracking for owner

    // Inject minimal CSS if not already available
    if (!document.getElementById('ckc-style')) {
      const style = document.createElement('style');
      style.id = 'ckc-style';
      style.textContent = `
        .ckc-insert { background-color: rgba(81,207,102,0.15); color: #0f5132; }
        .ckc-delete { text-decoration: line-through; color: #842029; }
        .ckc-replace .ckc-insert { background-color: rgba(51,154,240,0.15); border-bottom: 1px dashed #339af0; }
        .ckc-replace .ckc-delete { text-decoration: line-through; color: #dc3545; }
      `;
      document.head.appendChild(style);
    }

    const editable = editor.editable().$;
    const observer = new MutationObserver(mutations => {
      mutations.forEach(m => {
        // INSERTIONS
        m.addedNodes.forEach(node => {
          if (node.nodeType === 3 && node.textContent.trim()) {
            // Text node inserted
            const span = document.createElement('span');
            span.className = 'ckc-change ckc-insert';
            span.textContent = node.textContent;
            node.parentNode.replaceChild(span, node);
          } else if (node.nodeType === 1 && node.nodeName !== 'SPAN') {
            Array.from(node.childNodes).forEach(ch => {
              if (ch.nodeType === 3 && ch.textContent.trim()) {
                const span = document.createElement('span');
                span.className = 'ckc-change ckc-insert';
                span.textContent = ch.textContent;
                node.replaceChild(span, ch);
              }
            });
          }
        });

        // DELETIONS
        m.removedNodes.forEach(node => {
          if (node.nodeType === 3 && node.textContent.trim()) {
            const del = document.createElement('span');
            del.className = 'ckc-change ckc-delete';
            del.textContent = node.textContent;
            if (m.target && m.target.parentNode) {
              m.target.parentNode.insertBefore(del, m.target.nextSibling);
            }
          }
        });
      });

      if (window.Repo && Repo.setContent) {
        Repo.setContent(editor.getData());
      }
    });

    observer.observe(editable, {
      childList: true,
      characterData: true,
      subtree: true
    });

    console.log('✅ TrackChanges auto-tracking active for', editor.name);
  });
})();
