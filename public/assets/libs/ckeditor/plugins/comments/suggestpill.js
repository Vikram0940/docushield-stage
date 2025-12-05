/* ============================================================================
 * Suggest Mode Pill (helper, not a CKEditor plugin)
 * - Insert pill into .doc-small-buttons, before #version-btn
 * - Exposes SuggestPill.init(editor)
 * ============================================================================
 */
(function (w) {
  function log() { try { console.log.apply(console, ['[SuggestPill]'].concat([].slice.call(arguments))); } catch (e) {} }

  function reflect(editor, pill) {
    if (!pill || !w.TrackCfg || !TrackCfg.isSuggest) return;
    var on = !!TrackCfg.isSuggest(editor);
    pill.classList.toggle('on', on);
    pill.setAttribute('aria-pressed', on ? 'true' : 'false');
    var label = pill.querySelector('.label');
    if (label) label.textContent = on ? 'Suggestions ON' : 'Suggestions OFF';
  }

  function ensure(editor) {
    var host = document.querySelector('.doc-small-buttons');
    if (!host) { log('Host .doc-small-buttons not found'); return null; }

    // Already there?
    var existing = host.querySelector('.tc-suggest-pill');
    if (existing) { reflect(editor, existing); return existing; }

    // Build pill
    var pill = document.createElement('button');
    pill.type = 'button';
    pill.className = 'tc-suggest-pill btn btn-sm doc-btn-sm text-primary';
    pill.innerHTML = '<span class="dot" aria-hidden="true"></span><span class="label">Suggest OFF</span>';

    pill.addEventListener('click', function (e) {
      e.preventDefault();
      if (!w.TrackCfg) return;
      var next = !TrackCfg.isSuggest(editor);
      TrackCfg.setSuggest(editor, next);
      reflect(editor, pill);
      try { editor.fire('tcSuggestToggled', { enabled: next }); } catch (e) {}
    });

    // Insert before Version History
    var before = host.querySelector('#version-btn, .btn-version-history');
    if (before) before.parentNode.insertBefore(pill, before); else host.appendChild(pill);

    reflect(editor, pill);
    log('Inserted pill');
    return pill;
  }

  function attach(editor, tries) {
    var pill = ensure(editor);
    if (pill) {
      // Keep in sync
      editor.on && editor.on('tcSuggestToggled', function () { reflect(editor, pill); });
      editor.on && editor.on('mode', function () { reflect(editor, pill); });
      return;
    }
    if ((tries || 0) < 20) {
      setTimeout(function () { attach(editor, (tries || 0) + 1); }, 300);
    } else {
      log('Gave up waiting for .doc-small-buttons');
    }
  }

  w.SuggestPill = {
    init: function (editor) {
      attach(editor, 0);
    }
  };
})(window);
