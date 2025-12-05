/* comments/commentsRepository.js
 * Minimal, safe comment repository for CKEditor plugin
 * - Thread-based model
 * - Simple in-memory store
 * - Event dispatching
 * - Preloaded dummy comments
 */

CKEDITOR.commentsRepository = (function () {
  'use strict';

  var threads = {}; // { threadId: { id, comments: [...] } }
  var listeners = {};

  // --- Utilities -------------------------------------------------------------

  function fire(event, data) {
    (listeners[event] || []).forEach(function (cb) {
      try { cb({ name: event, data: data }); } catch (e) { /* ignore */ }
    });
  }

  function on(event, cb) {
    if (!listeners[event]) listeners[event] = [];
    listeners[event].push(cb);
  }

  // --- Core API --------------------------------------------------------------

  function createThread(thread) {
    if (!thread || !thread.threadId) return null;
    if (!threads[thread.threadId]) {
      threads[thread.threadId] = {
        id: thread.threadId,
        createdAt: thread.createdAt || new Date(),
        comments: []
      };
    }
    return threads[thread.threadId];
  }

  function addComment(threadId, comment) {
    if (!threadId || !threads[threadId]) return;
    var c = {
      id: 'c_' + Date.now().toString(36),
      author: comment.author || 'Anonymous',
      avatar: comment.avatar || null,
      content: comment.content || '',
      createdAt: comment.createdAt || new Date(),
      replies: comment.replies || []
    };
    threads[threadId].comments.push(c);
    fire('commentAdded', { threadId: threadId, comment: c });
    return c;
  }

  function addReply(threadId, commentId, reply) {
    if (!threadId || !threads[threadId]) return;
    var com = threads[threadId].comments.find(function (c) { return c.id === commentId; });
    if (!com) return;
    var r = {
      id: 'r_' + Date.now().toString(36),
      author: reply.author || 'Anonymous',
      avatar: reply.avatar || null,
      content: reply.content || '',
      createdAt: reply.createdAt || new Date()
    };
    com.replies.push(r);
    fire('replyAdded', { threadId: threadId, commentId: commentId, reply: r });
    return r;
  }

  function getThread(threadId) {
    return threads[threadId] || null;
  }

  function getAllThreads() {
    return Object.values(threads);
  }

  // --- Dummy data (for testing/demo) -----------------------------------------
  // These threads are seeded so the sidebar looks alive initially.
  (function preloadDummy() {
    var t1 = createThread({ threadId: 'demo1', createdAt: new Date() });
    addComment('demo1', {
      author: 'Monica Smith',
      avatar: 'https://i.pravatar.cc/40?img=5',
      content: "This bit doesn’t need to be here, please remove and let me know about the changes."
    });
    addComment('demo1', {
      author: 'Monica Smith',
      avatar: 'https://i.pravatar.cc/40?img=5',
      content: "We should add more details to this section here, it doesn’t explain the issue enough."
    });
    addReply('demo1', threads['demo1'].comments[1].id, {
      author: 'Dave Thomas',
      avatar: 'https://i.pravatar.cc/40?img=8',
      content: "Change to primary entities name"
    });

    var t2 = createThread({ threadId: 'demo2', createdAt: new Date() });
    addComment('demo2', {
      author: 'Dave Thomas',
      avatar: 'https://i.pravatar.cc/40?img=8',
      content: "I can go ahead and remove this if you’d like."
    });
  })();

  // --- Public API ------------------------------------------------------------
  return {
    on: on,
    fire: fire,
    createThread: createThread,
    addComment: addComment,
    addReply: addReply,
    getThread: getThread,
    getAllThreads: getAllThreads
  };
})();
