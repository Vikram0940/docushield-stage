(function () {
  'use strict';

  /* ------------------------------
   * Helpers
   * ------------------------------ */
  const $  = (sel, root) => (root || document).querySelector(sel);
  const $$ = (sel, root) => Array.from((root || document).querySelectorAll(sel));
  function byText(root, selector, text) {
    if (!root) return null;
    const needle = (text || '').trim();
    return $$(selector, root).find(el => (el.textContent || '').trim() === needle) || null;
  }

  const qs = new URLSearchParams(location.search);
  const DOCUMENT_ID = qs.get('demo1') || 'demoDoc-001';

  /* ------------------------------
   * Version Store (demo: localStorage)
   * ------------------------------ */
  const VersionStore = {
    key() { return `doc:${DOCUMENT_ID}:versions`; },
    all() { try { return JSON.parse(localStorage.getItem(this.key()) || '[]'); } catch { return []; } },
    add(content, meta = {}) {
      const list = this.all();
      list.push({
        id: Date.now(),
        ts: new Date().toISOString(),
        title: $('#doc-title')?.value || 'Untitled',
        content,
        meta
      });
      localStorage.setItem(this.key(), JSON.stringify(list));
    }
  };

  function updateVersionCount() {
    const el = $('#version-count');
    if (el) el.textContent = VersionStore.all().length;
  }

  /* ------------------------------
   * CKEditor bootstrap (single editor)
   * ------------------------------ */
  // const extra = ['comments', 'floating-tools', 'toolbarswitch', 'fixed', 'button', 'toolbar', 'maximize', 'pagebreak', 'fakeobjects', 'contextmenu', 'menu', 'floatpanel', 'panel', 'menubutton', 'newpage', 'save']; // add 'pagebreak' once you install that plugin folder

    const extra = [
        'comments',
        'undo',
        'print',
        'sharedspace',
        'button',
        'toolbar',
        'maximize',
        'pagebreak',
        'fakeobjects',
        'contextmenu',
        'menu',
        'floatpanel',
        'panel',
        'menubutton',
        'newpage',
        'save',
        // 'menubutton',
        // 'menu',
        // 'dsmenus'
    ];
    const editor = CKEDITOR.replace('docushield-editor', {
        extraPlugins: extra.join(','),
        documentId: DOCUMENT_ID,
        commentsRepo: 'local',
        reviewMode: false,                 // locks content, keeps comments interactive
        readOnly: true,
        commentsContainer: '#ds-comments-sidebar',
        resize_enabled: false,
        removePlugins: 'elementspath,versioncheck',
        ignoreAllWarnings: true,
        sharedSpaces: {
            top: 'editor-toolbar',
        },
        toolbar: [
            { name: 'clipboard', items: [ 'Undo', 'Redo', 'Print' ] },
            { name: 'comments', items: [ 'Comment' ] },
            { name: 'links',     items: [ 'Link', 'Unlink' ] },
            { name: 'colors',    items: [ 'TextColor', 'BGColor' ] },
            // { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline' ] },
            { name: 'paragraph', items: [ 'BulletedList', 'NumberedList', 'Bold', 'Italic', 'Underline', 'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter', 'JustifyRight' ] },
            // { name: 'align',     items: [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight' ] },
            { name: 'formatting', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
            // { name: 'styles',    items: [ 'Font', 'FontSize' ] },
            { name: 'insert',    items: [ 'Image', 'Table' ] }
        ],
        bodyClass: 'ds-doc-page',
        height: 1123,
        customPlaceholder: 'Start typing here...',
    });

  editor.on('instanceReady', function () {
    try {
    //   const editable = editor.editable().$;

    //   if (editable && !editable.closest('.editor-page')) {
    //     const wrapper = document.createElement('div');
    //     wrapper.className = 'editor-page';
    //     editable.parentNode.insertBefore(wrapper, editable);
    //     wrapper.appendChild(editable);
    //   }
    } catch (e) {}
    updateVersionCount();
  });

    
    document.addEventListener("DOMContentLoaded", () => {
        const commentsBtn = document.querySelector("#comments-toggle"); 
        const docBody = document.querySelector(".doc-body");

        // helper function
        function updateState() {
            const isOpen = docBody.classList.contains("comments-open");

            if (isOpen) {
            commentsBtn.classList.add("active");
            } else {
            commentsBtn.classList.remove("active");
            }
        }

        // initial check on load
        updateState();

        // toggle on click
        commentsBtn.addEventListener("click", () => {
            docBody.classList.toggle("comments-open");
            updateState();
        });
    });



  /* ------------------------------
   * Menus & actions
   * ------------------------------ */
  function saveDraft() {
    VersionStore.add(editor.getData(), { title: $('#doc-title')?.value });
    updateVersionCount();
  }
  $('#save-draft')?.addEventListener('click', saveDraft);

  // Send for Review
  $('#send-for-review')?.addEventListener('click', function () {
    if (confirm('Save current draft and mark as “Ready for Review”?')) {
      saveDraft();
      alert('Draft saved. Review request sent (stub).');
    }
  });

  $('#toggle-sidebar')?.addEventListener('click', function (e) {
    e.preventDefault();
    $('#ds-comments-sidebar')?.classList.toggle('d-none');
  });

  // Top Version History button
  $('#version-btn')?.addEventListener('click', function () {
    location.href = `/revisions.html?doc=${encodeURIComponent(DOCUMENT_ID)}`;
  });

  // Optional: if a secondary .toolbar exists, wire its Version History button safely
  (function wireToolbarVersionHistory() {
    const toolbar = $('.toolbar');
    const btn = byText(toolbar, '.btn', 'Version History');
    if (btn) btn.addEventListener('click', function () {
      location.href = `/revisions.html?doc=${encodeURIComponent(DOCUMENT_ID)}`;
    });
  })();

  // Cosmetic star toggle
  $('#star-btn')?.addEventListener('click', function () {
    this.classList.toggle('text-warning');
    this.textContent = this.classList.contains('text-warning') ? '★' : '☆';
  });

})();
