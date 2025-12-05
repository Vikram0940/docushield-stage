/* comments/notifications.js
 * Robust notification helper for @mentions + Repo events
 * - Uses resilient selectors for your dropdown structure
 * - Red badge on bell
 * - Click to mark read
 */

(function () {
  'use strict';

  // Run after header DOM exists
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  function init() {
    const list = [];

    // --- Selectors (robust to class/id variations)
    const bellBtn =
      document.querySelector('#notifications-toggle') ||
      document.querySelector('[aria-label="Notifications"]') ||
      document.querySelector('.notifications-center button, .notifications-center .btn');

    const listEl =
      document.querySelector('.notifications .notifications-body .notifications-list') ||
      document.querySelector('.notifications .notifications-body ul') ||
      document.querySelector('#notif-list'); // fallback if you later add an id

    if (!bellBtn) {
      console.warn('[Notif] Bell button not found.');
      return;
    }
    if (!listEl) {
      console.warn('[Notif] Notifications list not found (expected .notifications .notifications-body .notifications-list).');
      return;
    }

    // --- Badge on bell
    let badge = bellBtn.querySelector('.notif-count');
    if (!badge) {
      badge = document.createElement('span');
      badge.className =
        'notif-count position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
      badge.style.fontSize = '0.65rem';
      bellBtn.style.position = 'relative';
      bellBtn.appendChild(badge);
    }

    function updateBadge() {
      const unread = list.filter(n => !n.read).length;
      badge.textContent = unread ? unread : '';
      badge.style.display = unread ? 'inline-block' : 'none';
    }

        // --- Render only new plugin notifications, don't clear existing ones
    function render() {
      if (!listEl) return;

      // Create our custom section container if not already present
      let pluginSection = listEl.querySelector('li[data-plugin-section]');
      if (!pluginSection) {
        pluginSection = document.createElement('li');
        pluginSection.dataset.pluginSection = 'true';
        pluginSection.className = 'border-bottom p-0';
        pluginSection.innerHTML = `
          <ul class="list-unstyled mb-0" data-plugin-items></ul>
        `;
        // Prepend at very top
        listEl.prepend(pluginSection);
      }

      const pluginUl = pluginSection.querySelector('[data-plugin-items]');
      pluginUl.innerHTML = ''; // clear only our own section items

      list.forEach((n, i) => {
        const li = document.createElement('li');
        li.className =
          'px-3 py-2 d-flex align-items-start border-bottom notif-item ' +
          (n.read ? '' : 'bg-light');
        li.style.cursor = 'pointer';
        li.innerHTML = `
          <div class="flex-grow-1">
            <div class="fw-semibold">${n.text}</div>
            <div class="small text-muted">${n.time}</div>
          </div>
        `;
        li.addEventListener('click', () => {
          if (!list[i].read) {
            list[i].read = true;
            li.classList.remove('bg-light');
            updateBadge();
          }
        });
        // Prepend newest first
        pluginUl.prepend(li);
      });

      updateBadge();
    }

    // --- Add a new item, then render (without touching existing ones)
    function add(text) {
      list.push({
        text,
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        read: false
      });
      render();
    }


    // ---- Listen to mentions.js direct event
    document.addEventListener('mentionAdded', (e) => {
      const u = e.detail;
      if (u && (u.name || u.text)) add(`You mentioned @${u.name || u.text}`);
    });

    // ---- Listen to Repo(local) events to detect mentions inside comments/replies
    if (window.Repo && typeof Repo.on === 'function') {
      Repo.on('commentAdded', ({ data }) => {
        const { threadId, comment } = data || {};
        extractMentionIds(comment?.content).forEach((id) => {
          add(`@${resolveUserName(id)} was mentioned by ${comment.author} in comment (${threadId})`);
        });
      });
      Repo.on('replyAdded', ({ data }) => {
        const { threadId, reply } = data || {};
        extractMentionIds(reply?.content).forEach((id) => {
          add(`@${resolveUserName(id)} was mentioned by ${reply.author} in reply (${threadId})`);
        });
      });
    } else {
      console.warn('[Notif] Repo not available; only direct mentionAdded will notify.');
    }

    // Utilities
    function extractMentionIds(txt) {
      const ids = [];
      (txt || '').replace(/@\[[^\|\]]+\|([^\]]+)\]/g, (_, id) => ids.push(id));
      return ids;
    }
    function resolveUserName(id) {
      const u = Array.isArray(window.DocUsers) && window.DocUsers.find(x => x.id === id);
      return u ? u.name : id;
    }

    // Expose small debug API if needed
    window.DocuNotifications = { add, render, list };

    // Initial draw (shows “No notifications”)
    render();
  }
})();
