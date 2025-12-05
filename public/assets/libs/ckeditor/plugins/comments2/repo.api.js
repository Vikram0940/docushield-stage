/* ============================================================================
 * API Repo (remote data + fetch)
 * ============================================================================
 * - Same API as repo.local.js for plugin compatibility
 * - Uses UserColors.get(userId) for deterministic colors
 * - Emits identical events
 * - Replace API_BASE with your backend URL
 * ============================================================================
 */

var Repo = (function () {
  'use strict';

  // --------------------------------------------------------------------------
  // Configuration
  // --------------------------------------------------------------------------
  var API_BASE = '/api/comments'; // TODO: replace with real backend route
  var documentId = 'defaultDoc';
  var listeners = {};
  var threads = {};

  // --------------------------------------------------------------------------
  // Helpers
  // --------------------------------------------------------------------------
  function colorFor(userId) {
    if (typeof UserColors !== 'undefined' && UserColors.get) {
      return UserColors.get(userId || 'Anonymous');
    }
    return '#ffe066';
  }

  function fire(event, data) {
    (listeners[event] || []).forEach(function (cb) {
      try { cb({ name: event, data }); } catch (e) { console.error(e); }
    });
  }

  function on(event, cb) {
    if (!listeners[event]) listeners[event] = [];
    listeners[event].push(cb);
  }

  async function api(url, method = 'GET', body) {
    try {
      const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: body ? JSON.stringify(body) : undefined,
      });
      if (!res.ok) throw new Error(res.status + ' ' + res.statusText);
      return await res.json();
    } catch (err) {
      console.error('[Repo(api)] Request failed:', err);
      return null;
    }
  }

  // --------------------------------------------------------------------------
  // Core Methods
  // --------------------------------------------------------------------------
  async function init(docId) {
    documentId = docId || 'defaultDoc';
    const data = await api(`${API_BASE}?documentId=${encodeURIComponent(documentId)}`);
    threads = {};

    if (data && Array.isArray(data.threads)) {
      data.threads.forEach(function (t) {
        threads[t.id] = Object.assign({}, t, {
          color: colorFor(t.userId || t.author || 'Anonymous'),
        });
      });
    } else {
      // fallback demo thread
      threads.demo1 = {
        id: 'demo1',
        color: colorFor('DemoUser'),
        createdAt: new Date(),
        comments: [
          {
            id: 'c1',
            author: 'Server User',
            avatar: 'https://i.pravatar.cc/40?img=11',
            content: 'Fetched from API (demo mode)',
            createdAt: new Date(),
            replies: [],
          },
        ],
      };
    }

    fire('repoLoaded', { documentId, threads });
  }

  async function createThread(thread) {
    if (!thread || !thread.threadId) return null;
    const payload = { id: thread.threadId, documentId };
    const res = await api(API_BASE + '/threads', 'POST', payload);
    const newThread = res || {
      id: thread.threadId,
      color: colorFor(thread.userId || thread.author || 'Anonymous'),
      comments: [],
      createdAt: new Date(),
    };
    threads[newThread.id] = newThread;
    fire('threadCreated', { threadId: newThread.id });
    return newThread;
  }

  async function addComment(threadId, comment) {
    const payload = Object.assign({}, comment, { documentId, threadId });
    const res = await api(API_BASE + '/comments', 'POST', payload);
    const newComment = res || {
      id: 'c_' + Date.now().toString(36),
      author: comment.author || 'Anonymous',
      avatar: comment.avatar || null,
      content: comment.content || '',
      createdAt: new Date(),
      replies: [],
    };

    if (threads[threadId]) {
      threads[threadId].comments.push(newComment);
    }
    fire('commentAdded', { threadId, comment: newComment });
    return newComment;
  }

  async function addReply(threadId, commentId, reply) {
    const payload = Object.assign({}, reply, { documentId, threadId, commentId });
    const res = await api(API_BASE + '/replies', 'POST', payload);
    const newReply = res || {
      id: 'r_' + Date.now().toString(36),
      author: reply.author || 'Anonymous',
      avatar: reply.avatar || null,
      content: reply.content || '',
      createdAt: new Date(),
    };

    const thread = threads[threadId];
    if (thread) {
      const comment = thread.comments.find(c => c.id === commentId);
      if (comment) comment.replies.push(newReply);
    }
    fire('replyAdded', { threadId, commentId, reply: newReply });
    return newReply;
  }

  async function editComment(threadId, commentId, newContent) {
    const payload = { documentId, threadId, commentId, content: newContent };
    await api(API_BASE + '/comments/' + commentId, 'PUT', payload);
    const thread = threads[threadId];
    if (thread) {
      const comment = thread.comments.find(c => c.id === commentId);
      if (comment) comment.content = newContent;
    }
    fire('commentEdited', { threadId, commentId, content: newContent });
  }

  async function deleteComment(threadId, commentId) {
    await api(API_BASE + '/comments/' + commentId, 'DELETE', { documentId, threadId });
    const thread = threads[threadId];
    if (thread) {
      thread.comments = thread.comments.filter(c => c.id !== commentId);
    }
    fire('commentDeleted', { threadId, commentId });
  }

  function getAllThreads() {
    return Object.values(threads);
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
    getAllThreads
  };
})();
