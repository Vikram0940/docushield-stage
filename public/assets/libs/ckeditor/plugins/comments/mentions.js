/* ============================================================================
 * CKEditor Comments Mentions Extension
 * ============================================================================
 *  - Adds lightweight @mention autocomplete to comment/suggestion composers
 *  - Works with plain <textarea> (no inline editor)
 *  - Inserts safe tokens like @[Name|id] which are converted to <span class="mention">
 * ============================================================================ */

(function () {
  'use strict';

  var MENTION_USERS = (window.DocUsers && window.DocUsers.slice()) || [
    { id: 'u1', name: 'Monica Smith' },
    { id: 'u2', name: 'Alice Johnson' },
    { id: 'u3', name: 'Dave Thomas' },
    { id: 'u4', name: 'Sophia White' }
  ];

  function buildMentionToken(user) {
    return '@[' + user.name + '|' + user.id + ']';
  }

  function transformMentions(text) {
    var mentions = [];
    var html = text.replace(/@\[(.+?)\|([^\]]+)\]/g, function (_, name, id) {
      mentions.push(id);
      var safe = name.replace(/[<>&]/g, s => ({ '<': '&lt;', '>': '&gt;', '&': '&amp;' }[s]));
      return '<span class="mention" data-user-id="' + id + '">@' + safe + '</span>';
    });
    return { html: html, mentions: mentions };
  }

  function enableMentionsForTextarea(textarea) {
    if (!textarea || textarea._mentionsEnabled) return;
    textarea._mentionsEnabled = true;

    var dd = document.createElement('div');
    dd.className = 'mention-dd';
    Object.assign(dd.style, {
      position: 'absolute',
      minWidth: '180px',
      maxHeight: '180px',
      overflowY: 'auto',
      background: '#fff',
      border: '1px solid #ddd',
      borderRadius: '6px',
      boxShadow: '0 6px 18px rgba(0,0,0,.08)',
      zIndex: 99999,
      display: 'none'
    });
    document.body.appendChild(dd);

    var currentItems = [];
    var activeIndex = -1;

    function hide() { dd.style.display = 'none'; activeIndex = -1; }
    function show() { dd.style.display = 'block'; }
    function positionDD() {
      var r = textarea.getBoundingClientRect();
      dd.style.left = (r.left + window.scrollX) + 'px';
      dd.style.top = (r.bottom + window.scrollY + 4) + 'px';
      dd.style.width = r.width + 'px';
    }

    function render(items) {
      dd.innerHTML = '';
      items.forEach(function (it, i) {
        var row = document.createElement('div');
        row.className = 'mention-dd-item';
        row.textContent = '@ ' + it.name;
        row.style.padding = '6px 10px';
        row.style.cursor = 'pointer';
        if (i === activeIndex) row.style.background = '#eaf1ff';
        row.onmouseenter = function () { activeIndex = i; render(items); };

        row.addEventListener('mousedown', function (e) {
            // Run before textarea blur
            e.preventDefault();
            e.stopPropagation();
            insert(it);
            // Hide shortly after insertion
            setTimeout(hide, 80);
        });

        // row.onclick = function () { insert(it); };
        dd.appendChild(row);
      });
    }

    function filter(term) {
        var q = (term || '').toLowerCase();
        
        // if (!q) return [];

        // Get list of already mentioned user IDs in this textarea
        var usedIds = [];
        textarea.value.replace(/@\[[^\|\]]+\|([^\]]+)\]/g, function(_, id){
            usedIds.push(id);
        });

        // When nothing typed after "@", show all (except used ones)
        if (!q) {
            return MENTION_USERS.filter(function (u) {
            return !usedIds.includes(u.id);
            }).slice(0, 8);
        }

        return MENTION_USERS.filter(function (u) {
            // Exclude already mentioned users
            if (usedIds.includes(u.id)) return false;
            return u.name.toLowerCase().includes(q);
        }).slice(0, 8);
    }


    function getToken() {
      var v = textarea.value, pos = textarea.selectionStart;
      var upto = v.slice(0, pos);
      var at = upto.lastIndexOf('@');
      if (at < 0) return null;
      var q = upto.slice(at + 1);
      if (/\s/.test(q)) return null;
      return { start: at, query: q };
    }

    function replace(start, end, replacement) {
      var v = textarea.value;
      textarea.value = v.slice(0, start) + replacement + v.slice(end);
      var caret = start + replacement.length;
      textarea.setSelectionRange(caret, caret);
      textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }

    // function insert(user) {
    //   var t = getToken();
    //   if (!t) return hide();
    //   replace(t.start, textarea.selectionStart, buildMentionToken(user));
    //   hide();
    // }

    function insert(user) {
      var t = getToken();
      if (!t) return hide();
      replace(t.start, textarea.selectionStart, buildMentionToken(user));
      hide();

      // --- Notify system ---
      document.dispatchEvent(
        new CustomEvent('mentionAdded', { detail: user })
      );
    }


    textarea.addEventListener('keydown', function (e) {
        if (dd.style.display === 'block') {
            if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (currentItems.length) {
                activeIndex = (activeIndex + 1) % currentItems.length;
                render(currentItems);
            }
            } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (currentItems.length) {
                activeIndex = (activeIndex - 1 + currentItems.length) % currentItems.length;
                render(currentItems);
            }
            } else if (e.key === 'Enter' && activeIndex >= 0) {
            e.preventDefault();
            insert(currentItems[activeIndex]);
            } else if (e.key === 'Escape') {
            hide();
            }
        }
    });

    textarea.addEventListener('input', function () {
        var t = getToken();
        if (!t) return hide();
        currentItems = filter(t.query);
        if (!currentItems.length) return hide();
        activeIndex = 0;
        render(currentItems);
        positionDD();
        show();
    });

    // --- Fix: handle mouse click correctly ---
    let isClickingDropdown = false;
        dd.addEventListener('mousedown', function () {
        isClickingDropdown = true;
    });
    dd.addEventListener('mouseup', function () {
        isClickingDropdown = false;
        setTimeout(() => hide(), 50);
    });
    textarea.addEventListener('blur', function () {
        setTimeout(function () {
            if (!isClickingDropdown) hide();
        }, 100);
    });

  }

  // Expose globally
  window.DocuMentions = {
    enableMentionsForTextarea: enableMentionsForTextarea,
    transformMentions: transformMentions
  };

})();
