const contentField = document.getElementById('docushield-editor');
const docId = document.getElementById('documentId').value;
const statusEl = document.getElementById('autosave-status');

let autosaveTimeout = null;
let lastSavedContent = contentField.value;
let debounceDelay = 5000; // 5 seconds after user stops typing

// Function to save document via AJAX
function autosaveDocument() {
    const content = contentField.value;

    // Only save if content changed
    if (content.trim() === lastSavedContent.trim()) {
        statusEl.textContent = "No changes to save";
        return;
    }

    statusEl.textContent = "Saving...";

    fetch($app_url + '/documents/autosave', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            document_id: docId,
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            lastSavedContent = content;
            const now = new Date();
            statusEl.textContent = `Saved (v${data.version}) at ${now.toLocaleTimeString()}`;
        } else if (data.status === 'no_change') {
            statusEl.textContent = "No changes since last save";
        } else {
            statusEl.textContent = "Error saving document";
        }
    })
    .catch(err => {
        console.error('Autosave error:', err);
        statusEl.textContent = "Failed to autosave";
    });
}

// Debounce function — resets timer every time user types
contentField.addEventListener('input', function() {
    if (autosaveTimeout) clearTimeout(autosaveTimeout);
    statusEl.textContent = "Typing...";
    autosaveTimeout = setTimeout(autosaveDocument, debounceDelay);
});

// Optional: also autosave when user leaves the page
window.addEventListener('beforeunload', function(e) {
    if (contentField.value.trim() !== lastSavedContent.trim()) {
        autosaveDocument();
    }
});