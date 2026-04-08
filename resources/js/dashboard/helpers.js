(function () {
  function escapeHtml(str) {
    str = (str ?? '').toString();
    return str.replace(/[&<>"']/g, (m) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[m]));
  }

  function fmtBytes(bytes) {
    bytes = parseInt(bytes || 0, 10);
    if (!bytes) return '—';
    const units = ['B', 'KB', 'MB', 'GB'];
    let i = 0;
    let v = bytes;
    while (v >= 1024 && i < units.length - 1) { v /= 1024; i++; }
    return `${v.toFixed(i === 0 ? 0 : 1)} ${units[i]}`;
  }

  function csrfHeaders(extra = {}) {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const h = { 'X-Requested-With': 'XMLHttpRequest', ...extra };
    if (token) h['X-CSRF-TOKEN'] = token;
    return h;
  }

  window.GP = window.GP || {};
  window.GP.escapeHtml = escapeHtml;
  window.GP.fmtBytes = fmtBytes;
  window.GP.csrfHeaders = csrfHeaders;
})();