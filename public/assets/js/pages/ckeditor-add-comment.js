(function() {
  // helper to attach popover once per editor
  function attachPopover(editor) {
    const popover = document.createElement('div');
    popover.className = 'comment-popover d-none';
    popover.innerHTML = `<button class="comment-popover-btn">💬 Add Comment</button>`;
    document.body.appendChild(popover);

    const button = popover.querySelector('.comment-popover-btn');
    button.addEventListener('click', () => {
      const selected = editor.getSelection().getSelectedText();
      alert(`Add comment for: "${selected}"`);
      popover.classList.add('d-none');
    });

    function showPopoverIfNeeded() {
      const sel = editor.getSelection();
      const text = sel.getSelectedText().trim();
      if (!text) return popover.classList.add('d-none');
      const range = sel.getRanges()[0];
      if (!range) return;

      const rect = range.getClientRects()[0];
      if (!rect) return;

      const iframe = editor.container.$.querySelector('iframe');
      const frameRect = iframe?.getBoundingClientRect() || { top: 0, left: 0 };

      popover.style.top  = `${rect.top + frameRect.top - 40}px`;
      popover.style.left = `${rect.left + frameRect.left}px`;
      popover.classList.remove('d-none');
    }

    editor.document.on('mouseup', () => setTimeout(showPopoverIfNeeded, 10));
    editor.document.on('keyup', () => setTimeout(showPopoverIfNeeded, 10));
  }

  // Hook into CKEditor globally
  CKEDITOR.on('instanceReady', evt => attachPopover(evt.editor));
})();
