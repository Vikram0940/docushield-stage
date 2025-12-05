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
        // 'icomments',
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

  // ------------------------------
  // Enable & trigger Send for Review modal
  // ------------------------------
  editor.on('change', function () {
    const btn = document.getElementById('send-for-review');
    if (!btn) return;

    const content = editor.getData().trim();
    const hasContent = content.replace(/<[^>]*>/g, '').trim().length > 0; // ignore HTML tags
    btn.disabled = !hasContent;
  });

  // Recheck state on load
  editor.on('instanceReady', function () {
    updateVersionCount();

    const btn = document.getElementById('send-for-review');
    if (btn) {
      const content = editor.getData().trim();
      const hasContent = content.replace(/<[^>]*>/g, '').trim().length > 0;
      btn.disabled = !hasContent;
    }
  });

    
  
    CKEDITOR.instances['docushield-editor'].on('contentDom', function () {
        const editor = this;
        const editable = editor.editable();

        // Create placeholder element
        const wrapper = editable.$.parentNode;
        const placeholderText = editor.config.customPlaceholder || '';
        const placeholderEl = editable.$.ownerDocument.createElement('div');
        placeholderEl.className = 'doc-placeholder';
        placeholderEl.textContent = placeholderText;

        // Append placeholder
        wrapper.appendChild(placeholderEl);

        function isEmpty() {
            return editor.getData().replace(/<[^>]*>/g, '').trim().length === 0;
        }

        function showPlaceholder() {
            if (isEmpty()) placeholderEl.style.display = 'block';
        }

        function hidePlaceholder() {
            placeholderEl.style.display = 'none';
        }

        // Hide on focus
        editor.on('focus', hidePlaceholder);

        // Show on blur (if empty)
        editor.on('blur', showPlaceholder);

        // Also hide on typing
        editor.on('change', () => {
            if (!isEmpty()) hidePlaceholder();
        });

        // Initial check
        showPlaceholder();
    });

    document.addEventListener("DOMContentLoaded", () => {
        const input = document.getElementById("doc-title");

        function resizeInput() {
            input.style.width = "1px"; // reset
            input.style.width = Math.min(input.scrollWidth + 10, 400) + "px"; // grow but max 400px
        }

        resizeInput(); // initial size
        input.addEventListener("input", resizeInput);
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

  // File menu
  document.body.addEventListener('click', function (e) {
    const fileCmd = e.target.closest?.('[data-cmd]');
    if (!fileCmd) return;
    e.preventDefault();

    const cmd = fileCmd.getAttribute('data-cmd');
    switch (cmd) {
      case 'new':
        if (confirm('Start a new document? Unsaved changes will be lost.')) {
          editor.setData('');
          if ($('#doc-title')) $('#doc-title').value = 'Untitled';
        }
        break;
      case 'open':
        $('#open-file')?.click();
        break;
      case 'save':
        saveDraft();
        break;
      case 'export-pdf':
        window.print(); // simple first pass
        break;
    }
  });

  // Edit menu
  document.body.addEventListener('click', function (e) {
    const ckCmd = e.target.closest?.('[data-ck]');
    if (!ckCmd) return;
    e.preventDefault();
    const cmd = ckCmd.getAttribute('data-ck');
    try { editor.execCommand(cmd); } catch {}
  });

  // File → Open
  $('#open-file')?.addEventListener('change', function () {
    const f = this.files?.[0];
    if (!f) return;
    const r = new FileReader();
    r.onload = () => editor.setData(String(r.result || ''));
    r.readAsText(f);
    this.value = '';
  });

  function saveDraft() {
    VersionStore.add(editor.getData(), { title: $('#doc-title')?.value });
    updateVersionCount();
  }
  $('#save-draft')?.addEventListener('click', saveDraft);

  // Send for Review
  $('#send-for-review').addEventListener('click', function () {
    if (confirm('Save current draft and mark as “Ready for Review”?')) {
      const geturl = this.dataset.geturl;
      const url = this.dataset.url;
      const event = this.dataset.event;
      //console.log(geturl + "/" + url);
      document.dispatchEvent(new CustomEvent("eventReview", {
        detail: { geturl, url, event }
      }));
      //saveDraft();
      //alert('Draft saved. Review request sent (stub).');
    }
  });

  // View menu
  $('#toggle-page-margins')?.addEventListener('click', function (e) {
    e.preventDefault();
    document.body.classList.toggle('show-margins');
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
