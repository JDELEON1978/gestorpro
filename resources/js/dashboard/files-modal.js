(function () {
  const { escapeHtml, fmtBytes, csrfHeaders } = window.GP;

  async function renderFilesTable(files) {
    const bodyEl = document.getElementById('filesTableBody');
    if (!bodyEl) return;

    if (!files || !files.length) {
      bodyEl.innerHTML = `<tr><td colspan="5" class="text-muted">No hay archivos aún.</td></tr>`;
      return;
    }

    bodyEl.innerHTML = files.map(f => `
      <tr>
        <td class="text-truncate" style="max-width:420px;">
          <i class="bi bi-file-earmark me-1"></i>
          ${escapeHtml(f.original_name)}
        </td>
        <td>${fmtBytes(f.size_bytes)}</td>
        <td>${escapeHtml(f.user_name || '—')}</td>
        <td class="small text-muted">${escapeHtml(f.created_at || '—')}</td>
        <td class="text-end">
          <a class="btn btn-sm gp-btn" href="${f.download_url}" target="_blank" title="Descargar">
            <i class="bi bi-download"></i>
          </a>
          <button class="btn btn-sm gp-btn js-del-file"
                  data-file-id="${f.id}"
                  title="Eliminar">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `).join('');
  }

  async function openFilesModal(taskId) {
    const modalEl = document.getElementById('modalFiles');
    const errEl = document.getElementById('filesError');
    const titleEl = document.getElementById('filesTaskTitle');
    const hidden = document.getElementById('files_task_id');
    const bodyEl = document.getElementById('filesTableBody');

    if (!modalEl || !errEl || !titleEl || !hidden || !bodyEl) return;

    errEl.classList.add('d-none');
    errEl.textContent = '';
    hidden.value = taskId;

    bodyEl.innerHTML = `<tr><td colspan="5" class="text-muted">Cargando...</td></tr>`;
    bootstrap.Modal.getOrCreateInstance(modalEl).show();

    const res = await fetch(`/tasks/${taskId}/files`, { headers: csrfHeaders({ 'Accept': 'application/json' }) });
    const data = await res.json().catch(() => ({}));

    titleEl.textContent = data?.task?.title || `Tarea #${taskId}`;
    await renderFilesTable(data.files || []);
  }

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-open-files');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const taskId = btn.getAttribute('data-task-id');
    if (taskId) openFilesModal(taskId);
  });

  document.getElementById('btnUploadFiles')?.addEventListener('click', async (e) => {
    e.preventDefault();

    const taskId = document.getElementById('files_task_id')?.value;
    const input = document.getElementById('files_input');
    const errEl = document.getElementById('filesError');

    if (!taskId || !input || !errEl) return;

    errEl.classList.add('d-none');
    errEl.textContent = '';

    if (!input.files || !input.files.length) {
      errEl.textContent = 'Selecciona al menos un archivo.';
      errEl.classList.remove('d-none');
      return;
    }

    const fd = new FormData();
    Array.from(input.files).forEach(f => fd.append('files[]', f));

    const res = await fetch(`/tasks/${taskId}/files`, {
      method: 'POST',
      headers: csrfHeaders({ 'Accept': 'application/json' }),
      body: fd
    });

    if (!res.ok) {
      let msg = 'No se pudo subir el archivo.';
      try {
        const data = await res.json();
        msg = data?.message || msg;
      } catch { }
      errEl.textContent = msg;
      errEl.classList.remove('d-none');
      return;
    }

    input.value = '';
    await openFilesModal(taskId);

    if (typeof window.refreshProjectArea === 'function') await window.refreshProjectArea();
  });
})();