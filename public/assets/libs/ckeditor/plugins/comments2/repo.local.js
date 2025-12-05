/* ============================================================================
 * Local Repo (document content + comments with dummy data)
 * ============================================================================
 * - Stores both comment threads and document HTML per documentId
 * - Uses localStorage for persistence
 * - Includes dummy starter comments for first-time load
 * - Emits events for plugin to listen to
 * ============================================================================
 */

var Repo = (function () {
  'use strict';

  // --------------------------------------------------------------------------
  // Internal State
  // --------------------------------------------------------------------------
  var threads = {};             // { threadId: { id, color, comments: [...] } }
  var documentContent = '';     // raw CKEditor HTML content
  var listeners = {};
  var documentId = 'defaultDoc';

  var COLORS = [
    '#ffe066', '#ff6b6b', '#6bcff6', '#b197fc',
    '#51cf66', '#ffa94d', '#f06595', '#63e6be'
  ];
  var colorIndex = 0;

  // --------------------------------------------------------------------------
  // Helpers
  // --------------------------------------------------------------------------

  // Allow forcing a reload of threads (without losing content)
  function refreshThreads() {
    try {
      const data = localStorage.getItem(storageKey(documentId));
      if (data) {
        const parsed = JSON.parse(data);
        threads = parsed.threads || threads;
        Object.keys(threads).forEach(function (k) {
          if (!threads[k].type) threads[k].type = 'comment';   // backfill existing data
          if (!threads[k].status) threads[k].status = 'pending';
        });

      }
    } catch (e) {
      console.warn('[Repo(local)] refreshThreads failed', e);
    }
  }
  if (typeof Repo === 'object') Repo.refreshThreads = refreshThreads;



  function colorFor(userId) {
    if (typeof UserColors !== 'undefined' && UserColors.get) {
      return UserColors.get(userId || 'Anonymous');
    }
    var c = COLORS[colorIndex % COLORS.length];
    colorIndex++;
    return c;
  }

  function storageKey(docId) {
    return 'ckeditor-comments-' + (docId || 'defaultDoc');
  }

  // function fire(event, data) {
  //   (listeners[event] || []).forEach(function (cb) {
  //     try { cb({ name: event, data }); } catch (e) { console.error(e); }
  //   });
  // }

  function fire(event, data) {
    (listeners[event] || []).forEach(function (cb) {
      try { cb({ name: event, data }); } catch (e) { console.error(e); }
    });
  }
  if (typeof Repo === 'object') Repo.fire = fire;

  function on(event, cb) {
    if (!listeners[event]) listeners[event] = [];
    listeners[event].push(cb);
  }

  function saveToStorage() {
    try {
      var payload = {
        threads: threads,
        content: documentContent,
        savedAt: new Date().toISOString()
      };
      localStorage.setItem(storageKey(documentId), JSON.stringify(payload));
      fire('repoSaved', { documentId, payload });
    } catch (e) {
      console.warn('[Repo(local)] save failed', e);
    }
  }

  function loadFromStorage() {
    try {
      var data = localStorage.getItem(storageKey(documentId));
      if (!data) return null;
      var parsed = JSON.parse(data);
      threads = parsed.threads || {};
      Object.keys(threads).forEach(function (k) {
        if (!threads[k].type) threads[k].type = 'comment';   // backfill existing data
        if (!threads[k].status) threads[k].status = 'pending';
      });

      documentContent = parsed.content || '';
      versions = parsed.versions || [];
      return parsed;
    } catch (e) {
      console.warn('[Repo(local)] load failed', e);
      return null;
    }
  }

  // --------------------------------------------------------------------------
  // Core Methods
  // --------------------------------------------------------------------------
  function init(docId) {
    documentId = docId || 'defaultDoc';
    var stored = loadFromStorage();

    if (!stored) {
      // Dummy starter data
      threads = {
        demo1: {
          id: 'demo1',
          createdAt: new Date(),
          color: colorFor('Monica Smith'),
          comments: [
            {
              id: 'c1',
              author: 'Monica Smith',
              avatar: 'https://i.pravatar.cc/40?img=5',
              content:
                'This bit doesn’t need to be here, please remove and let me know about the changes.',
              createdAt: new Date(),
              replies: [
                {
                  id: 'r1',
                  author: 'Alice Johnson',
                  avatar: 'https://i.pravatar.cc/40?img=2',
                  content: 'Yes, I agree with Monica here.',
                  createdAt: new Date()
                }
              ]
            },
            {
              id: 'c2',
              author: 'Dave Thomas',
              avatar: 'https://i.pravatar.cc/40?img=8',
              content:
                'Consider changing to primary entity names for consistency.',
              createdAt: new Date(),
              replies: []
            },
            {
              id: 'c3',
              author: 'Alice Johnson',
              avatar: 'https://i.pravatar.cc/40?img=3',
              content: 'We should expand on this point in more detail.',
              createdAt: new Date(),
              replies: [
                {
                  id: 'r2',
                  author: 'John Doe',
                  avatar: 'https://i.pravatar.cc/40?img=9',
                  content: 'Good idea, Alice. I can draft a paragraph.',
                  createdAt: new Date()
                },
                {
                  id: 'r3',
                  author: 'Sophia White',
                  avatar: 'https://i.pravatar.cc/40?img=6',
                  content: 'Lets back it with data if possible.',
                  createdAt: new Date()
                }
              ]
            }
          ]
        }
      };

      documentContent = `
        <p>
          This is some <span class="ckc-anchor" data-ckc-thread="demo1">sample highlighted text</span>
          to demonstrate the comment system. Start typing or add a comment.
        </p>
      `;

      saveToStorage();
    }

    fire('repoLoaded', { documentId, threads, content: documentContent });
  }

  function createThread(thread) {
    if (!thread || !thread.threadId) return null;
    if (!threads[thread.threadId]) {
      threads[thread.threadId] = {
        id: thread.threadId,
        createdAt: new Date(),
        color: colorFor(thread.userId || thread.author || 'Anonymous'),
        comments: [],
        status: thread.status || 'pending',
        type: thread.type || 'comment'
      };
      saveToStorage();
      fire('threadCreated', { threadId: thread.threadId });
    }
    return threads[thread.threadId];
  }

  function addComment(threadId, comment) {
    if (!threadId || !threads[threadId]) return;
    var c = {
      id: 'c_' + Date.now().toString(36),
      author: comment.author || 'Anonymous',
      authorId: comment.authorId || (window.CurrentUser && window.CurrentUser.id) || 'anonymous',
      avatar: comment.avatar || null,
      content: comment.content || '',
      createdAt: new Date(),
      replies: []
    };
    threads[threadId].comments.push(c);
    saveToStorage();
    fire('commentAdded', { threadId, comment: c });
    return c;
  }

  function addReply(threadId, commentId, reply) {
    var thread = threads[threadId];
    if (!thread) return;
    var com = thread.comments.find(function (c) { return c.id === commentId; });
    if (!com) return;
    var r = {
      id: 'r_' + Date.now().toString(36),
      author: reply.author || 'Anonymous',
      authorId: reply.authorId || (window.CurrentUser && window.CurrentUser.id) || 'anonymous',
      avatar: reply.avatar || null,
      content: reply.content || '',
      createdAt: new Date()
    };
    com.replies.push(r);
    saveToStorage();
    fire('replyAdded', { threadId, commentId, reply: r });
    return r;
  }

  function editComment(threadId, commentId, newContent) {
    var thread = threads[threadId];
    if (!thread) return;
    var comment = thread.comments.find(function (c) { return c.id === commentId; });
    if (!comment) return;
    comment.content = newContent;
    saveToStorage();
    fire('commentEdited', { threadId, commentId, content: newContent });
  }

  function deleteComment(threadId, commentId) {
    var thread = threads[threadId];
    if (!thread) return;
    thread.comments = thread.comments.filter(function (c) { return c.id !== commentId; });
    saveToStorage();
    fire('commentDeleted', { threadId, commentId });
  }

  function updateThread(threadId, data) {
    try {
      console.log('%c[Repo.updateThread] called →', 'color:orange;', { threadId, data });

      var t = threads[threadId];
      if (!t) {
        console.warn('[Repo.updateThread] Thread not found in local Repo →', threadId);
        return false;
      }

      console.log('[Repo.updateThread] Before update →', JSON.parse(JSON.stringify(t)));

      for (var key in data) {
        if (data.hasOwnProperty(key)) {
          t[key] = data[key];
        }
      }

      console.log('[Repo.updateThread] After update →', JSON.parse(JSON.stringify(t)));

      saveToStorage();
      fire('threadUpdated', { threadId: threadId, data: data });

      console.log('%c[Repo.updateThread] Saved to localStorage ✅', 'color:green;');
      return true;
    } catch (e) {
      console.warn('[Repo.updateThread] failed:', e);
      return false;
    }
  }


  // Not being used yet
  function removeThread(editor, threadId) {
    const spans = editor.document.find(`span[data-thread="${threadId}"]`);
    for (let i = 0; i < spans.count(); i++) {
      const span = spans.getItem(i);
      const parent = span.getParent();
      // Replace the span with its inner text
      const textNode = new CKEDITOR.dom.text(span.getText(), editor.document);
      span.insertBeforeMe(textNode);
      span.remove();
    }
    // Optional: refresh your Repo or save state
    if (window.Repo && Repo.setContent) Repo.setContent(editor.getData());
  }


  // --------------------------------------------------------------------------
  // Document Content Methods
  // --------------------------------------------------------------------------
  function setContent(html) {
    documentContent = html || '';
    saveToStorage();
  }

  function getContent() {
    return documentContent;
  }

  function getAllThreads() {
    return Object.values(threads);
  }

  // --- Newly added for: version snapshots + deleteThread support ---
  var versions = []; // in-memory cache (also persisted)

  function saveVersion(html) {
    try {
      versions.push({ html: html || '', at: new Date().toISOString() });
      var payload = {
        threads: threads,
        content: documentContent,
        versions: versions,
        savedAt: new Date().toISOString()
      };
      localStorage.setItem(storageKey(documentId), JSON.stringify(payload));
      fire('versionSaved', { documentId: documentId, count: versions.length });
    } catch (e) {
      console.warn('[Repo(local)] saveVersion failed', e);
    }
  }

  // --------------------------------------------------------------------------
  // Public API
  // --------------------------------------------------------------------------
  return {
    on,
    init,
    createThread,
    addComment,
    addReply,
    editComment,
    deleteComment,
    updateThread,
    getAllThreads,
    saveVersion,
    setContent,
    getContent
  };
})();
