
(function () {
  if (!window.TrackCfg) {
    window.TrackCfg = {
      isEnabled: function () { return true; },
      isReadonly: function () { return false; },
      isReview: function () { return false; },
      author: function () { return { id: 'anonymous', name: 'Anonymous', avatar: 'https://i.pravatar.cc/40?img=12' }; }
    };
  }
  // Suggest mode state store
  if (!window.TrackCfg._suggest) window.TrackCfg._suggest = Object.create(null);

  if (!window.TrackCfg.isSuggest) {
    window.TrackCfg.isSuggest = function (editor) {
      var id = editor && editor.id;
      return !!(id && window.TrackCfg._suggest[id]);
    };
  }
  if (!window.TrackCfg.setSuggest) {
    window.TrackCfg.setSuggest = function (editor, on) {
      var id = editor && editor.id;
      if (!id) return;
      window.TrackCfg._suggest[id] = !!on;
      // try { localStorage.setItem('tc:suggest:' + id, on ? '1' : '0'); } catch (e) {}
      if (editor) editor.fire && editor.fire('tcSuggestToggled', { on: !!on });
    };
  }
  if (!window.TrackCfg.getInitialSuggest) {
    window.TrackCfg.getInitialSuggest = function (editor) {
      try { return localStorage.getItem('tc:suggest:' + editor.id) === '1'; }
      catch (e) { return false; }
    };
  }
})();


