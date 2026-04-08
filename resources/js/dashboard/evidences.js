(function () {
  const { fmtBytes, escapeHtml, csrfHeaders } = window.GP;

  const STATUS = {
    PENDIENTE: 'PENDIENTE',
    SUBIDO: 'SUBIDO',
    EN_REVISION: 'EN_REVISION',
    APROBADO: 'APROBADO',
    RECHAZADO: 'RECHAZADO',
  };

  function setRowEstado(itemId, estado, file) {
    const row = document.querySelector(`[data-evi-row="${CSS.escape(String(itemId))}"]`);
    if (!row) return;

    const badgeHost = row.querySelector('.badge')?.parentElement;
    if (badgeHost) {
      const st = (estado || STATUS.PENDIENTE).toString().toUpperCase();
      const map = {
        'PENDIENTE': { cls: 'text-bg-secondary', label: 'PENDIENTE' },
        'SUBIDO': { cls: 'text-bg-info', label: 'SUBIDO' },
        'EN_REVISION': { cls: 'text-bg-warning', label: 'EN REVISIÓN' },
        'APROBADO': { cls: 'text-bg-success', label: 'APROBADO' },
        'RECHAZADO': { cls: 'text-bg-danger', label: 'RECHAZADO' },
      };
      const meta = map[st] || map['PENDIENTE'];
      badgeHost.innerHTML = `<span class="badge ${meta.cls}">${meta.label}</span>`;
    }

    const info = row.querySelector(`.js-evi-fileinfo[data-item-id="${CSS.escape(String(itemId))}"]`);
    const aDL = row.querySelector(`.js-evi-download[data-item-id="${CSS.escape(String(itemId))}"]`);

    if (file && (file.original_name || file.download_url)) {
      if (info) {
        const size = file.size_bytes ? ` • ${fmtBytes(file.size_bytes)}` : '';
        const date = file.created_at ? ` • ${escapeHtml(file.created_at)}` : '';
        info.innerHTML = `<span class="text-muted">${escapeHtml(file.original_name || 'archivo')}${size}${date}</span>`;
      }
      if (aDL && file.download_url) {
        aDL.href = file.download_url;
        aDL.classList.remove('d-none');
      }
    } else {
      if (info) info.innerHTML = '';
      if (aDL) {
        aDL.href = '#';
        aDL.classList.add('d-none');
      }
    }
  }

  function setRowError(itemId, msg) {
    const row = document.querySelector(`[data-evi-row="${CSS.escape(String(itemId))}"]`);
    if (!row) return;
    const err = row.querySelector(`.js-evi-error[data-item-id="${CSS.escape(String(itemId))}"]`);
    if (!err) return;

    if (msg) {
      err.textContent = msg;
      err.classList.remove('d-none');
    } else {
      err.textContent = '';
      err.classList.add('d-none');
    }
  }

  window.loadTaskEvidences = async function (taskId) {
    if (!taskId) return;

    try {
      const res = await fetch(`/tasks/${taskId}/evidences`, {
        headers: csrfHeaders({ 'Accept': 'application/json' })
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok || !data.ok) return;

      const items = data.items || [];
      items.forEach(x => {
        const itemId = x.item_id ?? x.id;
        if (!itemId) return;
        setRowEstado(itemId, x.estado || STATUS.PENDIENTE, x.file || null);
        setRowError(itemId, null);
      });
    } catch (err) {
      console.error(err);
    }
  };

  document.addEventListener('change', (e) => {
    const input = e.target.closest('.js-evi-input');
    if (!input) return;

    const itemId = input.getAttribute('data-item-id');
    if (!itemId) return;

    const file = input.files && input.files.length ? input.files[0] : null;
    const info = document.querySelector(`.js-evi-fileinfo[data-item-id="${CSS.escape(String(itemId))}"]`);
    if (info) {
      info.textContent = file ? `${file.name} • ${fmtBytes(file.size)}` : '';
    }
  });

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.js-upload-evidence');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const itemId = btn.getAttribute('data-item-id');
    const taskId = document.getElementById('task_id')?.value;

    if (!taskId) { alert('Primero guarda la tarea para poder subir evidencias.'); return; }
    if (!itemId) { alert('Este ítem no tiene ID (nodo_item_id). Asegura que start-node lo incluya.'); return; }

    setRowError(itemId, null);

    const input = document.querySelector(`.js-evi-input[data-item-id="${CSS.escape(String(itemId))}"]`);
    if (!input || !input.files || !input.files.length) {
      setRowError(itemId, 'Selecciona un archivo.');
      return;
    }

    const file = input.files[0];
    btn.disabled = true;

    try {
      const fd = new FormData();
      fd.append('file', file);

      const res = await fetch(`/tasks/${taskId}/evidences/${itemId}`, {
        method: 'POST',
        headers: csrfHeaders({ 'Accept': 'application/json' }),
        body: fd
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok || !data.ok) {
        setRowError(itemId, data.message || 'No se pudo subir el archivo.');
        btn.disabled = false;
        return;
      }

      input.value = '';
      if (data.item) setRowEstado(itemId, data.item.estado || STATUS.SUBIDO, data.item.file || null);
      else setRowEstado(itemId, STATUS.SUBIDO, null);

      btn.disabled = false;

      if (typeof window.refreshProjectArea === 'function') await window.refreshProjectArea();

    } catch (err) {
      console.error(err);
      setRowError(itemId, 'Error de red o servidor.');
      btn.disabled = false;
    }
  });
})();