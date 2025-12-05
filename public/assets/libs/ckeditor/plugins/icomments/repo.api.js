/* ============================================================================
 * API Repo (for backend integration, mirrors repo.local.js structure)
 * ============================================================================
 */

var Repo = (function () {
  var threads = {};
  var listeners = {};
  var documentId = 'defaultDoc';

  function fire(event, data) {
    (listeners[event] || []).forEach(cb => { try { cb({ name: event, data }); } catch (e) {} });
  }

  function on(event, cb) {
    if (!listeners[event]) listeners[event] = [];
    listeners[event].push(cb);
  }

  async function apiRequest(url, method = 'GET', body = null) {
    const options = { method, headers: { 'Content-Type': 'application/json' } };
    if (body) options.body = JSON.stringify(body);
    const res = await fetch(url, options);
    if (!res.ok) throw new Error('API request failed: ' + res.status);
    return res.json();
  }

  async function init(docId) {
    documentId = docId;
    try {
      // Example GET endpoint: returns { threads: { ... } }
      const data = await apiRequest(`/api/icomments?documentId=${docId}`);
      threads = data.threads || {};

      // Ensure icomments have a "replies" array
      Object.values(threads).forEach(thread => {
        thread.icomments.forEach(c => {
          if (!c.replies) c.replies = [];
        });
      });

      fire('repoLoaded', { documentId, threads });
    } catch (e) {
      console.warn('Repo(api): load failed', e);
      threads = {};
      fire('repoLoaded', { documentId, threads });
    }
  }

  async function createThread(thread) {
    const created = await apiRequest('/api/icomments/thread', 'POST', thread);
    if (!created.icomments) created.icomments = [];
    threads[thread.threadId] = created;
    fire('threadCreated', created);
    return created;
  }

  async function addicomment(threadId, icomment) {
    const c = await apiRequest(`/api/icomments/${threadId}`, 'POST', icomment);
    if (!c.replies) c.replies = [];
    threads[threadId].icomments.push(c);
    fire('icommentAdded', { threadId, icomment: c });
    return c;
  }

  async function addReply(threadId, icommentId, reply) {
    const r = await apiRequest(`/api/icomments/${threadId}/${icommentId}/reply`, 'POST', reply);
    const com = threads[threadId].icomments.find(c => c.id === icommentId);
    if (com) {
      com.replies.push(r);
    }
    fire('replyAdded', { threadId, icommentId, reply: r });
    return r;
  }

  async function editicomment(threadId, icommentId, newContent) {
    await apiRequest(`/api/icomments/${threadId}/${icommentId}`, 'PUT', { content: newContent });
    const icomment = threads[threadId].icomments.find(c => c.id === icommentId);
    if (icomment) icomment.content = newContent;
    fire('icommentEdited', { threadId, icommentId, content: newContent });
  }

  async function deleteicomment(threadId, icommentId) {
    await apiRequest(`/api/icomments/${threadId}/${icommentId}`, 'DELETE');
    threads[threadId].icomments = threads[threadId].icomments.filter(c => c.id !== icommentId);
    fire('icommentDeleted', { threadId, icommentId });
  }

  function getAllThreads() {
    return Object.values(threads);
  }

  return {
    on, init, createThread,
    addicomment, addReply,
    editicomment, deleteicomment,
    getAllThreads
  };
})();
