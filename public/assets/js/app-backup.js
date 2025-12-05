document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
  if (bootstrap?.Tooltip) new bootstrap.Tooltip(el);
});

document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".star").forEach(function (star) {
    star.addEventListener("click", function (e) {
      e.preventDefault(); // prevent jumping due to href
      this.classList.toggle("starred");
    });
  });
});


const toggleBtn = document.getElementById('filterToggle');
const closeBtn = document.getElementById('filterClose');
const panel = document.getElementById('filterPanel');
const overlay = document.getElementById('filterOverlay');

function closePanel() {
  panel.classList.remove('active');
  overlay.classList.remove('active');
}

toggleBtn.addEventListener('click', () => {
  panel.classList.toggle('active');
  overlay.classList.toggle('active');
});

closeBtn.addEventListener('click', closePanel);
overlay.addEventListener('click', closePanel);

// const input = document.getElementById('collabInput');
// const dropdown = document.getElementById('collabDropdown');

// input.addEventListener('input', () => {
//   if (input.value.trim().length > 0) {
//     dropdown.classList.remove('d-none');
//   } else {
//     dropdown.classList.add('d-none');
//   }
// });

// // Hide dropdown if click outside
// document.addEventListener('click', (e) => {
//   if (!dropdown.contains(e.target) && e.target !== input) {
//     dropdown.classList.add('d-none');
//   }
// });