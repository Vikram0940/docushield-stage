document.addEventListener("DOMContentLoaded", () => {
  const duplicateBtn = document.querySelector(".ds-restore-document");
  const confirmBtn = document.getElementById("confirmRestore");
  const modalEl = document.getElementById("restoreModal");

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
      confirmBtn.addEventListener("click", () => {
        // TODO: Add duplication logic here
        console.log("Document restore!");
        modalInstance.hide();
      });
    }
  } else {
    console.warn("Restore button or modal not found in DOM.");
  }
});
