/* public/js/app.js — VoteSecure Frontend */

// ── MODALS ────────────────────────────────────────────────────────
function openModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('open');
}

function closeModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('open');
}

// Close modal on overlay click
document.addEventListener('click', function (e) {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('open');
  }
});

// Close modal on Escape key
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
  }
});

// ── AUTO-DISMISS ALERTS ───────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.alert').forEach(function (alert) {
    setTimeout(function () {
      alert.style.transition = 'opacity 0.5s ease';
      alert.style.opacity    = '0';
      setTimeout(function () { alert.remove(); }, 500);
    }, 4000);
  });
});

// ── CONFIRM DANGEROUS ACTIONS ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
  });
});

// ── RESULT BAR ANIMATION ──────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  // Animate result bars into view
  const bars = document.querySelectorAll('.result-bar-fill');
  if (bars.length) {
    // Start at 0 then animate to target
    bars.forEach(function (bar) {
      const target = bar.style.width;
      bar.style.width = '0%';
      requestAnimationFrame(function () {
        setTimeout(function () { bar.style.width = target; }, 100);
      });
    });
  }
});

// ── TOPBAR ACTIVE LINK ────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  const path = window.location.pathname;
  document.querySelectorAll('.topbar-nav a').forEach(function (link) {
    if (link.getAttribute('href') && path.includes(link.getAttribute('href').split('/').pop())) {
      link.classList.add('active');
    }
  });
});