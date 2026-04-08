(function () {
  const { escapeHtml, csrfHeaders } = window.GP;

  function fmtMeta(meta) {
    if (!meta || typeof meta !== 'object' || Array.isArray(meta)) {
      return '—';
    }

    const entries = Object.entries(meta);
    if (!entries.length) return '—';

    return entries.map(([k, v]) => {
      let value = v;
      if (v && typeof v === 'object') value = JSON.stringify(v);

      return `
        <div class="small">
          <span class="fw-semibold">${escapeHtml(k)}:</span>
          <span>${escapeHtml(String(value ?? ''))}</span>
        </div>
      `;
    }).join('');
  }

  async function renderActivitiesTable(items) {
    const bodyEl = document.getElementById('activitiesTableBody');
    if (!bodyEl) return;

    if (!items || !items.length) {
      bodyEl.innerHTML = `
        <tr>
          <td colspan="4" class="text-muted">No hay actividades registradas.</td>
        </tr>
      `;
      return;
    }

    bodyEl.innerHTML = items.map(a => `
      <tr>
        <td class="small text-muted">${escapeHtml(a.created_at || '—')}</td>
        <td>${escapeHtml(a.user_name || 'Sistema')}</td>
        <td><span class="badge text-bg-secondary">${escapeHtml(a.event || '—')}</span></td>
        <td>${fmtMeta(a.meta)}</td>
      </tr>
    `).join('');
  }

  async function openActivitiesModal(taskId) {
    const modalEl = document.getElementById('modalTaskActivities');
    const errEl = document.getElementById('activitiesError');
    const titleEl = document.getElementById('activitiesTaskTitle');
    const hiddenEl = document.getElementById('activities_task_id');
    const bodyEl = document.getElementById('activitiesTableBody');

    if (!modalEl || !errEl || !titleEl || !hiddenEl || !bodyEl) return;

    errEl.classList.add('d-none');
    errEl.textContent = '';
    hiddenEl.value = taskId;
    titleEl.textContent = `Tarea #${taskId}`;
    bodyEl.innerHTML = `<tr><td colspan="4" class="text-muted">Cargando...</td></tr>`;

    bootstrap.Modal.getOrCreateInstance(modalEl).show();

    try {
      const res = await fetch(`/tasks/${taskId}/activities`, {
        headers: csrfHeaders({ 'Accept': 'application/json' })
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok || !data.ok) {
        errEl.textContent = data.message || 'No se pudieron cargar las actividades.';
        errEl.classList.remove('d-none');
        await renderActivitiesTable([]);
        return;
      }

      titleEl.textContent = data?.task?.title || `Tarea #${taskId}`;
      await renderActivitiesTable(data.activities || []);
    } catch (err) {
      console.error(err);
      errEl.textContent = 'Error de red o servidor.';
      errEl.classList.remove('d-none');
      await renderActivitiesTable([]);
    }
  }

  async function openAuditReview(taskId) {
    const modalEl = document.getElementById('modalAuditReview');
    const errEl = document.getElementById('auditError');
    const titleEl = document.getElementById('auditTaskTitle');
    const bodyEl = document.getElementById('auditReportBody');

    if (!modalEl || !errEl || !titleEl || !bodyEl) return;

    errEl.classList.add('d-none');
    errEl.textContent = '';
    titleEl.textContent = `Tarea #${taskId}`;
    bodyEl.textContent = 'Cargando...';

    bootstrap.Modal.getOrCreateInstance(modalEl).show();

    try {
      const res = await fetch(`/tasks/${taskId}/audit-review`, {
        headers: csrfHeaders({ 'Accept': 'application/json' })
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok || !data.ok) {
        errEl.textContent = data.message || 'No se pudo generar la revisión de auditoría.';
        errEl.classList.remove('d-none');
        bodyEl.textContent = '';
        return;
      }

      titleEl.textContent = data?.root_task?.title || `Tarea #${taskId}`;
      bodyEl.textContent = data.report || 'Sin contenido.';
    } catch (err) {
      console.error(err);
      errEl.textContent = 'Error de red o servidor.';
      errEl.classList.remove('d-none');
      bodyEl.textContent = '';
    }
  }

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-open-task-activities');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const taskId = btn.getAttribute('data-task-id');
    if (taskId) openActivitiesModal(taskId);
  });

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('#btnAuditReview');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        const taskId = document.getElementById('activities_task_id')?.value;
        if (!taskId) {
            console.warn('No existe activities_task_id');
            return;
        }

        openAuditReview(taskId);
    });

  window.openActivitiesModal = openActivitiesModal;
  window.openAuditReview = openAuditReview;
})();