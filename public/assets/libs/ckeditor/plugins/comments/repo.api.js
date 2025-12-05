/* ============================================================================
 * API Repo (mirroring local structure but using backend instead of localStorage)
 * ============================================================================
 */

var Repo = (function () {
  var threads = {};
  var listeners = {};
  var documentId = 'defaultDoc';

  const API_BASE = 'http://localhost/docushield-stage/api';

  const COLORS = [
    "#ffe066", "#ff6b6b", "#6bcff6", "#b197fc",
    "#51cf66", "#ffa94d", "#f06595", "#63e6be"
  ];
  let colorIndex = 0;
  function nextColor() {
    const color = COLORS[colorIndex % COLORS.length];
    colorIndex++;
    return color;
  }
  
function GetdsApiToken() {
    const token = localStorage.getItem("ds_apitoken");
    const expiry = localStorage.getItem("ds_apitoken_expiry");
    const now = Date.now();

    if (token && expiry && now < Number(expiry)) {
        return token; // token mil gaya, API nahin hit hogi
    }

    try {
        // New Token Generate
        fetch(`${API_BASE}/login`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email: "test@test1.com", password: "123456" })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.token) throw new Error("Login failed");

            localStorage.setItem("ds_apitoken", data.token);
            localStorage.setItem("ds_apitoken_expiry", now + 30*24*60*60*1000);

            console.log("Token saved. Reloading...");
            location.reload(); // 🟢 IMPORTANT — only first time
        });

        return null;

    } catch (err) {
        console.error("Token generation failed:", err);
        return null;
    }
}


//  async function GetdsApiToken() {
//     const token = localStorage.getItem("ds_apitoken");
//     const expiry = localStorage.getItem("ds_apitoken_expiry");
//     const now = Date.now();

//     if (token && expiry && now < Number(expiry)) return token;

