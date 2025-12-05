document.addEventListener("DOMContentLoaded", () => {
  const duplicateBtn = document.querySelector(".ds-duplicate-file");
  const confirmBtn = document.getElementById("confirmDuplicate");
  const modalEl = document.getElementById("duplicateModal");

  // Ensure Bootstrap is available and modal exists
  if (!window.bootstrap) {
    console.error("Bootstrap JS is not loaded!");
    return;
  }

  if (duplicateBtn && modalEl) {
    const modalInstance = new bootstrap.Modal(modalEl);

    duplicateBtn.addEventListener("click", () => {
      modalInstance.show();
    });

    if (confirmBtn) {
      confirmBtn.addEventListener("click", (e) => {
        // TODO: Add duplication logic here
        // Get the element that triggered the event
        const el = e.currentTarget;
        const url = el.dataset.url;
        document.dispatchEvent(new CustomEvent("duplicateDocument", {
          detail: { url, button: el }
        }));
        /*console.log("Document duplicated!");
        modalInstance.hide();*/
      });
    }
  } else {
    console.warn("Duplicate button or modal not found in DOM.");
  }
});
