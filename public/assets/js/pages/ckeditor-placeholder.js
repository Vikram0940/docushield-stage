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