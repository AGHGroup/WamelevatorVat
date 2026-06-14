/**
 * rtl-switcher.js
 * Toggles RTL layout and Arabic language for all pages.
 */
'use strict';

(function () {
  const STORAGE_KEY = 'sneat-dir';
  const htmlEl = document.documentElement;

  function applyDir(dir) {
    const isRtl = dir === 'rtl';
    htmlEl.setAttribute('dir', dir);
    htmlEl.setAttribute('lang', isRtl ? 'ar' : 'en');

    document.querySelectorAll('.rtl-toggle-btn').forEach(function (btn) {
      btn.textContent = isRtl ? 'EN' : 'ع';
      btn.title = isRtl ? 'Switch to English (LTR)' : 'تبديل إلى العربية';
    });

    localStorage.setItem(STORAGE_KEY, dir);
  }

  function toggleDir() {
    applyDir(htmlEl.getAttribute('dir') === 'rtl' ? 'ltr' : 'rtl');
  }

  /* Restore persisted preference as early as possible */
  var saved = localStorage.getItem(STORAGE_KEY);
  if (saved === 'rtl' || saved === 'ltr') {
    applyDir(saved);
  }

  document.addEventListener('DOMContentLoaded', function () {
    /* Set correct button label after DOM is ready */
    var currentDir = htmlEl.getAttribute('dir') || 'ltr';
    document.querySelectorAll('.rtl-toggle-btn').forEach(function (btn) {
      btn.textContent = currentDir === 'rtl' ? 'EN' : 'ع';
      btn.title = currentDir === 'rtl' ? 'Switch to English (LTR)' : 'تبديل إلى العربية';
      btn.addEventListener('click', toggleDir);
    });
  });
})();
