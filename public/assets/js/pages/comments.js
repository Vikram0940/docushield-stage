const COLORS = [
  "#ffe066", "#ff6b6b", "#6bcff6", "#b197fc",
  "#51cf66", "#ffa94d", "#f06595", "#63e6be"
];

function getRandomColor() {
  return COLORS[Math.floor(Math.random() * COLORS.length)];
}

document.addEventListener("DOMContentLoaded", () => {
  const CURRENT_USER = { name: "You", avatar: "https://i.pravatar.cc/50?img=13", timeLabel: "just now" };
  let uidCounter = 1;

    function ensureUid(commentEl) {
        if (!commentEl.dataset.uid) {
            commentEl.dataset.uid = `c-${uidCounter++}`;
            // assign random color once
            commentEl.dataset.color = getRandomColor();
        }
        return commentEl.dataset.uid;
    }

  function createComment({ author, text, replyTo }) {
    const tpl = document.querySelector("#comment-template");
    const frag = tpl.content.cloneNode(true);
    const card = frag.querySelector(".previous-comment");

    card.querySelector(".c-title").textContent = author || CURRENT_USER.name;
    card.querySelector(".c-time").textContent = CURRENT_USER.timeLabel;
    card.querySelector(".c-avatar img").src = CURRENT_USER.avatar;
    card.querySelector(".comment-content").textContent = text;

    if (replyTo) {
      let replyLabel = document.createElement("div");
      replyLabel.className = "reply-to small text-muted mb-1";
      replyLabel.textContent = `Replying to ${replyTo}`;
      card.querySelector(".comment-content").before(replyLabel);
    }

    if (!card.querySelector(".replies")) {
      const repliesContainer = document.createElement("div");
      repliesContainer.className = "replies ms-2 mt-1";
      card.appendChild(repliesContainer);
    }

    ensureUid(card);
    return card;
  }

  function createFooter(uid) {
    const tpl = document.querySelector("#footer-template");
    const frag = tpl.content.cloneNode(true);
    const footer = frag.querySelector(".comment-footer");
    footer.dataset.replyToUid = uid;

    const textarea = footer.querySelector(".reply-input");
    const actions = footer.querySelector(".action-buttons");
    const postBtn = footer.querySelector('[data-action="composer-post"]');
    const cancelBtn = footer.querySelector('[data-action="composer-cancel"]');

    textarea.addEventListener("focus", () => actions.classList.remove("d-none"));
    textarea.addEventListener("input", () => {
      textarea.style.height = "auto";
      textarea.style.height = textarea.scrollHeight + "px";
      postBtn.disabled = !textarea.value.trim();
    });
    cancelBtn.addEventListener("click", () => footer.remove());

    return footer;
  }

  document.addEventListener("click", (e) => {
    const actionEl = e.target.closest("[data-action]");
    if (!actionEl) return;
    e.preventDefault();

    let commentEl = actionEl.closest(".previous-comment");
    if (!commentEl.querySelector(".comment-header")) {
      commentEl = commentEl.querySelector(".previous-comment");
    }
    const action = actionEl.dataset.action;

    if (action === "reply") {
      const uid = ensureUid(commentEl);
      document.querySelectorAll(".comment-footer").forEach(f => f.remove());
      const footer = createFooter(uid);
      commentEl.querySelector(".replies").before(footer);
      footer.querySelector(".reply-input").focus();
    }

    if (action === "composer-post") {
      const footer = actionEl.closest(".comment-footer");
      const text = footer.querySelector(".reply-input").value.trim();
      if (!text) return;

      const replyToUid = footer.dataset.replyToUid;
      const parent = document.querySelector(`.previous-comment[data-uid="${replyToUid}"]`);
      const replyCard = createComment({ author: CURRENT_USER.name, text, replyTo: parent.querySelector(".c-title").textContent });
      parent.after(replyCard);
      footer.remove();
    }

    if (action === "edit") {
      const contentEl = commentEl.querySelector(".comment-content");
      const oldText = contentEl.textContent.trim();
      contentEl.classList.add("d-none");

      const editBox = document.createElement("div");
      editBox.className = "comment-footer mt-2";
      editBox.innerHTML = `
        <textarea class="form-control comment-edit-textarea" rows="1">${oldText}</textarea>
        <div class="d-flex justify-content-end gap-2 mt-2">
          <button class="btn btn-sm btn-light" data-action="edit-cancel">Cancel</button>
          <button class="btn btn-sm btn-primary" data-action="edit-save">Save</button>
        </div>`;
      contentEl.after(editBox);

      const ta = editBox.querySelector("textarea");
      ta.focus();
      ta.addEventListener("input", () => {
        ta.style.height = "auto";
        ta.style.height = ta.scrollHeight + "px";
      });
    }

    if (action === "edit-cancel") {
      const editBox = actionEl.closest(".comment-footer");
      editBox.previousElementSibling.classList.remove("d-none");
      editBox.remove();
    }

    if (action === "edit-save") {
      const editBox = actionEl.closest(".comment-footer");
      const newText = editBox.querySelector("textarea").value.trim();
      const contentEl = editBox.previousElementSibling;
      if (newText) contentEl.textContent = newText;
      contentEl.classList.remove("d-none");
      editBox.remove();
    }

    if (action === "delete") {
      if (confirm("Delete this comment?")) commentEl.remove();
    }
  });

  document.querySelectorAll(".previous-comment").forEach(ensureUid);


    // Highlight Comment
    document.addEventListener("click", (e) => {
        const hl = e.target.closest(".highlight");
        if (!hl) return;

        const id = hl.dataset.commentId;
        const target = document.querySelector(`.previous-comment[data-uid="${id}"]`);
        if (!target) return;

        const panel = document.querySelector(".comments-panel");
        panel.scrollTo({
            top: target.offsetTop - panel.offsetTop - 20,
            behavior: "smooth"
        });

        // Animate comment background
        const color = target.dataset.color || "#ffec99";
        target.style.transition = "background-color 0.3s ease";
        const original = target.style.backgroundColor;
        target.style.backgroundColor = color + "55";
        setTimeout(() => { target.style.backgroundColor = original; }, 800);
    });


    // Assign default highlight background
    document.querySelectorAll(".highlight").forEach((hl) => {
        const id = hl.dataset.commentId;
        const comment = document.querySelector(`.previous-comment[data-uid="${id}"]`);
        if (comment) {
            const color = comment.dataset.color;
            hl.style.backgroundColor = color + "55"; // semi-transparent always
            hl.dataset.color = color;
        }
    });


    // 👉 Comment → Highlight
    document.addEventListener("click", (e) => {
        const card = e.target.closest(".previous-comment");
        if (!card || !card.dataset.uid) return;

        const id = card.dataset.uid;
        const highlights = document.querySelectorAll(`.highlight[data-comment-id="${id}"]`);
        if (!highlights.length) return;

        const color = card.dataset.color || "#ffec99";

        // scroll to first highlight
        highlights[0].scrollIntoView({ behavior: "smooth", block: "center" });

        // flash highlights
        highlights.forEach((hl) => {
            hl.style.transition = "background-color 0.3s ease";
            const original = hl.dataset.color ? hl.dataset.color + "55" : hl.style.backgroundColor;
            hl.style.backgroundColor = color; // full opacity flash
            setTimeout(() => { hl.style.backgroundColor = original; }, 800);
        });
    });
});
