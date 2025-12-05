document.addEventListener("DOMContentLoaded", () => {
  const duplicateBtn = document.querySelector(".ds-return-to-draft");
  const confirmBtn = document.getElementById("confirmDraft");
  const modalEl = document.getElementById("returndraftModal");

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
        document.dispatchEvent(new CustomEvent("returnToDraft", {
          detail: { url, button: el }
        }));
        /*console.log("Document returned to draft!");
        modalInstance.hide();*/
      });
    }
  } else {
    console.warn("Return to Draft button or modal not found in DOM.");
  }
});



document.addEventListener("DOMContentLoaded", () => {
  const duplicateBtn = document.querySelector(".ds-accept-sign");
  const confirmBtn = document.getElementById("confirmAccept");
  const modalEl = document.getElementById("acceptsignModal");

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
        const el = e.currentTarget;
        const url = el.dataset.url;
        document.dispatchEvent(new CustomEvent("acceptAndSign", {
          detail: { url, button: el }
        }));
        // TODO: Add duplication logic here
        /*console.log("Document Accept & PDF Created!");
        modalInstance.hide();*/
      });
    }
  } else {
    console.warn("Accept and Sign button or modal not found in DOM.");
  }
});