(function () {
  'use strict';
  window.CurrentUser = {
    id: 'u2',
    name: 'Alice Johnson'
  };
  window.DocumentAuthor = {
    id: 'c2',
    name: 'Monica Smith'
  };


  /* --------------------------------------------------------------------------
   * Sidebar Rendering
   * ------------------------------------------------------------------------ */
  function ensureSidebar(editor) {
    var containerSelector = editor.config.commentsContainer || 'body';
    var container = document.querySelector(containerSelector);
    if (!container) {
     // console.warn('Comments plugin: container not found, using <body>.');
      container = document.body;
    }
    
    var panel = container.querySelector('#comments-panel');
    if (panel) return panel;

    var wrapper = document.createElement('div');
    wrapper.className = 'comments-wrapper';
    wrapper.innerHTML = '<div id="comments-panel" class="comments-panel h-100" style="overflow-y:auto;"></div>';
    container.appendChild(wrapper);
    return wrapper.querySelector('#comments-panel');
  }

  function renderReplyCard(thread, parentComment, reply) {
    var node = document.createElement('div');
    node.className = 'previous-comment';

    node.dataset.uid = reply.id || ('r_' + Date.now().toString(36));
    node.dataset.color = thread.color || '#ffe066';
    node.style.borderLeft = '4px solid ' + (thread.color || '#ffe066');

    node.innerHTML = `
      <div class="comment-header d-flex justify-content-between align-items-start">
        <div class="avatar-name d-flex align-items-center gap-2">
          <img src="${reply.avatar || 'https://i.pravatar.cc/40'}" class="rounded-circle" width="28" height="28">
          <div class="c-title fw-semibold">${reply.author || 'Anonymous'}</div>
          <span class="color-dot" style="background:${thread.color || '#ffe066'}"></span>
          <div class="c-time small text-muted">1m</div>
        </div>
      </div>
      <div class="reply-to small text-muted mb-1">Replying to ${parentComment.author || 'someone'}</div>
      <div class="comment-content">${reply.content || ''}</div>
      <div class="replies ms-2 mt-1"></div>
    `;
    return node;
  }

  function renderComments() {
    var panel = document.getElementById('comments-panel');
    if (!panel || typeof Repo === 'undefined') return;
    panel.innerHTML = '';

    if (typeof Repo.refreshThreads === 'function') Repo.refreshThreads();

    Repo.getAllThreads().forEach(function (thread) {
      // Newest-first for top-level comments
      var comments = (thread.comments || []).slice().reverse();

      comments.forEach(function (comment) {
        var node = document.createElement('div');
        node.className = 'previous-comment';
        node.dataset.uid = comment.id;
        node.dataset.thread = thread.id;
        node.dataset.color = thread.color || '#ffe066';
        node.style.borderLeft = '4px solid ' + (thread.color || '#ffe066');

        node.classList.add(thread.type === 'suggestion' ? 'isuggest' : 'icomment');

        if (thread.status === 'approved') node.classList.add('approved');
        else if (thread.status === 'denied') node.classList.add('denied');

        /* Detect if this thread belongs to a Track Change */
        var isChangeThread = false;
        try {
          // Check if editor exists and thread corresponds to any inline change span
          if (
            typeof CKEDITOR !== 'undefined' &&
            CKEDITOR.instances &&
            CKEDITOR.instances['docushield-editor']
          ) {
            var ed = CKEDITOR.instances['docushield-editor'];
            isChangeThread = !!(
              ed.document &&
              ed.document.findOne('span.ckc-change[data-ckc-thread="' + thread.id + '"]')
            );
          }
        } catch (e) {}

        /* Conditionally render Approve/Deny as inline icons if in review mode on a change thread */
        var inReview = (
          window.TrackCfg &&
          typeof TrackCfg.isReview === 'function' &&
          TrackCfg.isReview(CKEDITOR.instances['docushield-editor'])
        );

        var reviewActionsHtml = '';
        var actionsHtml = '';

        if (inReview && isChangeThread) {
          // Inline Approve / Deny icons + a small dropdown with just "Reply"
          reviewActionsHtml = `
            <div class="change-actions d-flex align-items-center gap-2">
              <button type="button"
                      class="btn btn-sm btn-link text-success p-0"
                      data-action="approve"
                      data-thread="${thread.id}"
                      title="Approve"
                      aria-label="Approve">
                <i class="bi bi-check-circle" style="font-size:1.1rem;"></i>
              </button>
              <button type="button"
                      class="btn btn-sm btn-link text-danger p-0"
                      data-action="deny"
                      data-thread="${thread.id}"
                      title="Deny"
                      aria-label="Deny">
                <i class="bi bi-x-circle" style="font-size:1.1rem;"></i>
              </button>

              <div class="dropdown">
                <a href="#" class="text-secondary c-action" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More">
                  <i class="bi bi-three-dots"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item"
                      href="#"
                      data-action="reply"
                      data-thread="${thread.id}"
                      data-comment="${comment.id}">Reply</a>
                  </li>
                </ul>
              </div>
            </div>`;
        } else {
          // Regular comments: keep Reply/Edit/Delete in dropdown
          // Determine permissions
          var canEditOrDelete = false;
          try {
            const curId = window.CurrentUser?.id || '';
            const docOwnerId = window.DocumentAuthor?.id || '';
            const commentAuthorName = (comment.author || '').trim().toLowerCase();

            // If comment has an ID field, prefer that (we'll fall back to name matching)
            const commentAuthorId = comment.authorId || comment.userId || '';

            canEditOrDelete =
              (curId && commentAuthorId && curId === commentAuthorId) ||
              (curId && docOwnerId && curId === docOwnerId) ||
              (commentAuthorName && commentAuthorName === (window.CurrentUser?.name || '').trim().toLowerCase());
          } catch (e) { console.warn('Permission check failed', e); }

          actionsHtml = `
            <div class="dropdown">
              <a href="#" class="text-secondary c-action" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More">
                <i class="bi bi-three-dots"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" data-action="reply" data-thread="${thread.id}" data-comment="${comment.id}">Reply</a></li>
                ${canEditOrDelete ? `
                <li><a class="dropdown-item" href="#" data-action="edit" data-thread="${thread.id}" data-comment="${comment.id}">Edit</a></li>
                <li><a class="dropdown-item" href="#" data-action="delete" data-thread="${thread.id}" data-comment="${comment.id}">Delete</a></li>
                ` : ''}
              </ul>
            </div>`;
        }
        node.innerHTML = `
          <div class="comment-header d-flex justify-content-between align-items-start">
            <div class="avatar-name d-flex align-items-center gap-2">
              <img src="${comment.avatar || 'https://i.pravatar.cc/40'}" class="rounded-circle" width="32" height="32" alt="">
              <div class="c-title fw-semibold">${comment.author || 'Anonymous'}</div>
              <span class="color-dot" style="background:${thread.color || '#ffe066'}"></span>
              <div class="c-time small text-muted">1m</div>
            </div>

            <div class="comment-actions ms-auto d-flex align-items-center">
              ${inReview && isChangeThread ? reviewActionsHtml : actionsHtml}
            </div>
          </div>

          <div class="comment-content">${comment.content || ''}</div>
          <div class="replies ms-2 mt-1"></div>
        `;


        // Replies as mini comment cards (optional newest-first)
        var repliesContainer = node.querySelector('.replies');
        var replies = (comment.replies || []).slice().reverse();
        replies.forEach(function (reply) {
          var rCard = renderReplyCard(thread, comment, reply);
          repliesContainer.appendChild(rCard);
        });


        /* Add remarks toggle if thread has stored remarks */
        if (Array.isArray(thread.remarks) && thread.remarks.length > 0) {
          const remarksBox = document.createElement('div');
          remarksBox.className = 'remarks-box';

          // render all remarks (newest first)
          const remarksList = thread.remarks
            .slice().reverse()
            .map(r => {
              const date = new Date(r.at).toLocaleString(undefined, {
                dateStyle: 'medium',
                timeStyle: 'short'
              });
              const icon = r.action === 'approved' ? '✅' : (r.action === 'denied' ? '❌' : '💬');
              const by = r.by ? ` by ${r.by}` : '';
              return `
                <div class="remark-entry">
                  <div class="remark-line">
                    ${icon} <span class="remark-action">${r.action}</span>${by}
                  </div>
                  <div class="remark-text">${r.text || ''}</div>
                  <div class="remark-time small text-muted">${date}</div>
                </div>
              `;
            })
            .join('');

          remarksBox.innerHTML = remarksList;

          const toggleBtn = document.createElement('button');
          toggleBtn.className = 'remarks-toggle';
          toggleBtn.innerHTML = '<i class="bi bi-info-circle"></i>';
          toggleBtn.title = 'Show remarks history';

          toggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            remarksBox.classList.toggle('open');
            toggleBtn.classList.toggle('active');

            // Flash editor highlight
            if (remarksBox.classList.contains('open')) {
              const span = document.querySelector(`[data-ckc-thread="${thread.id}"]`);
              if (span) {
                span.classList.add('tc-flash');
                setTimeout(() => span.classList.remove('tc-flash'), 1500);
              }
            }
          });

          node.appendChild(remarksBox);
          node.appendChild(toggleBtn);
        }
        panel.prepend(node);
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
        var commentId = btn.dataset.comment;
        var commentEl = btn.closest('.previous-comment');

        // ensure only one composer at a time in panel
        panel.querySelectorAll('.comment-footer').forEach(function (f) { f.remove(); });

        var author = (commentEl.querySelector('.c-title') || {}).textContent || '';
        var composer = document.createElement('div');
        composer.className = 'comment-footer';
        composer.innerHTML = `
          <div class="comment-reply d-flex align-items-start gap-2 my-2">
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
        var repliesBlock = commentEl.querySelector('.replies');
        commentEl.insertBefore(composer, repliesBlock);

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
          Repo.addReply(threadId, commentId, {
            author: 'You',
            avatar: 'https://i.pravatar.cc/40?img=12',
            content: text,
            createdAt: new Date()
          });
          composer.remove();
          renderComments();
        };

        smoothFocus(input);
      };
    });

    // Edit (top-level comments)
    panel.querySelectorAll('[data-action="edit"]').forEach(function (btn) {
      btn.onclick = function (e) {
        e.preventDefault();
        var threadId = btn.dataset.thread;
        var commentId = btn.dataset.comment;
        var commentEl = btn.closest('.previous-comment');
        var contentEl = commentEl.querySelector('.comment-content');
        var oldText = contentEl.textContent;

        contentEl.classList.add('d-none');

        var editBox = document.createElement('div');
        editBox.className = 'comment-footer mt-2';
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
            Repo.editComment(threadId, commentId, newVal);
            renderComments();
          } else {
            // no change -> restore view
            editBox.remove();
            contentEl.classList.remove('d-none');
          }
        };
      };
    });

    // Delete (top-level comments)
    panel.querySelectorAll('[data-action="delete"]').forEach(function (btn) {
      btn.onclick = function (e) {
        e.preventDefault();
        var threadId = btn.dataset.thread;
        var commentId = btn.dataset.comment;
        if (confirm('Delete this comment?')) {
          Repo.deleteComment(threadId, commentId);
          
          removeThreadHighlight(threadId);
          
          if (Repo.deleteThread) Repo.deleteThread(threadId);

          renderComments();
        }
      };
    });

    // Helper: resolve the CKEditor instance (adjust the ID if yours differs)
    function resolveEditor() {
      // Preferred: known instance id
      if (CKEDITOR.instances['docushield-editor']) return CKEDITOR.instances['docushield-editor'];
      // Fallback: focused editor
      if (CKEDITOR.currentInstance) return CKEDITOR.currentInstance;
      // Last resort: first available instance
      var keys = Object.keys(CKEDITOR.instances || {});
      return keys.length ? CKEDITOR.instances[keys[0]] : null;
    }

    // Approve/Deny handlers for change-threads
    panel.querySelectorAll('[data-action="approve"]').forEach(function (btn) {
      btn.onclick = function (e) {
        e.preventDefault();
        var threadId = btn.dataset.thread;
        var editor = resolveEditor();
        if (!editor) {
         // console.warn('[TrackActions] No CKEditor instance found for approve.');
          return;
        }
        if (window.TrackActions && typeof TrackActions.approve === 'function') {
          TrackActions.approve(editor, threadId);
          renderComments();
        }
      };
    });

    panel.querySelectorAll('[data-action="deny"]').forEach(function (btn) {
      btn.onclick = function (e) {
        e.preventDefault();
        var threadId = btn.dataset.thread;
        var editor = resolveEditor();
        if (!editor) {
          // console.warn('[TrackActions] No CKEditor instance found for deny.');
          return;
        }
        if (window.TrackActions && typeof TrackActions.deny === 'function') {
          TrackActions.deny(editor, threadId);
          renderComments();
        }
      };
    });
  }


  function getActiveEditor() {
    if (CURRENT_EDITOR_ID && CKEDITOR.instances[CURRENT_EDITOR_ID]) {
      return CKEDITOR.instances[CURRENT_EDITOR_ID];
    }

    // fallback: pick first if nothing focused
    return Object.values(CKEDITOR.instances)[0] || null;
  }
  function removeThreadHighlight(threadId) {
    try {
      // Try to get the active CKEditor instance
      const editor = getActiveEditor();
      if (!editor || !editor.document) {
       // console.warn('No active editor instance found while removing highlight.');
        return;
      }

      // Find the highlighted span
      const span = editor.document.findOne(`span[data-ckc-thread="${threadId}"]`);
      if (span) {
        // Unwrap the span safely (replace span with its inner text)
        const textNode = new CKEDITOR.dom.text(span.getText(), editor.document);
        span.insertBeforeMe(textNode);
        span.remove();
      }
    } catch (err) {
     // console.warn('Error removing highlight for thread', threadId, err);
    }
  }

  // Replacement: wrap selection as a suggestion using extract+insert (CKEditor 4-safe)
  function wrapSelectionForSuggestion(editor, threadId, color) {
    var sel = editor.getSelection();
    if (!sel) return false;

    var ranges = sel.getRanges();
    if (!ranges.length) return false;

    var range = ranges[0];
    if (range.collapsed) return false;

    // Build the wrapper
    var span = new CKEDITOR.dom.element('span', editor.document);
    span.setAttributes({
      'data-ckc-thread': threadId,
      'data-ckc-change': 'insert'
    });
    span.addClass('ckc-change');
    span.addClass('ckc-insert');
    if (color) span.setStyle('background-color', color + '33');

    // Take the selected contents out, move them into the span, and reinsert span
    editor.fire && editor.fire('saveSnapshot');

    var frag = range.extractContents(); // CKEDITOR.dom.documentFragment
    if (frag) {
      var child;
      while ((child = frag.getFirst())) {
        span.append(child);
      }
    } else {
      // Fallback: use plain text if fragment missing (edge cases)
      span.setText(sel.getSelectedText() || '');
    }

    range.insertNode(span);

    // Reselect the new element for nice UX
    sel.selectElement(span);

    editor.fire && editor.fire('change');
    return true;
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

  let CURRENT_EDITOR_ID = null;

  CKEDITOR.on('instanceReady', function (evt) {
    const ed = evt.editor;

    // Track focus
    ed.on('focus', function () {
      CURRENT_EDITOR_ID = ed.name; // "docushield-editor" etc.
    });

    // Optional: clear on blur
    ed.on('blur', function () {
    });
  });

  /* --------------------------------------------------------------------------
   * CKEditor Plugin Definition
   * ------------------------------------------------------------------------ */
 

  CKEDITOR.plugins.add('comments', {
    icons: 'comment',
    init: function (editor) {
      editor.config.extraAllowedContent =
        (editor.config.extraAllowedContent || '') +
        '; span[data-ckc-thread](ckc-anchor)';

      // Auto-load CSS (editor + page)
      editor.addContentsCss(this.path + 'styles.css');
      if (!document.getElementById('comments-plugin-css')) {
        var link = document.createElement('link');
        link.id = 'comments-plugin-css';
        link.rel = 'stylesheet';
        link.href = this.path + 'styles.css';
        document.head.appendChild(link);
      }

 // =========================================
// GLOBAL — Track last delete span
// =========================================
var lastDeleteSpan = null;

// Hard reset function
function resetDeleteSpan(force = false) {
    // force true: always reset
    if (force) {
        lastDeleteSpan = null;
        return;
    }

    // Otherwise reset only when cursor is OUTSIDE delete span
    var sel = editor.getSelection();
    if (!sel) return;

    var range = sel.getRanges()[0];
    if (!range) return;

    var node = range.startContainer;
    var parent = node.getAscendant("span", true);

    if (!parent || !parent.hasClass("ckc-delete")) {
        lastDeleteSpan = null;
    }
}

// =========================================
// EVENTS THAT RESET DELETE MODE
// =========================================

// Mouse click (even multiple clicks)
editor.on("click", function () {
    setTimeout(() => resetDeleteSpan(true), 5);
});

// Keyboard cursor movement
editor.on("key", function (evt) {
    // cursor movement keys
    if ([9, 13, 27, 32, 33, 34, 35, 36, 37, 38, 39, 40].includes(evt.data.keyCode)) {
        resetDeleteSpan(true);
    }
});

// Selection change handling (mouse drag, cursor collapse etc)
editor.on("selectionChange", function () {
    var sel = editor.getSelection();
    if (!sel) return;
    var range = sel.getRanges()[0];
    if (!range) return;

    var node = range.startContainer;
    var parentSpan = node.getAscendant("span", true);

    // If cursor is NOT inside strike span, reset
    if (!parentSpan || !parentSpan.hasClass("ckc-delete")) {
        resetDeleteSpan();
    }
});

// =========================================
// HELPER — Find previous text node (across lines)
// =========================================
function getPrevTextNode(node) {
    var prev = node.getPrevious();
    if (prev) {
        if (prev.type === CKEDITOR.NODE_TEXT) return prev;
        var lastChild = prev.getLast();
        while (lastChild && lastChild.type !== CKEDITOR.NODE_TEXT) {
            lastChild = lastChild.getLast();
        }
        return lastChild;
    } else {
        var parent = node.getParent();
        if (!parent || parent.equals(editor.editable())) return null;
        return getPrevTextNode(parent);
    }
}

// =========================================
// MAIN DELETE LOGIC
// =========================================
editor.on("key", function (evt) {

    if (evt.data.keyCode !== 8 && evt.data.keyCode !== 46) return;

    var sel = editor.getSelection();
    if (!sel) return;

    var range = sel.getRanges()[0];
    if (!range) return;

    var isSuggestActive = window.TrackCfg &&
        typeof window.TrackCfg.isSuggest === "function" &&
        window.TrackCfg.isSuggest(editor);

    if (!isSuggestActive) return;

    // =============================
    // CASE 1 — Collapsed cursor delete
    // =============================
    if (range.collapsed) {

        evt.cancel();

        var node = range.startContainer;
        var offset = range.startOffset;

        if (node.type !== CKEDITOR.NODE_TEXT) {
            node = node.getFirst();
            while (node && node.type !== CKEDITOR.NODE_TEXT)
                node = node.getFirst();
            if (!node) return;
            offset = 0;
        }

        var text = node.getText();
        if (!text.length) return;

        var targetIndex = offset;

        // BACKSPACE
        if (evt.data.keyCode === 8) {
            if (offset === 0) {
                var prevNode = getPrevTextNode(node);
                if (prevNode) {
                    var newRange = editor.createRange();
                    newRange.setStart(prevNode, prevNode.getText().length);
                    newRange.setEnd(prevNode, prevNode.getText().length);
                    sel.selectRanges([newRange]);
                    evt.cancel();
                    return;
                }
                return;
            } else {
                targetIndex = offset - 1;
            }
        }

        // DELETE
        if (evt.data.keyCode === 46) {
            if (offset >= text.length) return;
            targetIndex = offset;
        }

        var delChar = text.charAt(targetIndex);
        var before = text.substring(0, targetIndex);
        var after = text.substring(targetIndex + 1);

        node.setText(before);

        // =============================
        // FIX — Determine correct span (avoid merge bugs)
        // =============================
        var deleteSpan = null;

        // If cursor already inside a strike span → keep using it
        var parentSpan = node.getAscendant("span", true);
        if (parentSpan && parentSpan.hasClass("ckc-delete")) {
            deleteSpan = parentSpan;
            lastDeleteSpan = deleteSpan;
        } else if (lastDeleteSpan) {
            var prev = node.getPrevious();
            var next = node.getNext();
            if ((prev && prev.$ === lastDeleteSpan.$) || (next && next.$ === lastDeleteSpan.$)) {
              deleteSpan = lastDeleteSpan;
            }
        }

        // Otherwise create new span
        if (!deleteSpan) {
            deleteSpan = CKEDITOR.dom.element.createFromHtml(
                '<span class="ckc-change ckc-delete" style="text-decoration: line-through;"></span>'
            );
            deleteSpan.insertAfter(node);
            lastDeleteSpan = deleteSpan;
        }

        // Update span text
        if (evt.data.keyCode === 8) {
            deleteSpan.setHtml(delChar + deleteSpan.getHtml());
        } else {
            deleteSpan.setHtml(deleteSpan.getHtml() + delChar);
        }

        var afterNode = null;
        if (after.length > 0) {
            afterNode = new CKEDITOR.dom.text(after);
            afterNode.insertAfter(deleteSpan);
        }

        // Cursor handling
        var newRange = editor.createRange();

        if (evt.data.keyCode === 8) {
            newRange.setStart(node, before.length);
            newRange.setEnd(node, before.length);
        } else {
            if (afterNode) newRange.setStart(afterNode, 0);
            else newRange.moveToPosition(deleteSpan, CKEDITOR.POSITION_AFTER_END);
        }

        sel.selectRanges([newRange]);
        editor.fire("change");
        return;
    }

    // =============================
    // CASE 2 — Selected block delete
    // =============================
     // =============================
      evt.cancel();
      resetDeleteSpan(true);

      var isBackspace = (evt.data.keyCode === 8);

      // Extract selection content and wrap it in strike span
      var frag = range.extractContents();
      var span = new CKEDITOR.dom.element("span");
      span.setAttribute("class", "ckc-change ckc-delete");
      span.setStyle("text-decoration", "line-through");
      span.append(frag);
      range.insertNode(span);

      var newRange = editor.createRange();

      // ===== HELPERS to avoid jump =====
      function getNearestSafeLeft(span) {
          var p = span;
          while (p) {
              let prev = p.getPrevious();
              if (prev) {
                  if (prev.type === CKEDITOR.NODE_TEXT) return { node: prev, offset: prev.getText().length };
                  if (prev.type === CKEDITOR.NODE_ELEMENT) {
                      let t = prev.getLast();
                      if (t && t.type === CKEDITOR.NODE_TEXT)
                        return { node: t, offset: t.getText().length };
                  }
                  p = prev;
              } else {
                  p = p.getParent();
                  if (!p || p.equals(editor.editable())) return null;
              }
          }
          return null;
      }

      function getNearestSafeRight(span) {
          var p = span;
          while (p) {
              let next = p.getNext();
              if (next) {
                  if (next.type === CKEDITOR.NODE_TEXT) return { node: next, offset: 0 };
                  if (next.type === CKEDITOR.NODE_ELEMENT) {
                      let t = next.getFirst();
                      if (t && t.type === CKEDITOR.NODE_TEXT)
                          return { node: t, offset: 0 };
                  }
                  p = next;
              } else {
                  p = p.getParent();
                  if (!p || p.equals(editor.editable())) return null;
              }
          }
          return null;
      }

      // ===== BACKSPACE cursor LEFT =====
      if (isBackspace) {
          var prevNode = getNearestSafeLeft(span);

          if (prevNode) {
              newRange.setStart(prevNode.node, prevNode.offset);
              newRange.setEnd(prevNode.node, prevNode.offset);
          } else {
              // last fallback (still prevents jump)
              newRange.moveToPosition(span, CKEDITOR.POSITION_BEFORE_START);
          }
      }

      // ===== DELETE cursor RIGHT =====
      else {
          var nextNode = getNearestSafeRight(span);

          if (nextNode) {
              newRange.setStart(nextNode.node, nextNode.offset);
              newRange.setEnd(nextNode.node, nextNode.offset);
          } else {
              newRange.moveToPosition(span, CKEDITOR.POSITION_AFTER_END);
          }
      }

      sel.selectRanges([newRange]);
      lastDeleteSpan = null;
      editor.fire("change");
      });

  // =========================================
  // FIX — EXIT DELETE SPAN ON DOUBLE SPACE
  // =========================================
    editor.on("key", function (evt) {
        var sel = editor.getSelection();
        if (!sel) return;

        var range = sel.getRanges()[0];
        if (!range) return;

        var node = range.startContainer;
        var offset = range.startOffset;

        if (!node) return;

        // If cursor is inside a deleted span
        var parentSpan = node.getAscendant("span", true);
        if (parentSpan && parentSpan.hasClass("ckc-delete")) {
            // Only exit if cursor is at the end of span
            if (offset === node.getText().length) {
                var newRange = editor.createRange();
                newRange.moveToPosition(parentSpan, CKEDITOR.POSITION_AFTER_END);
                sel.selectRanges([newRange]);
                lastDeleteSpan = null; // reset
            }
        }
    });

    // ... existing code ...

      // Include Track-Changes styles inside the editor iframe
      editor.addContentsCss(this.path + 'inline/trackchanges.styles.css');
      if (!document.getElementById('tc-styles')) {
        var tcLink = document.createElement('link');
        tcLink.id = 'tc-styles';
        tcLink.rel = 'stylesheet';
        tcLink.href = this.path + 'inline/trackchanges.styles.css';
        document.head.appendChild(tcLink);
      }
      // Auto-load Repo
      var repoType = editor.config.commentsRepo || 'api'; // 'local' | 'api'
      var repoPath = this.path + 'repo.' + repoType + '.js';

      // --------------------------------------------------------------------------
      // Wait until Repo is ready (avoid race conditions)
      // --------------------------------------------------------------------------
      function waitForRepoReady(callback, attempts = 0) {
        if (window.Repo && typeof Repo.getAllThreads === 'function') {
          callback();
        } else if (attempts < 50) {
          setTimeout(() => waitForRepoReady(callback, attempts + 1), 100);
        } else {
          // console.error('[CommentsPlugin] Repo did not initialize after waiting.');
        }
      }

      function startOnceRepoLoaded() {
        waitForRepoReady(function () {
          const docId = editor.config.documentId || 'defaultDoc';
          Repo.init(docId);

          const savedContent = Repo.getContent && Repo.getContent();
          if (savedContent && savedContent.trim().length > 0) {
            editor.setData(savedContent);
           // console.log(`Restored saved content for ${docId}`);
          }

          // Debounce helper (avoid excessive writes)
          function debounce(fn, delay) {
            let timeout;
            return function (...args) {
              clearTimeout(timeout);
              timeout = setTimeout(() => fn.apply(this, args), delay);
            };
          }

          // Debounced auto-save (every 2s after user stops typing)
          const saveContentDebounced = debounce(function () {
            const content = editor.getData();
            Repo.setContent(content);
           // console.log(`Auto-saved content for ${docId} (${content.length} chars)`);
          }, 1000);

          // Watch for content changes
          editor.on('change', saveContentDebounced);

          // also save when comment data changes
          const repoEventsToSave = ['commentAdded', 'replyAdded', 'commentEdited', 'commentDeleted'];
          repoEventsToSave.forEach(evt => {
            Repo.on(evt, function () {
              try {
                // For commentDeleted, wait a short delay to allow highlight cleanup
                const delay = evt === 'commentDeleted' ? 250 : 0;
                setTimeout(() => {
                  const content = editor.getData();
                  Repo.setContent(content);
                //  console.log(`Saved after ${evt} (${content.length} chars, delay=${delay}ms)`);
                }, delay);
              } catch (err) {
                // console.warn(`[CommentsPlugin] Failed to save after ${evt}`, err);
              }
            });
          });

        
          ensureSidebar(editor);
          renderComments();

          // Initialize the pill once the editor + UI are up
          if (window.SuggestPill && typeof SuggestPill.init === 'function') {
            SuggestPill.init(editor);
          } else {
            // console.warn('[Comments] SuggestPill not present');
          }

        });

        editor.on('instanceReady', function () {
          if (window.SuggestPill && SuggestPill.init) SuggestPill.init(editor);
        });


        // =========================================================================
        // Click inside editor highlight → open sidebar (if closed) → scroll + flash
        // =========================================================================
        editor.on('contentDom', function () {
          var editable = editor.editable();

          editable.attachListener(editable, 'click', function (evt) {
            var target = evt.data.getTarget();
            if (!target) return;

            // Find closest thread anchor
            var anchorEl = target.$.closest
              ? target.$.closest('[data-ckc-thread]')
              : (target.$.getAttribute && target.$.getAttribute('data-ckc-thread') ? target.$ : null);

            if (!anchorEl) return;

            var threadId = anchorEl.getAttribute('data-ckc-thread');
            if (!threadId) return;

            // === STEP 1: Ensure sidebar is open ===
            var docBody = document.querySelector('.doc-body');
            var toggleBtn = document.getElementById('comments-toggle');

            if (docBody && !docBody.classList.contains('comments-open')) {
              // Toggle it open (simulate your normal behavior)
              docBody.classList.add('comments-open');
              if (toggleBtn) toggleBtn.classList.add('active'); // optional sync

              // If your UI uses JS to handle layout changes when toggled, you can trigger it too
              // e.g., toggleBtn.click();
            }

            // === STEP 2: Wait for CSS transition to finish ===
            setTimeout(function () {
              var commentEl = document.querySelector(
                '#comments-panel .previous-comment[data-thread="' + threadId + '"]'
              );
              if (commentEl) {
                commentEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                commentEl.classList.add('flash');
                setTimeout(() => commentEl.classList.remove('flash'), 1500);
              }
            }, 350); // match your sidebar slide transition
          });
        });
        // =========================================================================
        // Cross-link Highlights <-> Sidebar (for approve/deny + comments + suggest)
        // =========================================================================
        editor.on('contentDom', function () {
          var editable = editor.editable();

          // --- CLICK inside editor highlight → scroll & flash sidebar comment
          editable.attachListener(editable, 'click', function (evt) {
            var target = evt.data.getTarget();
            if (!target) return;

            // Accept any change type anchor (.ckc-anchor, .tc-replaced, .tc-denied, etc.)
            var anchorEl = target.$.closest
              ? target.$.closest('[data-ckc-thread]')
              : (target.$.getAttribute && target.$.getAttribute('data-ckc-thread') ? target.$ : null);

            if (!anchorEl) return;

            var threadId = anchorEl.getAttribute('data-ckc-thread');
            if (!threadId) return;

            // Find the corresponding comment in sidebar
            var commentEl = document.querySelector('#comments-panel .previous-comment[data-thread="' + threadId + '"]');
            if (commentEl) {
              commentEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
              commentEl.classList.add('flash');
              setTimeout(() => commentEl.classList.remove('flash'), 1500);
            }
          });
        });


        // --- CLICK in sidebar comment → flash the editor anchor
        document.addEventListener('click', function (e) {
          var el = e.target.closest('.previous-comment[data-thread]');
          if (!el) return;

          var threadId = el.getAttribute('data-thread');
          if (!threadId) return;

          // Find highlight inside editor iframe
          if (window.CKEDITOR) {
            Object.values(CKEDITOR.instances).forEach(function (inst) {
              try {
                var doc = inst.document && inst.document.$;
                if (!doc) return;
                var target = doc.querySelector('[data-ckc-thread="' + threadId + '"]');
                if (target) {
                  target.classList.add('tc-flash');
                  target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                  setTimeout(() => target.classList.remove('tc-flash'), 1500);
                }
              } catch (err) {}
            });
          }
        });

        Repo.on('commentAdded', renderComments);
        Repo.on('replyAdded', renderComments);
        Repo.on('commentEdited', renderComments);
        Repo.on('commentDeleted', renderComments);
        Repo.on('repoLoaded', renderComments);

        // Auto-load readonly patch if needed
        if (editor.config.reviewMode) {
          var roScript = document.createElement('script');
          roScript.src = CKEDITOR.plugins.getPath('comments') + 'comments.readonly.js';
          document.head.appendChild(roScript);
        }
      }

      // --------------------------------------------------------------------------
      // Auto-load dependencies (UserColors + Repo + optional Track Changes)
      // --------------------------------------------------------------------------
      function loadScriptSequentially(sources, callback) {
        if (!sources.length) return callback && callback();
        var src = sources.shift();
        if (document.querySelector('script[src="' + src + '"]')) {
          // already loaded → continue
          loadScriptSequentially(sources, callback);
          return;
        }
        var s = document.createElement('script');
        s.src = src;
        s.onload = function () {
          // console.log('[CommentsPlugin] Loaded:', src);
          loadScriptSequentially(sources, callback);
        };
        s.onerror = function () {
          // console.error('[CommentsPlugin] Failed to load:', src);
          loadScriptSequentially(sources, callback);
        };
        document.head.appendChild(s);
      }

      if (typeof Repo === 'undefined') {
        var basePath = this.path; // CKEditor plugin base
        var userColorsPath = basePath + 'userColors.js';
        var repoPath = basePath + 'repo.' + repoType + '.js';

        // --------------------------------------------------------------------------
        // Always auto-load Track Changes (inline module)
        // --------------------------------------------------------------------------
        var tcBase = basePath + 'inline/';

        // If user didn't define trackChanges config, create default one
        if (!editor.config.trackChanges) {
          editor.config.trackChanges = {
            enabled: true,
            mode: 'review', // default mode
            // author: { id: 'auto', name: 'Anonymous', avatar: 'https://i.pravatar.cc/40?img=12' },
            author: {
              id: window.CurrentUser?.id || 'anonymous',
              name: window.CurrentUser?.name || 'Anonymous',
              avatar: window.CurrentUser?.avatar || 'https://i.pravatar.cc/40?img=12'
            },
            ownerCanDecide: true
          };
        }

        // Unless explicitly disabled, always load inline scripts
        var tcConfig = editor.config.trackChanges;
        var tcEnabled = tcConfig.enabled !== false && tcConfig.mode !== 'off';
        var mentionsPath = basePath + 'mentions.js';
        var notificationsPath = basePath + 'notifications.js';
        var suggestPillPath = basePath + 'suggestpill.js';
        // optional Track Changes (inline folder)
        var sources = [userColorsPath, repoPath, mentionsPath, notificationsPath, suggestPillPath];

        if (tcEnabled) {
          var tcBase = basePath + 'inline/';
          sources.push(tcBase + 'trackchanges.config.js');
          sources.push(tcBase + 'trackchanges.core.js');
          sources.push(tcBase + 'trackchanges.actions.js');

          // Load styles once
          if (!document.getElementById('tc-styles')) {
            var tcLink = document.createElement('link');
            tcLink.id = 'tc-styles';
            tcLink.rel = 'stylesheet';
            tcLink.href = tcBase + 'trackchanges.styles.css';
            document.head.appendChild(tcLink);
          }
        }

        loadScriptSequentially(sources, startOnceRepoLoaded);
      } else {
        startOnceRepoLoaded();
      }

      // Command to add a new thread
      editor.addCommand('addSuggestionThread', {
        exec: function (ed) {
          if (typeof Repo === 'undefined') return;

          // --- Selection (same pattern as addCommentThread) -----------------------
          const sel = ed.getSelection();
          if (!sel) return;

          const range = sel.getRanges()[0];
          if (!range || range.collapsed) return;

          // Save selection so we can restore before wrapping later
          const savedRanges = sel.getRanges(true);

          // Generate new thread ID (same scheme)
          const threadId = 't_' + Date.now().toString(36);

          // Open composer at top of comments panel
          const panel = ensureSidebar(ed);

          // Ensure only one composer exists at a time
          panel.querySelectorAll('.comment-footer').forEach(f => f.remove());

          // Create composer (minor text changes: placeholder + button label)
          const composer = document.createElement('div');
          composer.className = 'comment-footer';
          composer.innerHTML = `
            <div class="comment-reply d-flex align-items-start gap-2 my-2">
              <img src="https://i.pravatar.cc/40?img=12" class="rounded-circle" width="32" height="32">
              <div class="flex-grow-1">
                <textarea class="form-control reply-input" rows="2" placeholder="Describe your suggestion..."></textarea>
                <div class="d-flex justify-content-end gap-2 mt-2">
                  <button type="button" class="btn btn-sm btn-light cancel-comment">Cancel</button>
                  <button type="button" class="btn btn-sm btn-primary post-comment" disabled>Suggest</button>
                </div>
              </div>
            </div>`;
          panel.prepend(composer);

          const input    = composer.querySelector('.reply-input');
          const postBtn  = composer.querySelector('.post-comment');
          const cancelBtn= composer.querySelector('.cancel-comment');

          // Enable/disable Post button + autosize textarea (same as comments)
          input.addEventListener('input', function () {
            postBtn.disabled = input.value.trim().length === 0;
            input.style.height = 'auto';
            input.style.height = input.scrollHeight + 'px';
          });

          cancelBtn.onclick = function () {
            composer.remove();
          };

          postBtn.onclick = function () {
            const text = input.value.trim();
            if (!text) return;

            // Create thread first for suggestions (flagged as change thread)
            // const thread = Repo.createThread({ threadId: threadId, isChangeThread: true, type: 'suggestion' }) || {};
            const thread = Repo.createThread({ threadId: threadId, type: 'suggestion' }) || {};

            const threadColor = thread?.color || '#ffe066';

            // Restore original selection before wrapping
            ed.getSelection().selectRanges(savedRanges);

            // Wrap selection as a *suggestion* (use your suggestion wrapper)  <-- CHANGED
            // In comments you call: wrapSelectionWithAnchor(ed, threadId, threadColor)
            // For suggestions, call your suggestion wrapper:
            const ok = !!wrapSelectionForSuggestion(ed, threadId, threadColor);
            if (!ok) {
              // console.warn('[Comments] Could not wrap selection for suggestion.');
              composer.remove();
              return;
            }

            // Add the actual note to the thread (same as comments)
            Repo.addComment(threadId, {
              author: 'You',
              avatar: 'https://i.pravatar.cc/40?img=12',
              content: text,
              createdAt: new Date()
            });

            composer.remove();
            renderComments();

            // Optional: scroll to the new thread marker (same pattern)
            try {
              const mark = ed.document.findOne('span[data-thread-id="\${threadId}"]');
              if (mark) mark.scrollIntoView();
            } catch (e) {}
          };

          // Auto-focus + scroll into view (same as comments)
          try {
            input.focus();
            composer.scrollIntoView({ behavior: 'smooth', block: 'center' });
          } catch (e) {}
        }
      });

      // Command to add a new *suggestion* thread (mirrors addCommentThread closely)
      editor.addCommand('addCommentThread', {
        exec: function (ed) {
          if (typeof Repo === 'undefined') return;

          const sel = ed.getSelection();
          if (!sel) return;

          const range = sel.getRanges()[0];
          if (!range || range.collapsed) return;

          // Save selection so we can restore before wrapping later
          const savedRanges = sel.getRanges(true);

          // Generate new thread ID
          const threadId = 't_' + Date.now().toString(36);

          // Open composer at top of comments panel
          const panel = ensureSidebar(ed);

          // Ensure only one composer exists at a time
          panel.querySelectorAll('.comment-footer').forEach(f => f.remove());

          // Create composer
          const composer = document.createElement('div');
          composer.className = 'comment-footer';
          composer.innerHTML = `
            <div class="comment-reply d-flex align-items-start gap-2 my-2">
              <img src="https://i.pravatar.cc/40?img=12" class="rounded-circle" width="32" height="32">
              <div class="flex-grow-1">
                <textarea class="form-control reply-input" rows="2" placeholder="Write a comment..."></textarea>
                <div class="d-flex justify-content-end gap-2 mt-2">
                  <button type="button" class="btn btn-sm btn-light cancel-comment">Cancel</button>
                  <button type="button" class="btn btn-sm btn-primary post-comment" disabled>Post</button>
                </div>
              </div>
            </div>`;
          panel.prepend(composer);

          const input = composer.querySelector('.reply-input');
          const postBtn = composer.querySelector('.post-comment');
          const cancelBtn = composer.querySelector('.cancel-comment');

          if (window.DocuMentions && DocuMentions.enableMentionsForTextarea) {
            DocuMentions.enableMentionsForTextarea(input);
          }

          // Enable/disable Post button + autosize textarea
          input.addEventListener('input', function () {
            postBtn.disabled = input.value.trim().length === 0;
            input.style.height = 'auto';
            input.style.height = input.scrollHeight + 'px';
          });

          cancelBtn.onclick = function () {
            composer.remove();
          };

          postBtn.onclick = function () {
            const text = input.value.trim();
            if (!text) return;

            // Create thread only now (on post)
            // const thread = Repo.createThread({ threadId: threadId });
            const thread = Repo.createThread({ threadId: threadId, isChangeThread: true, type: 'comment' }) || {};

            const threadColor = thread?.color || '#ffe066';

            // Restore original selection before wrapping
            ed.getSelection().selectRanges(savedRanges);

            // Highlight the selected text with thread color
            wrapSelectionWithAnchor(ed, threadId, threadColor);

            const raw = input.value.trim();
            if (!raw) return;
            const parsed = window.DocuMentions
              ? DocuMentions.transformMentions(raw)
              : { html: raw, mentions: [] };

            // Add the actual comment to the thread
            Repo.addComment(threadId, {
              author: 'You',
              avatar: 'https://i.pravatar.cc/40?img=12',
              // content: text,
              content: parsed.html,
              mentions: parsed.mentions,
              createdAt: new Date()
            });

            composer.remove();
            renderComments();

            // Optional: scroll to the new thread marker
            try {
              const mark = ed.document.findOne(`span[data-thread-id="${threadId}"]`);
              if (mark) mark.scrollIntoView();
            } catch (e) {}
          };

          // Auto-focus + scroll into view
          try {
            input.focus();
            composer.scrollIntoView({ behavior: 'smooth', block: 'center' });
          } catch (e) {}
        }
      });
      
      // Toolbar button
      editor.ui.addButton('Comment', {
        label: 'Add Comment',
        command: 'addCommentThread',
        toolbar: 'insert',
        icon: this.path + 'icons/comment.png'
      });

      editor.ui.addButton('Suggestion', {
        label: 'Add a Suggestion',
        command: 'addSuggestionThread',
        toolbar: 'insert',
        icon: this.path + 'icons/suggestion.png'
      });

      // editor.ui.addButton('UploadDocument', {
      //   label: 'Upload a Document',
      //   command: 'addUploadDocumentThread',
      //   toolbar: 'insert',
      //   icon: this.path + 'icons/upload_file1.svg'
      // });

      /* --------------------------------------------------------------------------
      * Context-menu integration: “Add Comment”
      * ------------------------------------------------------------------------ */
      if (editor.addMenuGroup) editor.addMenuGroup('commentsGroup');

      if (editor.addMenuItem) {
        editor.addMenuItem('addCommentContext', {
          label: 'Add Comment',
          icon: this.path + 'icons/comment.png',
          command: 'addCommentThread',
          group: 'commentsGroup',
          order: 1
        });
      }

      if (editor.contextMenu) {
        editor.contextMenu.addListener(function () {
          try {
            const sel = editor.getSelection();
            if (!sel || !sel.getSelectedText() || sel.getSelectedText().trim() === '') {
              return null;
            }
            return { addCommentContext: CKEDITOR.TRISTATE_OFF };
          } catch (e) {
            return null;
          }
        });
      }

      if (editor.addMenuItem) {
        editor.addMenuItem('addSuggestionContext', {
          label: 'Add a Suggestion',
          icon: this.path + 'icons/suggestion.png',
          command: 'addSuggestionThread',
          group: 'commentsGroup',
          order: 1
        });
      }

      if (editor.contextMenu) {
        editor.contextMenu.addListener(function () {
          try {
            const sel = editor.getSelection();
            if (!sel || !sel.getSelectedText() || sel.getSelectedText().trim() === '') {
              return null;
            }
            return { addSuggestionContext: CKEDITOR.TRISTATE_OFF };
          } catch (e) {
            return null;
          }
        });
      }

      // Make sure the sidebar exists or create one
      ensureSidebar(editor);

      /* --------------------------------------------
      * Auto-open Comments Sidebar Logic (robust)
      * -------------------------------------------- */
      editor.on('contentDom', function () {
        const openCommentsSidebar = () => {
          const toggleBtn = document.querySelector('#comments-toggle');
          const docBody = document.querySelector('.doc-body');
          const ReplyInput = document.querySelector('.reply-input');

          if (docBody && !docBody.classList.contains('comments-open')) {
            docBody.classList.add('comments-open');
          }

          if (toggleBtn && !toggleBtn.classList.contains('active')) {
            toggleBtn.classList.add('active');
          }

          ReplyInput.focus();
        };

        // Clicking existing comment markers
        editor.document.on('click', function (evt) {
          const el = evt.data.getTarget().$;
          if (el && (el.closest('.cke_comment') || el.classList.contains('comment-marker'))) {
            openCommentsSidebar();
          }
        });

        // Repository events (most reliable trigger)
        if (editor.commentsRepository) {
          editor.commentsRepository.on('commentAdded', openCommentsSidebar);
          editor.commentsRepository.on('commentSelected', openCommentsSidebar);
        }

        // Optional — hook your toolbar button manually
        const toolbarBtn = document.querySelector('.cke_button__comment, .cke_button__addcomment');
        if (toolbarBtn) {
          toolbarBtn.addEventListener('click', openCommentsSidebar);
        }

        editor.on('afterCommandExec', function (evt) {
          // Open sidebar for both comment and suggestion threads
          if (evt.data.name === 'addCommentThread' || evt.data.name === 'addSuggestionThread') {
            // Defer slightly to ensure composer exists before focusing
            CKEDITOR.tools.setTimeout(function () {
              openCommentsSidebar();
            }, 50);
          }
        });
      });
      (function attachSelectionStateControl() {
        // Commands that REQUIRE a non-collapsed text selection
        var SELECTION_REQUIRED_CMDS = ['addCommentThread', 'tcDelete', 'tcReplace'];

        function setCmdEnabled(cmdName, enabled) {
          var cmd = editor.getCommand(cmdName);
          if (!cmd) return;
          cmd.setState(enabled ? CKEDITOR.TRISTATE_OFF : CKEDITOR.TRISTATE_DISABLED);
        }

        function disableSelectionRequired() {
          SELECTION_REQUIRED_CMDS.forEach(function (name) { setCmdEnabled(name, false); });
        }

        function textSelected() {
          try {
            if (editor.readOnly) return false;
            if (editor.mode !== 'wysiwyg') return false;

            var sel = editor.getSelection();
            if (!sel) return false;

            var ranges = sel.getRanges();
            if (!ranges || !ranges.length) return false;

            var r = ranges[0];
            if (!r || r.collapsed) return false;

            // Ensure there's some non-whitespace text (strict mode)
            var t = sel.getSelectedText ? sel.getSelectedText() : '';
            if ((t || '').replace(/\s+/g, '').length === 0) return false;

            return true;
          } catch (e) {
            return false;
          }
        }

        function refreshStates() {
          var enableSelectionCmds = textSelected();

          // Toggle all selection-required commands together
          SELECTION_REQUIRED_CMDS.forEach(function (name) {
            setCmdEnabled(name, enableSelectionCmds);
          });

          // Insert is always enabled
          setCmdEnabled('tcInsert', true);
        }

        // Initial: disable selection-required until something is selected
        editor.on('instanceReady', function () {
          disableSelectionRequired();
          setCmdEnabled('tcInsert', true);
        });

        // Core selection/state hooks
        editor.on('selectionChange', refreshStates);
        editor.on('focus', refreshStates);

        // Be gentle on blur—context menu can cause transient blur
        editor.on('blur', function () {
          setTimeout(function () {
            if (!editor.focusManager || !editor.focusManager.hasFocus) {
              disableSelectionRequired();
            }
          }, 120);
        });

        // When editable DOM is ready, listen inside the iframe
        editor.on('contentDom', function () {
          var edEl = editor.editable();

          // Recompute after mouse selections
          edEl.on('mouseup', refreshStates);

          // Recompute after keyboard selection changes (Shift+Arrows, Ctrl+A, etc.)
          edEl.on('keyup', function () { refreshStates(); });

          // Only pessimistically disable on LEFT-button drag start (not right click)
          edEl.on('mousedown', function (evt) {
            var e = evt.data.$;
            // e.button: 0=left, 1=middle, 2=right
            if (e.button === 0) {
              disableSelectionRequired();
            }
          });

          // Right-click: re-evaluate states just before context menu builds
          edEl.on('contextmenu', function () {
            // Allow caret/selection to settle first
            setTimeout(refreshStates, 0);
          });
        });

        // Respect readOnly and mode flips (source ↔ wysiwyg)
        editor.on('readOnly', refreshStates);
        editor.on('mode', function () {
          if (editor.mode !== 'wysiwyg') {
            disableSelectionRequired();
          } else {
            refreshStates();
          }
        });

        // Safety net: after any command execution (incl. context menu actions)
        editor.on('afterCommandExec', refreshStates);
      })();

      
      editor.on('contentDom', function () {
        var editable = editor.editable();
        var lastSpaceTime = 0;

        editable.attachListener(editable, 'keyup', function (evt) {
          var keyCode = evt.data.$.keyCode || evt.data.keyCode;
          if (keyCode !== 32) return; // SPACE only

          var now = Date.now();
          var isDouble = now - lastSpaceTime < 800; // double-press threshold (ms)
          lastSpaceTime = now;

          var sel = editor.getSelection();
          if (!sel) return;

          var range = sel.getRanges()[0];
          if (!range) return;

          // Find the wrapper span (comment/suggestion/etc.)
          var container = range.startContainer;
          var span = container.getAscendant('span', true);
          if (!span) return;

          if (!(span instanceof CKEDITOR.dom.element)) {
            span = new CKEDITOR.dom.element(span);
          }

          // Accept only tracked/comment spans
          if (!span.hasAttribute('data-ckc-thread')) return;

          // === Step 1: Verify caret is truly at END of span ===
          var atEnd =
            (range.checkEndOfNode && range.checkEndOfNode(span)) ||
            range.startOffset >= span.getChildCount();

          if (!atEnd) return; // caret not at end → do nothing

          // === Step 2: Ensure nothing but whitespace follows inside the span ===
          var lastChild = span.getLast();
          if (lastChild && lastChild.type === CKEDITOR.NODE_TEXT) {
            var text = lastChild.getText();
            // Caret might be inside last text node; make sure it's at end
            if (range.startContainer.equals(lastChild) &&
                range.startOffset < text.length) {
              return; // caret still inside text, not at absolute end
            }
          }

          // === Step 3: Confirm double-space and exit ===
          if (!isDouble) return;

          // 🔆 flash feedback (added)
          try {
            span.addClass('tc-flash');
            setTimeout(function () {
              if (span && span.removeClass) span.removeClass('tc-flash');
            }, 800);
          } catch (e) {}

          var spaceNode = new CKEDITOR.dom.text(' ', editor.document);
          var nativeSpan = span.$;
          var parent = nativeSpan.parentNode;
          if (!parent) return;

          parent.insertBefore(spaceNode.$, nativeSpan.nextSibling);

          // Move caret after inserted space
          var newRange = editor.createRange();
          newRange.moveToPosition(spaceNode, CKEDITOR.POSITION_AFTER_END);
          sel.selectRanges([newRange]);

          // console.log('[AutoExit] Caret moved outside wrapper');
        });
      });
    }
  });
})();
