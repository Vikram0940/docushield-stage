/* ============================================================================
 * Local Repo (dummy data + localStorage)
 * ============================================================================
 */

var Repo = (function () {
  var threads = {};
  var listeners = {};
  var documentId = 'defaultDoc';

  var COLORS = [
    "#ffe066", "#ff6b6b", "#6bcff6", "#b197fc",
    "#51cf66", "#ffa94d", "#f06595", "#63e6be"
  ];
  var colorIndex = 0;
  function nextColor() {
    var c = COLORS[colorIndex % COLORS.length];
    colorIndex++;
    return c;
  }

  function storageKey(documentId) {
    return 'ckeditor-icomments-' + documentId;
  }

  function saveToStorage() {
    try {
      localStorage.setItem(storageKey(documentId), JSON.stringify(threads));
    } catch (e) {
      console.warn('Repo(local): save failed', e);
    }
  }

  function loadFromStorage() {
    try {
      var data = localStorage.getItem(storageKey(documentId));
      return data ? JSON.parse(data) : null;
    } catch (e) {
      console.warn('Repo(local): load failed', e);
      return null;
    }
  }

  function fire(event, data) {
    (listeners[event] || []).forEach(cb => { try { cb({ name: event, data }); } catch (e) {} });
  }

  function on(event, cb) {
    if (!listeners[event]) listeners[event] = [];
    listeners[event].push(cb);
  }

  function init(docId) {
    documentId = docId;
    var stored = loadFromStorage();
    if (stored) {
      threads = stored;
    } else {
      // Dummy data with replies
      threads = {
        demo1: {
          id: 'demo1',
          createdAt: new Date(),
          color: nextColor(),
          icomments: [
            {
              id: 'c1',
              author: 'Monica Smith',
              avatar: 'https://i.pravatar.cc/40?img=5',
              content: 'This bit doesn’t need to be here, please remove and let me know about the changes.',
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
              content: 'Consider changing to primary entity names for consistency.',
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
                  content: 'Let’s back it with data if possible.',
                  createdAt: new Date()
                }
              ]
            },
            {
              id: 'c4',
              author: 'John Doe',
              avatar: 'https://i.pravatar.cc/40?img=9',
              content: 'Looks good to me overall!',
              createdAt: new Date(),
              replies: []
            },
            {
              id: 'c5',
              author: 'Sophia White',
              avatar: 'https://i.pravatar.cc/40?img=6',
              content: 'Can we double-check the references here before finalizing?',
              createdAt: new Date(),
              replies: [
                {
                  id: 'r4',
                  author: 'Dave Thomas',
                  avatar: 'https://i.pravatar.cc/40?img=8',
                  content: 'I will validate those and update.',
                  createdAt: new Date()
                }
              ]
            },
            {
              id: 'c6',
              author: 'Michael Lee',
              avatar: 'https://i.pravatar.cc/40?img=11',
              content: 'We may want to shorten this section for readability.',
              createdAt: new Date(),
              replies: []
            }
          ]
        }
      };
      saveToStorage();
    }
    fire('repoLoaded', { documentId, threads });
  }

  function createThread(thread) {
    if (!thread || !thread.threadId) return null;
    if (!threads[thread.threadId]) {
      threads[thread.threadId] = {
        id: thread.threadId,
        createdAt: new Date(),
        color: nextColor(),
        icomments: []
      };
      saveToStorage();
    }
    return threads[thread.threadId];
  }

  function addicomment(threadId, icomment) {
    if (!threadId || !threads[threadId]) return;
    var c = {
      id: 'c_' + Date.now().toString(36),
      author: icomment.author || 'Anonymous',
      avatar: icomment.avatar || null,
      content: icomment.content || '',
      createdAt: new Date(),
      replies: []
    };
    threads[threadId].icomments.push(c);
    saveToStorage();
    fire('icommentAdded', { threadId, icomment: c });
    return c;
  }

  function addReply(threadId, icommentId, reply) {
    var com = threads[threadId].icomments.find(c => c.id === icommentId);
    if (!com) return;
    var r = {
      id: 'r_' + Date.now().toString(36),
      author: reply.author || 'Anonymous',
      avatar: reply.avatar || null,
      content: reply.content || '',
      createdAt: new Date()
    };
    com.replies.push(r);
    saveToStorage();
    fire('replyAdded', { threadId, icommentId, reply: r });
    return r;
  }

  function editicomment(threadId, icommentId, newContent) {
    var icomment = threads[threadId].icomments.find(c => c.id === icommentId);
    if (!icomment) return;
    icomment.content = newContent;
    saveToStorage();
    fire('icommentEdited', { threadId, icommentId, content: newContent });
  }

  function deleteicomment(threadId, icommentId) {
    threads[threadId].icomments = threads[threadId].icomments.filter(c => c.id !== icommentId);
    saveToStorage();
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
