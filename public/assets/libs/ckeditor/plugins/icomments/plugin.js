/* ============================================================================
 * CKEditor 4 icomments Plugin (UI only, auto-loads Repo + CSS)
 * - No external <template> dependency
 * - Newest-first ordering
 * - Auto-focus + smooth scroll on composers
 * - Replies as mini icomment boxes with "Replying to {name}" label
 * - Color dot + left border per thread color
 * ============================================================================
 * Version: 2.1.3
 */

(function () {
  'use strict';

  /* --------------------------------------------------------------------------
   * Sidebar Rendering
   * ------------------------------------------------------------------------ */
  function ensureSidebar(editor) {
    var containerSelector = editor.config.commentsContainer || 'body';
    var container = document.querySelector(containerSelector);
    if (!container) {
      console.warn('icomments plugin: container not found, using <body>.');
      container = document.body;
    }
    var panel = container.querySelector('#icomments-panel');
    if (panel) return panel;

    var wrapper = document.createElement('div');
    wrapper.className = 'icomments-wrapper';
    wrapper.innerHTML = '<div id="icomments-panel" class="icomments-panel h-100" style="overflow-y:auto;"></div>';
    container.appendChild(wrapper);
    return wrapper.querySelector('#icomments-panel');
  }

  function renderReplyCard(thread, parenticomment, reply) {
    var node = document.createElement('div');
    node.className = 'previous-icomment';
    node.dataset.uid = reply.id || ('r_' + Date.now().toString(36));
    node.dataset.color = thread.color || '#ffe066';
    node.style.borderLeft = '4px solid ' + (thread.color || '#ffe066');

    node.innerHTML = `
      <div class="icomment-header d-flex justify-content-between align-items-start">
        <div class="avatar-name d-flex align-items-center gap-2">
          <img src="${reply.avatar || 'https://i.pravatar.cc/40'}" class="rounded-circle" width="28" height="28">
          <div class="c-title fw-semibold">${reply.author || 'Anonymous'}</div>
          <span class="color-dot" style="background:${thread.color || '#ffe066'}"></span>
          <div class="c-time small text-muted">1m</div>
        </div>
      </div>
      <div class="reply-to small text-muted mb-1">Replying to ${parenticomment.author || 'someone'}</div>
      <div class="icomment-content">${reply.content || ''}</div>
      <div class="replies ms-2 mt-1"></div>
    `;
    return node;
  }

  function rendericomments() {
    var panel = document.getElementById('icomments-panel');
    if (!panel || typeof Repo === 'undefined') return;
    panel.innerHTML = '';

    Repo.getAllThreads().forEach(function (thread) {
      // Newest-first for top-level icomments
      var icomments = (thread.icomments || []).slice().reverse();

      icomments.forEach(function (icomment) {
        var node = document.createElement('div');
        node.className = 'previous-icomment';
        node.dataset.uid = icomment.id;
        node.dataset.thread = thread.id; 
        node.dataset.color = thread.color || '#ffe066';
        node.style.borderLeft = '4px solid ' + (thread.color || '#ffe066');

        node.innerHTML = `
          <div class="icomment-header d-flex justify-content-between align-items-start">
            <div class="avatar-name d-flex align-items-center gap-2">
              <img src="${icomment.avatar || 'https://i.pravatar.cc/40'}" class="rounded-circle" width="32" height="32">
              <div class="c-title fw-semibold">${icomment.author || 'Anonymous'}</div>
              <span class="color-dot" style="background:${thread.color || '#ffe066'}"></span>
              <div class="c-time small text-muted">1m</div>
            </div>
            <div class="icomment-actions dropdown ms-auto">
              <a href="#" class="text-secondary c-action" data-bs-toggle="dropdown">
                <i class="bi bi-three-dots"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" data-action="reply" data-thread="${thread.id}" data-icomment="${icomment.id}">Reply</a></li>
                <li><a class="dropdown-item" href="#" data-action="edit" data-thread="${thread.id}" data-icomment="${icomment.id}">Edit</a></li>
                <li><a class="dropdown-item" href="#" data-action="delete" data-thread="${thread.id}" data-icomment="${icomment.id}">Delete</a></li>
              </ul>
            </div>
          </div>
          <div class="icomment-content">${icomment.content || ''}</div>
          <div class="replies ms-2 mt-1"></div>
        `;

        // Replies as mini icomment cards (optional newest-first)
        var repliesContainer = node.querySelector('.replies');
        var replies = (icomment.replies || []).slice().reverse();
        replies.forEach(function (reply) {
          var rCard = renderReplyCard(thread, icomment, reply);
          repliesContainer.appendChild(rCard);
        });

        // Because we reversed the array, appending preserves newest-first
        panel.appendChild(node);
      });
    });

    bindActions(panel);
  }

  function smoothFocus(el) {
    if (!el) return;
    el.focus();
    try {
      el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } catch (e) {}
  }

  function bindActions(panel) {
    if (typeof Repo === 'undefined') return;

    // Reply
    panel.querySelectorAll('[data-action="reply"]').forEach(function (btn) {
      btn.onclick = function (e) {
        e.preventDefault();
        var threadId = btn.dataset.thread;
        var icommentId = btn.dataset.icomment;
        var icommentEl = btn.closest('.previous-icomment');

        // ensure only one composer at a time in panel
        panel.querySelectorAll('.icomment-footer').forEach(function (f) { f.remove(); });

        var author = (icommentEl.querySelector('.c-title') || {}).textContent || '';
        var composer = document.createElement('div');
        composer.className = 'icomment-footer';
        composer.innerHTML = `
          <div class="icomment-reply d-flex align-items-start gap-2 my-2">
            <img src="https://i.pravatar.cc/40?img=12" class="rounded-circle" width="32" height="32">
            <div class="flex-grow-1">
              <div class="small text-muted mb-1">Replying to <strong>${author}</strong></div>
              <textarea class="form-control reply-input" rows="1" placeholder="Write a reply..."></textarea>
              <div class="d-flex justify-content-end gap-2 mt-2">
                <button type="button" class="btn btn-sm btn-light cancel-reply">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary post-reply" disabled>Reply</button>
              </div>
            </div>
          </div>`;

        // insert composer just before .replies block for context
        var repliesBlock = icommentEl.querySelector('.replies');
        icommentEl.insertBefore(composer, repliesBlock);

        var input = composer.querySelector('.reply-input');
        var postBtn = composer.querySelector('.post-reply');
        var cancelBtn = composer.querySelector('.cancel-reply');

        input.addEventListener('input', function () {
          postBtn.disabled = input.value.trim().length === 0;
          // auto-resize
          input.style.height = 'auto';
          input.style.height = input.scrollHeight + 'px';
        });

        cancelBtn.onclick = function () { composer.remove(); };

        postBtn.onclick = function () {
          var text = input.value.trim();
          if (!text) return;
          Repo.addReply(threadId, icommentId, {
            author: 'You',
            avatar: 'https://i.pravatar.cc/40?img=12',
            content: text,
            createdAt: new Date()
          });
          composer.remove();
          rendericomments();
        };

        smoothFocus(input);
      };
    });

    // Edit (top-level icomments)
    panel.querySelectorAll('[data-action="edit"]').forEach(function (btn) {
      btn.onclick = function (e) {
        e.preventDefault();
        var threadId = btn.dataset.thread;
        var icommentId = btn.dataset.icomment;
        var icommentEl = btn.closest('.previous-icomment');
        var contentEl = icommentEl.querySelector('.icomment-content');
        var oldText = contentEl.textContent;

        contentEl.classList.add('d-none');

        var editBox = document.createElement('div');
        editBox.className = 'icomment-footer mt-2';
        editBox.innerHTML = `
          <textarea class="form-control edit-input" rows="2">${oldText}</textarea>
          <div class="mt-2 d-flex justify-content-end gap-2">
            <button class="btn btn-sm btn-light cancel-edit">Cancel</button>
            <button class="btn btn-sm btn-primary save-edit">Save</button>
          </div>`;

        contentEl.after(editBox);

        var ta = editBox.querySelector('.edit-input');
        smoothFocus(ta);
        ta.addEventListener('input', function () {
          ta.style.height = 'auto';
          ta.style.height = ta.scrollHeight + 'px';
        });

        editBox.querySelector('.cancel-edit').onclick = function () {
          editBox.remove();
          contentEl.classList.remove('d-none');
        };

        editBox.querySelector('.save-edit').onclick = function () {
          var newVal = ta.value.trim();
          if (newVal && newVal !== oldText) {
            Repo.editicomment(threadId, icommentId, newVal);
            rendericomments();
          } else {
            // no change -> restore view
            editBox.remove();
            contentEl.classList.remove('d-none');
          }
        };
      };
    });

    // Delete (top-level icomments)
    panel.querySelectorAll('[data-action="delete"]').forEach(function (btn) {
      btn.onclick = function (e) {
        e.preventDefault();
        var threadId = btn.dataset.thread;
        var icommentId = btn.dataset.icomment;
        if (confirm('Delete this icomment?')) {
          Repo.deleteicomment(threadId, icommentId);
          rendericomments();
        }
      };
    });
  }

  function wrapSelectionWithAnchor(editor, threadId, color) {
    var style = new CKEDITOR.style({
      element: 'span',
      attributes: {
        'data-ckc-thread': threadId,
        'class': 'ckc-anchor'
      },
      styles: {
        'background-color': color + '55'
      }
    });
    editor.applyStyle(style);
  }

  /* --------------------------------------------------------------------------
   * CKEditor Plugin Definition
   * ------------------------------------------------------------------------ */
  CKEDITOR.plugins.add('icomments', {
    icons: 'icomment',
    init: function (editor) {
      editor.config.extraAllowedContent =
        (editor.config.extraAllowedContent || '') +
        '; span[data-ckc-thread](ckc-anchor)';

      // Auto-load CSS (editor + page)
      editor.addContentsCss(this.path + 'styles.css');
      if (!document.getElementById('icomments-plugin-css')) {
        var link = document.createElement('link');
        link.id = 'icomments-plugin-css';
        link.rel = 'stylesheet';
        link.href = this.path + 'styles.css';
        document.head.appendChild(link);
      }

editor.on('instanceReady', function () {
    const sidebar = document.querySelector(editor.config.commentsContainer || '.doc-body');
    const toggleBtn = document.querySelector('#comments-toggle');
    if (!sidebar) return;

    const HIGHLIGHT_COLOR = '#fff3cd';
    let lastTextPerParagraph = []; // baseline text per paragraph
    let commentedTextPerParagraph = []; // track text already commented/approved
    let liveCommentBoxes = {};      // track live comment per paragraph

    // Create a new live comment
    function createLiveComment(newText, pIndex) {
        const liveCommentBox = document.createElement('div');
        liveCommentBox.className = 'previous-icomment live-editor-comment flash-zoom';
        liveCommentBox.dataset.uid = 'live_' + Date.now().toString(36);
        liveCommentBox.dataset.color = '#0d6efd';
        liveCommentBox.dataset.pIndex = pIndex;
        liveCommentBox.style.borderLeft = '4px solid #0d6efd';
        liveCommentBox.style.background = '#f1f5ff';
        liveCommentBox.style.padding = '8px';
        liveCommentBox.style.margin = '8px 18px';
        liveCommentBox.style.borderRadius = '8px';
        liveCommentBox.style.wordWrap = 'break-word';
        liveCommentBox.innerHTML = `
            <div class="icomment-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <img src="https://i.pravatar.cc/40?img=12" class="rounded-circle" width="32" height="32">
                    <div class="c-title fw-semibold">You</div>
                    <span class="color-dot" style="background:#0d6efd"></span>
                    <div class="c-time small text-muted">Live</div>
                </div>
                <div class="comment-actions d-flex gap-2">
                    <button class="approve-btn" title="Approve" style="background:none;border:none;cursor:pointer;font-size:26px;color:#198754;">&#10003;</button>
                    <button class="reject-btn" title="Reject" style="background:none;border:none;cursor:pointer;font-size:26px;color:red;">&#10005;</button>
                </div>
            </div>
            <div class="icomment-content" style="display: flex; align-items: flex-start;">
                <strong style="margin-right: 8px; min-width: 50px;">Insert:</strong>
                <span class="live-text" style="flex: 1; word-break: break-word;">${newText}</span>
            </div>
        `;
        sidebar.prepend(liveCommentBox);
        setTimeout(() => liveCommentBox.classList.remove('flash-zoom'), 800);

        const editable = editor.editable();

        // Reject → delete text from editor
        liveCommentBox.querySelector('.reject-btn').addEventListener('click', () => {
            const paragraph = editable.find('p').toArray()[pIndex];
            if (paragraph) paragraph.remove(); 
            liveCommentBox.remove();
            delete liveCommentBoxes[pIndex];
            lastTextPerParagraph[pIndex] = '';
            commentedTextPerParagraph[pIndex] = '';
        });

        // Approve → keep text, remove highlight, update commentedTextPerParagraph
        liveCommentBox.querySelector('.approve-btn').addEventListener('click', () => {
            const paragraph = editable.find('p').toArray()[pIndex];
            if (paragraph) {
                paragraph.find('span.live-highlight').toArray().forEach(span => {
                    const parent = span.getParent();
                    span.remove(true);
                    if (parent) parent.$.normalize();
                });
            }
            liveCommentBox.remove();
            delete liveCommentBoxes[pIndex];

            // Update commented text baseline for this paragraph
            commentedTextPerParagraph[pIndex] = lastTextPerParagraph[pIndex];
            editor.resetDirty();
        });

        return liveCommentBox;
    }

    editor.on('change', function () {
        const editable = editor.editable();
        const paragraphs = editable.find('p').toArray();

        paragraphs.forEach((p, idx) => {
            const text = p.getText();
            const last = lastTextPerParagraph[idx] || '';
            const commented = commentedTextPerParagraph[idx] || '';

            if (!text) return; // skip empty paragraphs

            // Only take **new text typed after last approved/commented**
            const newText = text.substring(commented.length);

            if (!newText) return; // nothing new to show

            if (!liveCommentBoxes[idx]) {
                liveCommentBoxes[idx] = createLiveComment(newText, idx);
            } else {
                liveCommentBoxes[idx].querySelector('.live-text').textContent = newText;
            }

            // Highlight **only new text**
            const existingSpans = p.find('span.live-highlight').toArray();
            existingSpans.forEach(span => span.remove(true));

            const oldPart = text.substring(0, commented.length);
            const latestPart = text.substring(commented.length);

            const span = new CKEDITOR.dom.element('span');
            span.setAttribute('class', 'live-highlight');
            span.setStyle('background-color', HIGHLIGHT_COLOR);
            span.setText(latestPart);


            lastTextPerParagraph[idx] = text;
        });

        if (!sidebar.classList.contains('comments-open')) {
            sidebar.classList.add('comments-open');
            if (toggleBtn) toggleBtn.classList.add('active');
        }
    });

    CKEDITOR.addCss(`
        span.live-highlight {
            background-color: ${HIGHLIGHT_COLOR} !important;
            border-radius: 3px;
            padding: 2px;
        }
    `);
});





      // Auto-load Repo
      var repoType = editor.config.commentsRepo || 'local'; // 'local' | 'api'
      var repoPath = this.path + 'repo.' + repoType + '.js';

      function startOnceRepoLoaded() {
        Repo.init(editor.config.documentId || 'demoDoc');
        ensureSidebar(editor);
        rendericomments();

        // Click highlight in editor → scroll to sidebar icomment
        editor.on('contentDom', function () {
          var editable = editor.editable();

          /* ======================================================
          * 🟢 Custom Delete / Backspace → Apply StrikeThrough
          * ====================================================== */
          /* ======================================================
        * 🟢 Improved Delete / Backspace → Apply StrikeThrough
        * ====================================================== */
 editable.attachListener(editable, 'keydown', function (evt) {
    const keyCode = evt.data.getKey();
    if (keyCode !== 8 && keyCode !== 46) return;

    const selection = editor.getSelection();
    const range = selection && selection.getRanges()[0];
    if (!range) return;

    // ----------------------------
    // CASE 1: Selection delete → wrap in <del>
    // ----------------------------
    if (!range.collapsed) {
        const frag = range.cloneContents();
        const tempDiv = document.createElement('div');
        tempDiv.appendChild(frag.$);
        const deletedText = tempDiv.textContent.trim();
        if (!deletedText) return;

        const delEl = new CKEDITOR.dom.element('del');
        delEl.setText(deletedText);

        range.deleteContents();
        range.insertNode(delEl);
        range.moveToPosition(delEl, CKEDITOR.POSITION_AFTER_END);
        selection.selectRanges([range]);

        // Create sidebar box (same as before)
        const sidebarPanel = document.getElementById('icomments-panel');
        const delBox = document.createElement('div');
        delBox.className = 'previous-icomment deleted-entry flash-zoom';
        delBox.dataset.uid = 'del_' + Date.now().toString(36);
        delBox.dataset.color = '#dc3545';
        delBox.style.borderLeft = '4px solid #dc3545';
        delBox.style.background = '#fff0f0';
        delBox.style.padding = '8px';
        delBox.style.borderRadius = '8px';
        delBox.style.wordWrap = 'break-word';

        delBox.innerHTML = `
          <div class="icomment-header d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2">
                  <img src="https://i.pravatar.cc/40?img=12" class="rounded-circle" width="32" height="32">
                  <div class="c-title fw-semibold">You</div>
                  <span class="color-dot" style="background:#dc3545"></span>
                  <div class="c-time small text-muted">Deleting</div>
              </div>
              <div class="comment-actions d-flex gap-2">
                  <button class="approve-del-btn" title="Approve Delete"
                      style="background:none;border:none;cursor:pointer;font-size:26px;color:#198754;">&#10003;</button>
                  <button class="reject-del-btn" title="Reject Delete"
                      style="background:none;border:none;cursor:pointer;font-size:26px;color:red;">&#10005;</button>
              </div>
          </div>
          <div class="icomment-content" style="display:flex; align-items:flex-start; margin-top:6px;">
              <strong style="margin-right:8px; min-width:60px;">Deleting:</strong>
              <span class="deleted-text" style="flex:1; word-break:break-word;">${CKEDITOR.tools.htmlEncode(deletedText)}</span>
          </div>
        `;
        sidebarPanel.prepend(delBox);

        delBox.querySelector('.approve-del-btn').addEventListener('click', function () {
            editor.editable().find('del').toArray().forEach(d => {
                if (d.getText() === deletedText) d.remove();
            });
            delBox.remove();
        });

        delBox.querySelector('.reject-del-btn').addEventListener('click', function () {
            editor.editable().find('del').toArray().forEach(d => {
                if (d.getText() === deletedText) {
                    const restored = new CKEDITOR.dom.text(d.getText());
                    restored.insertAfter(d);
                    d.remove();
                }
            });
            delBox.remove();
        });

        setTimeout(() => delBox.classList.remove('flash-zoom'), 600);

        evt.data.preventDefault();
        evt.cancel();
        return false;
    }

    // ----------------------------
    // CASE 2: Single character → wrap in <del> immediately
    // ----------------------------
    else {
        const textNode = range.startContainer;
        if (!textNode || textNode.type !== CKEDITOR.NODE_TEXT) return;

        const offset = range.startOffset;
        const txt = textNode.getText();
        let charToDelete = '';
        if (keyCode === 8 && offset > 0) charToDelete = txt.charAt(offset - 1);
        else if (keyCode === 46 && offset < txt.length) charToDelete = txt.charAt(offset);
        if (!charToDelete) return;

        // Split text around character
        let before, after;
        if (keyCode === 8) {
            before = txt.slice(0, offset - 1);
            after = txt.slice(offset);
        } else {
            before = txt.slice(0, offset);
            after = txt.slice(offset + 1);
        }

        const delEl = new CKEDITOR.dom.element('del');
        delEl.setText(charToDelete);

        const newNode = new CKEDITOR.dom.element('span');
        newNode.setHtml(before + delEl.getOuterHtml() + after);

        newNode.insertAfter(textNode);
        textNode.remove();

        // Move cursor **after the <del>**
        const newRange = editor.createRange();
        newRange.setStart(delEl, CKEDITOR.POSITION_AFTER_END);
        newRange.collapse(true);
        selection.selectRanges([newRange]);

        evt.data.preventDefault();
        evt.cancel();
        return false;
    }
});



          /* ======================================================
          * 🟡 Existing Click-to-Scroll Comment Logic
          * ====================================================== */
          editable.attachListener(editable, 'click', function (evt) {
            var target = evt.data.getTarget();
            if (!target) return;

            // Find closest anchor
            var anchorEl = target.$.closest
              ? target.$.closest('.ckc-anchor')
              : (target.$.classList && target.$.classList.contains('ckc-anchor') ? target.$ : null);

            if (!anchorEl) return;

            var threadId = anchorEl.getAttribute('data-ckc-thread');
            if (!threadId) return;

            // Find first icomment of this thread
            var icommentEl = document.querySelector('#icomments-panel .previous-icomment[data-thread="' + threadId + '"]');
            if (icommentEl) {
              icommentEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
              icommentEl.classList.add('flash');
              setTimeout(function () { icommentEl.classList.remove('flash'); }, 1500);
            }
          });
        });


        Repo.on('icommentAdded', rendericomments);
        Repo.on('replyAdded', rendericomments);
        Repo.on('icommentEdited', rendericomments);
        Repo.on('icommentDeleted', rendericomments);
        Repo.on('repoLoaded', rendericomments);

        // Auto-load readonly patch if needed
        if (editor.config.reviewMode) {
          var roScript = document.createElement('script');
          roScript.src = CKEDITOR.plugins.getPath('icomments') + 'icomments.readonly.js';
          document.head.appendChild(roScript);
        }
      }

      if (typeof Repo === 'undefined') {
        var script = document.createElement('script');
        script.src = repoPath;
        script.onload = startOnceRepoLoaded;
        document.head.appendChild(script);
      } else {
        startOnceRepoLoaded();
      }

      // Command to add a new thread
      editor.addCommand('addicommentThread', {
        exec: function (ed) {
          if (typeof Repo === 'undefined') return;

          var sel = ed.getSelection();
          if (!sel) return;
          var range = sel.getRanges()[0];
          if (!range || range.collapsed) return;

          var threadId = 't_' + Date.now().toString(36);
          var thread = Repo.createThread({ threadId: threadId });

          // Highlight selection inside editor with semi-transparent thread color
          wrapSelectionWithAnchor(ed, threadId, thread.color || '#ffe066');

          // Open composer at top of panel
          var panel = ensureSidebar(ed);
          // ensure only one composer at a time
          panel.querySelectorAll('.icomment-footer').forEach(function (f) { f.remove(); });

          var composer = document.createElement('div');
          composer.className = 'icomment-footer';
          composer.innerHTML = `
            <div class="icomment-reply d-flex align-items-start gap-2 my-2">
              <img src="https://i.pravatar.cc/40?img=12" class="rounded-circle" width="32" height="32">
              <div class="flex-grow-1">
                <textarea class="form-control reply-input" rows="2" placeholder="Write a icomment..."></textarea>
                <div class="d-flex justify-content-end gap-2 mt-2">
                  <button type="button" class="btn btn-sm btn-light cancel-icomment">Cancel</button>
                  <button type="button" class="btn btn-sm btn-primary post-icomment" disabled>Post</button>
                </div>
              </div>
            </div>`;

          panel.prepend(composer);

          var input = composer.querySelector('.reply-input');
          var postBtn = composer.querySelector('.post-icomment');
          var cancelBtn = composer.querySelector('.cancel-icomment');

          input.addEventListener('input', function () {
            postBtn.disabled = input.value.trim().length === 0;
            input.style.height = 'auto';
            input.style.height = input.scrollHeight + 'px';
          });

          cancelBtn.onclick = function () { composer.remove(); };

          postBtn.onclick = function () {
            var text = input.value.trim();
            if (!text) return;
            Repo.addicomment(threadId, {
              author: 'You',
              avatar: 'https://i.pravatar.cc/40?img=12',
              content: text,
              createdAt: new Date()
            });
            composer.remove();
            rendericomments();
          };

          // Auto-focus + scroll
          (function () {
            try {
              input.focus();
              composer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } catch (e) {}
          })();
        }
      });

      // Toolbar button
      editor.ui.addButton('icomment', {
        label: 'Add icomment',
        command: 'addicommentThread',
        toolbar: 'insert',
        icon: this.path + 'icons/icomment.png'
      });
    }
  });

  /* --------------------------------------------------------------------------
  * CKEditor Plugin Definition End
  * ------------------------------------------------------------------------ */
})();
