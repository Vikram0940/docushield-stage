/* ============================================================================
 * Track Changes – Actions (approve/deny) + snapshots (hardened)
 * ============================================================================
 */
(function () {
  'use strict';

  // ---------- Find change nodes for a given thread ----------
  function findChangeNodes(editor, threadId) {
    var root = editor && editor.editable && editor.editable();
    if (!root) return [];

    var selector = [
      'span[data-ckc-thread="' + threadId + '"]',
      'span[data-thread="' + threadId + '"]',
      'ins[data-ckc-thread="' + threadId + '"]',
      'del[data-ckc-thread="' + threadId + '"]',
      'ins[data-thread="' + threadId + '"]',
      'del[data-thread="' + threadId + '"]'
    ].join(',');

    var list = root.find(selector);
    var out = [];
    for (var i = 0; i < list.count(); i++) out.push(list.getItem(i));

    if (!out.length) {
      console.warn('[TrackActions] No nodes found for thread:', threadId);
    }
    return out;
  }

  // ---------- Type detection ----------
  // function nodeIsInsert(el) {
  //   var tag = (el.$.tagName || '').toUpperCase();
  //   if (tag === 'INS') return true;
  //   var t = el.getAttribute('data-ckc-change');
  //   if (t === 'insert') return true;
  //   var cls = (el.getAttribute('class') || '');
  //   return /\btc-insert\b/.test(cls);
  // }

  function nodeIsInsert(el) {
    var tag = (el.$ && el.$.tagName || el.tagName || '').toUpperCase();
    if (tag === 'INS') return true;

    // Support both data-ckc-change="insert" and data-change="insert"
    var t = (el.getAttribute && (el.getAttribute('data-ckc-change') || el.getAttribute('data-change'))) || null;
    if (t === 'insert') return true;

    // Support both ckc-insert and older tc-insert class names
    var cls = (el.getAttribute && el.getAttribute('class')) || '';
    if (/\bckc-insert\b/.test(cls)) return true;
    if (/\btc-insert\b/.test(cls)) return true;

    return false;
  }


  // function nodeIsDelete(el) {
  //   var tag = (el.$.tagName || '').toUpperCase();
  //   if (tag === 'DEL' || tag === 'S') return true;
  //   var t = el.getAttribute('data-ckc-change');
  //   if (t === 'delete') return true;
  //   var cls = (el.getAttribute('class') || '');
  //   return /\btc-delete\b/.test(cls);
  // }

  function nodeIsDelete(el) {
    var tag = (el.$ && el.$.tagName || el.tagName || '').toUpperCase();
    if (tag === 'DEL' || tag === 'S') return true;

    // Support both data-ckc-change="delete" and data-change="delete"
    var t = (el.getAttribute && (el.getAttribute('data-ckc-change') || el.getAttribute('data-change'))) || null;
    if (t === 'delete') return true;

    // Support both ckc-delete and older tc-delete class names
    var cls = (el.getAttribute && el.getAttribute('class')) || '';
    if (/\bckc-delete\b/.test(cls)) return true;
    if (/\btc-delete\b/.test(cls)) return true;

    return false;
  }


  // ---------- Helpers ----------
  function getEditableRoot(editor, el) {
    // Prefer the editor's editable; fallback to the element's document body.
    try {
      var edRoot = editor && editor.editable && editor.editable();
      if (edRoot) return edRoot;
    } catch (e) {}
    try {
      var body = el && el.getDocument && el.getDocument().getBody();
      if (body) return body;
    } catch (e) {}
    return null;
  }

  // Safe unwrap: move children to fragment; insert before next sibling or append.
  
  function unwrapElement(el, editor) {
    if (!el) return;

    // Build a fragment of all children
    var frag = new CKEDITOR.dom.documentFragment(el.getDocument());
    var child;
    while ((child = el.getFirst())) {
      frag.append(child); // reparent child into fragment
    }

    // Prefer inserting right before the wrapper itself
    try {
      el.insertBeforeMe(frag);   // ✅ CKEditor API: insert node before "el"
    } catch (e) {
      // Fallbacks if the wrapper is in a weird state
      try {
        var parent = el.getParent && el.getParent();
        if (parent) parent.append(frag);
        else {
          var root = editor && editor.editable && editor.editable();
          if (root) root.append(frag);
        }
      } catch (e2) {
        // Last resort: drop fragment silently
      }
    }

    // Remove the wrapper
    try { el.remove(); } catch (e3) {}
  }


  function removeElement(el) {
    if (el) el.remove();
  }

  // Compute DOM depth so we can process deepest nodes first
  function domDepth(el) {
    var d = 0, p = el;
    while (p && p.getParent && p.getParent()) { p = p.getParent(); d++; }
    return d;
  }

  // ---------- Approve / Deny core ----------
  function applyApprove(editor, threadId) {
    var nodes = findChangeNodes(editor, threadId);
    nodes = nodes.slice ? nodes.slice() : Array.prototype.slice.call(nodes);
    nodes.sort(function (a, b) { return domDepth(b) - domDepth(a); });

    var allThreads = (Repo && Repo.getAllThreads) ? Repo.getAllThreads() : [];
    var threadData = allThreads.find(t => t.id === threadId);
    var parentComment = threadData && threadData.comments && threadData.comments[0];
    var newText = parentComment ? parentComment.content : '';
    var oldText = nodes.length ? nodes[0].getText() : '';
    var remarkText = `Replaced '${oldText}' with '${newText}'`;

    for (var i = 0; i < nodes.length; i++) {
      var n = nodes[i];
      if (!n) continue;

      var changeType = n.getAttribute && n.getAttribute('data-ckc-change');
      if (changeType === 'insert' && newText) {
        var span = new CKEDITOR.dom.element('span');
        span.setAttributes({
          'class': 'tc-replaced',
          'data-ckc-thread': threadId,
          'data-ckc-remarks': remarkText
        });
        span.setText(newText);
        n.insertBeforeMe(span);
        n.remove();
        continue;
      }

      if (changeType === 'insert') unwrapElement(n, editor);
      else if (changeType === 'delete') removeElement(n);
      else unwrapElement(n, editor);
    }

    // --- Store remark in Repo ---
    if (threadData) {
      if (!threadData.remarks) threadData.remarks = [];
      threadData.remarks.push({
        action: 'approved',
        by: (window.CurrentUser && CurrentUser.name) || 'You',
        text: remarkText,
        at: new Date().toISOString()
      });
      Repo.updateThread(threadId, threadData);
    }

    if (window.TrackChanges) TrackChanges.flashThread(threadId);
  }



  function applyDeny(editor, threadId) {
    var nodes = findChangeNodes(editor, threadId);
    nodes = nodes.slice ? nodes.slice() : Array.prototype.slice.call(nodes);
    nodes.sort(function (a, b) { return domDepth(b) - domDepth(a); });

    var allThreads = (Repo && Repo.getAllThreads) ? Repo.getAllThreads() : [];
    var threadData = allThreads.find(t => t.id === threadId);
    var parentComment = threadData && threadData.comments && threadData.comments[0];
    var suggestionText = parentComment ? parentComment.content : '';
    var originalText = nodes.length ? nodes[0].getText() : '';
    var remarkText = `Suggested replacing '${originalText}' with '${suggestionText}' — Denied`;

    for (var i = 0; i < nodes.length; i++) {
      var n = nodes[i];
      if (!n) continue;

      var changeType = n.getAttribute && n.getAttribute('data-ckc-change');
      if (changeType === 'insert') {
        var span = new CKEDITOR.dom.element('span');
        span.setAttributes({
          'class': 'tc-denied',
          'data-ckc-thread': threadId,
          'data-ckc-remarks': remarkText
        });
        span.setText(originalText);
        n.insertBeforeMe(span);
        n.remove();
        continue;
      }

      if (changeType === 'delete') unwrapElement(n, editor);
      else unwrapElement(n, editor);
    }

    // --- Store remark in Repo ---
    if (threadData) {
      if (!threadData.remarks) threadData.remarks = [];
      threadData.remarks.push({
        action: 'denied',
        by: (window.CurrentUser && CurrentUser.name) || 'You',
        text: remarkText,
        at: new Date().toISOString()
      });
      Repo.updateThread(threadId, threadData);
    }

    if (window.TrackChanges) TrackChanges.flashThread(threadId);
  }





  // ---------- Persistence / snapshots ----------
  function saveSnapshot(editor) {
    try {
      if (editor && editor.fire) {
        editor.fire('saveSnapshot');
        editor.fire('change'); // force UI refresh
      }
      var html = editor.getData ? editor.getData() : '';
      if (window.Repo && Repo.setContent)  Repo.setContent(html);
      if (window.Repo && Repo.saveVersion) Repo.saveVersion(html);
      if (window.Repo && Repo.refreshSidebar) Repo.refreshSidebar();
    } catch (e) {
      console.warn('[TrackChanges] Snapshot failed', e);
    }
  }

  // ---------- Public API ----------
  function setThreadStatus(threadId, status) {
    try {
      console.log('%c[setThreadStatus] called →', 'color:orange;', { threadId, status });

      const threads = (window.Repo && Repo.getAllThreads && Repo.getAllThreads()) || [];
      console.log('[setThreadStatus] Threads in Repo →', threads);

      const t = threads.find(x => x.id === threadId || x.threadId === threadId);
      if (!t) {
        console.warn('[setThreadStatus] Thread not found in Repo →', threadId);
        return;
      }

      t.status = status;
      console.log('[setThreadStatus] Status set in memory →', threadId, status);

      if (Repo.updateThread) {
        console.log('[setThreadStatus] Calling Repo.updateThread() →', threadId, status);
        Repo.updateThread(threadId, { status });
      } else {
        console.warn('[setThreadStatus] Repo.updateThread() not defined!');
      }

      // Check if sidebar refresh happens
      if (typeof renderComments === 'function') {
        console.log('[setThreadStatus] Calling renderComments()');
        renderComments();
      } else if (Repo.refreshSidebar) {
        console.log('[setThreadStatus] Calling Repo.refreshSidebar()');
        Repo.refreshSidebar();
      } else {
        console.warn('[setThreadStatus] No sidebar refresh method found!');
      }

    } catch (e) {
      console.error('[setThreadStatus] failed:', e);
    }
  }



  window.TrackActions = window.TrackActions || {};

  window.TrackActions.approve = function (editor, threadId) {
    if (!editor) editor = CKEDITOR.instances['docushield-editor'];
    if (!editor) return;

    if (editor.fire) editor.fire('saveSnapshot');
    applyApprove(editor, threadId);
    saveSnapshot(editor);
    setThreadStatus(threadId, 'approved');

    if (window.Repo && Repo.deleteThread) Repo.deleteThread(threadId);
    console.log('[TrackActions] Approved thread → ' + threadId);
  };

  window.TrackActions.deny = function (editor, threadId) {
    if (!editor) editor = CKEDITOR.instances['docushield-editor'];
    if (!editor) return;

    if (editor.fire) editor.fire('saveSnapshot');
    applyDeny(editor, threadId);
    saveSnapshot(editor);
    setThreadStatus(threadId, 'denied'); 

    if (window.Repo && Repo.deleteThread) Repo.deleteThread(threadId);
    console.log('[TrackActions] Denied thread → ' + threadId);
  };

  console.log('%c[TrackActions] actions.js loaded (approve/deny, safe unwrap, deepest-first, robust fallback)', 'color:orange');
})();
