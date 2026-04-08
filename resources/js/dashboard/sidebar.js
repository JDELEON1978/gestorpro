(function () {
  window.toggleSidebarMini = function () {
    const sidebarCol = document.getElementById('gpSidebarCol');
    const mainCol = document.getElementById('gpMainCol');
    const expanded = document.getElementById('gpSidebarExpanded');
    const collapsed = document.getElementById('gpSidebarCollapsed');
    const btn = document.getElementById('btnSidebarCollapse');

    if (!sidebarCol || !mainCol || !expanded || !collapsed || !btn) return;

    const isMini = sidebarCol.classList.contains('col-auto');
    if (isMini) {
      sidebarCol.className = 'col-12 col-lg-3 col-xxl-3';
      mainCol.className = 'col-12 col-lg-9 col-xxl-9';
      expanded.style.display = 'block';
      collapsed.style.display = 'none';
      btn.textContent = '<<';
    } else {
      sidebarCol.className = 'col-auto';
      mainCol.className = 'col';
      expanded.style.display = 'none';
      collapsed.style.display = 'block';
      btn.textContent = '>>';
    }
  };

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-bs-target="#modalNewProject"]');
    if (!btn) return;

    const wsId = btn.getAttribute('data-workspace-id');
    const wsName = btn.getAttribute('data-workspace-name');
    const nameEl = document.getElementById('npWorkspaceName');
    if (nameEl) nameEl.textContent = wsName || '—';

    const form = document.getElementById('formNewProject');
    if (form && wsId) form.action = `/workspaces/${wsId}/projects`;
  });
})();