//     try {
//         const res = await fetch(`${API_BASE}/login`, {
//             method: "POST",
//             headers: { "Content-Type": "application/json" },
//             body: JSON.stringify({ email: "test@test1.com", password: "123456" })
//         });
//         const data = await res.json();
//         if (!data.token) throw new Error("Login failed");
//         localStorage.setItem("ds_apitoken", data.token);
//         localStorage.setItem("ds_apitoken_expiry", now + 30*24*60*60*1000); // 30 days
//         return data.token;
//     } catch (err) {
//         console.error("Token generation failed:", err);
//         return null;
//     }
// }



  // 🔹 Fire Subscribe Event
  function fire(event, data) {
    (listeners[event] || []).forEach(cb => {
      try { cb({ name: event, data }); } catch (e) {}
    });
  }

  // 🔹 Event Listener
  function on(event, cb) {
    if (!listeners[event]) listeners[event] = [];
    listeners[event].push(cb);
  }

 async function apiRequest(url, method = 'GET', body = null) {
    const token = GetdsApiToken();
    if (!token) {
        throw new Error("No API Token found or token expired");
    }

    const res = await fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
        },
        body: body ? JSON.stringify(body) : null
    });

    if (!res.ok) {
        const errText = await res.text();
        console.error("API Error:", res.status, errText);
        throw new Error(errText);
    }

    return await res.json();
}

  /** ===============================================================
   * INIT - Load comments for specific documentId
   * ============================================================= */

  /*
  ** 
  #1. Get Document Info
  #2. Load everything required in the CKEditor frm Document Response
  #3. Get All the Comments of the document
  #4. Load All the comments into the sidebar
  #5. AutoSave Document
  #6. Post Comments/Replies to backend

  Additional Tasks:
  #1. Upload a WordDocument in CKEditor.
  #2. Wellness Static Version - Put that on live site with backend connection
  **/

  async function loadDocumentWithComments(docId) {
    documentId = docId;
    try {
        const token = await GetdsApiToken();
        if (!token) throw new Error("No API token found");

        const [docRes, commentsRes] = await Promise.all([
            apiRequest(`${API_BASE}/document/${docId}`),
            apiRequest(`${API_BASE}/comments/${docId}`)
        ]);

        const document = docRes.data;
        const comments = commentsRes.comments || [];

        threads[docId] = {
            id: docId,
            color: nextColor(),
            createdAt: new Date(),
            document,
            comments: comments.map(c => ({ ...c, replies: c.replies || [] }))
        };

        // ✅ Set CKEditor content
        if (window.CKEDITOR && CKEDITOR.instances['docushield-editor']) {
            CKEDITOR.instances['docushield-editor'].setData(document.content || '');
        }

        fire('repoLoaded', { documentId, threads });
        return threads[docId];
    } catch (err) {
        console.error("Document+Comments Load Error:", err);
        threads[docId] = { id: docId, comments: [], document: null };
        fire('repoLoaded', { documentId, threads });
        return threads[docId];
    }
}


  // 🔹 Init example
  async function init() {
    const DsIDInput = document.getElementById('documentId');
    const DsID = DsIDInput ? DsIDInput.value.trim() : '';

    if (!DsID) {
        console.warn("No Document ID found. Skipping load.");
        return;
    }

    try {
        const docThread = await loadDocumentWithComments(DsID);
        console.log("Document loaded:", docThread);
    } catch (err) {
        console.error("Init failed:", err);
    }
}



  /** ===============================================================
   * CREATE NEW THREAD
   * ============================================================= */
  function createThread({ threadId }) {
    if (!threads[threadId]) {
      threads[threadId] = {
        id: threadId,
        createdAt: new Date(),
        color: nextColor(),
        comments: []
      };
      fire('threadCreated', threads[threadId]);
    }
    return threads[threadId];
  }

  /** ===============================================================
   * ADD COMMENT
   * ============================================================= */
  function addComment(threadId, comment) {
    const c = apiRequest(`${API_BASE}/comments/${threadId}`, 'POST', comment);

    c.replies = c.replies || [];
    threads[threadId].comments.push(c);

    fire('commentAdded', { threadId, comment: c });
    return c;
  }

  /** ===============================================================
   * ADD REPLY
   * ============================================================= */
  function addReply(threadId, commentId, reply) {
    const r = apiRequest(`${API_BASE}/comments/${threadId}/${commentId}/reply`, 'POST', reply);

    const com = threads[threadId].comments.find(c => c.id == commentId);
    if (com) com.replies.push(r);

    fire('replyAdded', { threadId, commentId, reply: r });
    return r;
  }

  /** ===============================================================
   * EDIT COMMENT
   * ============================================================= */
  function editComment(threadId, commentId, newContent) {
    apiRequest(`${API_BASE}/comments/${threadId}/${commentId}`, 'PUT', { content: newContent });

    const comment = threads[threadId].comments.find(c => c.id == commentId);
    if (comment) comment.content = newContent;

    fire('commentEdited', { threadId, commentId, content: newContent });
  }

  /** ===============================================================
   * DELETE COMMENT
   * ============================================================= */
  function deleteComment(threadId, commentId) {
    apiRequest(`${API_BASE}/comments/${threadId}/${commentId}`, 'DELETE');

    threads[threadId].comments = threads[threadId].comments.filter(c => c.id != commentId);

    fire('commentDeleted', { threadId, commentId });
  }


  async function uploadDocument(file) {
      const token = GetdsApiToken();
      if (!token) throw new Error("No API token found");

      const formData = new FormData();
      formData.append("file", file);

      const res = await fetch(`${API_BASE}/documents/upload`, {
          method: "POST",
          headers: {
              'Authorization': 'Bearer ' + token
          },
          body: formData
      });

      const data = await res.json();

      if (!data.url) {
          console.error("Upload failed:", data);
          throw new Error("Upload failed");
      }

      return data.url;
  }


  let autoSaveTimer = null;

  function startAutoSave() {
      const editor = CKEDITOR.instances['docushield-editor'];

      editor.on('change', function () {
          clearTimeout(autoSaveTimer);

          // debounce 2 sec
          autoSaveTimer = setTimeout(() => {
              saveDocumentContent();
          }, 2000);
      });
  }

  async function saveDocumentContent() {
      const token = GetdsApiToken();
      if (!token) return;

      const editor = CKEDITOR.instances['docushield-editor'];
      const content = editor.getData();
      const docId = document.getElementById('documentId').value;

      const formData = new FormData();
      formData.append("document_id", docId);
      formData.append("content", content);

      const response = await fetch(`${API_BASE}/documents/autosave`, {
          method: "POST",
          headers: {
              "Authorization": "Bearer " + token,
          },
          body: formData
      });

      const result = await response.json();
      console.log("AutoSaved:", result);

      document.getElementById("autosave-status").innerText =
          "Saved at " + new Date().toLocaleTimeString();
  }

  window.addEventListener("load", function () {
      if (CKEDITOR.instances['docushield-editor']) {
          startAutoSave();
      } else {
          CKEDITOR.on('instanceReady', startAutoSave);
      }
  });

  function getAllThreads() {
    return Object.values(threads);
  }


  return {
    on, init, createThread,
    addComment, addReply,
    editComment, deleteComment,
    getAllThreads,
    loadDocumentWithComments,
    uploadDocument
  };
})();