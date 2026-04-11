(function () {
  const { escapeHtml, csrfHeaders } = window.GP;

  function resetNodoUI() {
    const nodoIdEl = document.getElementById('task_nodo_id');
    if (nodoIdEl) nodoIdEl.value = '';

    const info = document.getElementById('taskProcessInfo');
    if (info) info.style.display = 'none';

    const evidWrap = document.getElementById('taskEvidenciasWrap');
    if (evidWrap) evidWrap.style.display = 'none';

    const transWrap = document.getElementById('taskTransWrap');
    if (transWrap) transWrap.style.display = 'none';

    const nodoNombre = document.getElementById('taskNodoNombre');
    const nodoDesc = document.getElementById('taskNodoDesc');
    const nodoSla = document.getElementById('taskNodoSla');
    const nodoItems = document.getElementById('taskNodoItems');
    const evRows = document.getElementById('taskEvidenciasRows');
    const transBtns = document.getElementById('taskTransButtons');
    const transHint = document.getElementById('taskTransHint');

    if (nodoNombre) nodoNombre.textContent = '—';
    if (nodoDesc) nodoDesc.textContent = '';
    if (nodoSla) nodoSla.textContent = '—';
    if (nodoItems) nodoItems.innerHTML = '';
    if (evRows) evRows.innerHTML = '';
    if (transBtns) transBtns.innerHTML = '';
    if (transHint) {
      transHint.textContent = '';
      transHint.classList.add('d-none');
    }
  }

  function renderEvidenceBadge(estado) {
    const st = (estado || 'PENDIENTE').toString().toUpperCase();
    const map = {
      'PENDIENTE': { cls: 'text-bg-secondary', label: 'PENDIENTE' },
      'SUBIDO': { cls: 'text-bg-info', label: 'SUBIDO' },
      'EN_REVISION': { cls: 'text-bg-warning', label: 'EN REVISIÓN' },
      'APROBADO': { cls: 'text-bg-success', label: 'APROBADO' },
      'RECHAZADO': { cls: 'text-bg-danger', label: 'RECHAZADO' },
    };
    const meta = map[st] || map['PENDIENTE'];
    return `<span class="badge ${meta.cls}">${meta.label}</span>`;
  }

  function formatSlaText(slaHours) {
    const hours = parseInt(slaHours, 10);
    if (!Number.isFinite(hours) || hours <= 0) {
      return 'Sin SLA configurado';
    }

    return hours === 1 ? '1 hora hábil' : `${hours} horas hábiles`;
  }

  function updateTransitionButtonsState() {
    const transBtns = document.getElementById('taskTransButtons');
    const transHint = document.getElementById('taskTransHint');
    const taskId = (document.getElementById('task_id')?.value || '').trim();

    if (!transBtns) return;

    const buttons = Array.from(transBtns.querySelectorAll('.js-task-trans'));
    if (!buttons.length) {
      if (transHint) {
        transHint.textContent = '';
        transHint.classList.add('d-none');
      }
      return;
    }

    const requiredRows = Array.from(document.querySelectorAll('[data-evi-row]')).filter((row) => {
      return row.getAttribute('data-obligatorio') === '1'
        && row.getAttribute('data-requiere-evidencia') === '1';
    });

    let disabled = false;
    let message = '';

    if (!taskId) {
      disabled = true;
      message = requiredRows.length > 0
        ? 'Primero guarda la tarea y luego sube las evidencias obligatorias para continuar.'
        : 'Primero guarda la tarea para poder continuar por una transición.';
    } else {
      const missingRows = requiredRows.filter((row) => row.getAttribute('data-has-file') !== '1');
      if (missingRows.length > 0) {
        disabled = true;
        const names = missingRows
          .map((row) => row.getAttribute('data-item-nombre') || 'ítem requerido')
          .filter(Boolean);
        message = `Faltan evidencias obligatorias: ${names.join(', ')}.`;
      }
    }

    buttons.forEach((btn) => {
      btn.disabled = disabled;
      btn.classList.toggle('disabled', disabled);
    });

    if (transHint) {
      if (disabled && message) {
        transHint.textContent = message;
        transHint.classList.remove('d-none');
      } else {
        transHint.textContent = '';
        transHint.classList.add('d-none');
      }
    }
  }

  function renderNodoMeta(nodo, task = null) {
    document.getElementById('task_nodo_id').value = nodo.id;

    const info = document.getElementById('taskProcessInfo');
    info.style.display = 'block';
    document.getElementById('taskNodoNombre').textContent = nodo.nombre || `Nodo #${nodo.id}`;
    document.getElementById('taskNodoDesc').textContent = nodo.descripcion || '';
    document.getElementById('taskNodoSla').textContent = formatSlaText(
      task?.sla_hours ?? nodo.sla_horas ?? nodo.sla_hours ?? null
    );

    const itemsUl = document.getElementById('taskNodoItems');
    const items = nodo.items || [];
    itemsUl.innerHTML = items.length
      ? items.map(it => {
          const opt = it.obligatorio ? '' : ' <span class="text-muted">(opcional)</span>';
          return `<li>${escapeHtml(it.nombre)} <span class="text-muted">(${escapeHtml(it.categoria)})</span>${opt}</li>`;
        }).join('')
      : `<li class="text-muted">Sin ítems configurados.</li>`;

    const evidWrap = document.getElementById('taskEvidenciasWrap');
    const evRows = document.getElementById('taskEvidenciasRows');
    const evidenceItems = items.filter(x => x.requiere_evidencia);

    if (evidenceItems.length) {
      evidWrap.style.display = 'block';

      evRows.innerHTML = evidenceItems.map((it) => {
        const nodoItemId = it.nodo_item_id ?? it.nodoItemId ?? it.id ?? null;
        const safeId = nodoItemId ? String(nodoItemId) : '';
        const opt = it.obligatorio ? 'Obligatorio' : 'Opcional';

        return `
          <div class="border rounded p-2"
               data-evi-row="${escapeHtml(safeId)}"
               data-item-nombre="${escapeHtml(it.nombre)}"
               data-obligatorio="${it.obligatorio ? 1 : 0}"
               data-requiere-evidencia="${it.requiere_evidencia ? 1 : 0}"
               data-has-file="0">
            <div class="d-flex align-items-start justify-content-between gap-2">
              <div class="small fw-semibold">
                ${escapeHtml(it.nombre)}
                <span class="text-muted">(${escapeHtml(it.categoria)})</span>
                <span class="text-muted">- ${escapeHtml(opt)}</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                ${renderEvidenceBadge('PENDIENTE')}
              </div>
            </div>

            <div class="small text-muted mt-1">
              1 archivo por ítem (Pendiente/Subido/En revisión/Aprobado/Rechazado).
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
              <label class="btn btn-sm gp-btn-primary mb-0 ${safeId ? '' : 'disabled'}" style="display:inline-flex;align-items:center;gap:.4rem;">
                <i class="bi bi-paperclip"></i> Seleccionar
                <input class="js-evi-input"
                       type="file"
                       data-item-id="${escapeHtml(safeId)}"
                       ${safeId ? '' : 'disabled'}
                       style="display:none;">
              </label>

              <button type="button"
                      class="btn btn-sm gp-btn-primary js-upload-evidence"
                      data-item-id="${escapeHtml(safeId)}"
                      ${safeId ? '' : 'disabled'}>
                <i class="bi bi-upload me-1"></i> Subir
              </button>

              <a class="btn btn-sm gp-btn-primary js-evi-download d-none"
                 href="#"
                 target="_blank"
                 data-item-id="${escapeHtml(safeId)}">
                <i class="bi bi-download me-1"></i> Descargar
              </a>

              <span class="small text-muted js-evi-fileinfo" data-item-id="${escapeHtml(safeId)}"></span>
            </div>

            <div class="alert alert-danger d-none mt-2 py-1 small js-evi-error" data-item-id="${escapeHtml(safeId)}"></div>
          </div>
        `;
      }).join('');
    } else {
      evidWrap.style.display = 'none';
      evRows.innerHTML = '';
    }

    const trans = (nodo.transiciones || []).filter(t => (t.etiqueta ?? '').toString().trim() !== '');
    const transWrap = document.getElementById('taskTransWrap');
    const transBtns = document.getElementById('taskTransButtons');

    if (trans.length) {
      transWrap.style.display = 'block';
      transBtns.innerHTML = trans.map(t => `
        <button type="button"
                class="btn btn-sm gp-btn-primary js-task-trans"
                data-next-nodo-id="${t.nodo_destino_id}"
                data-is-end="${t.is_end ? 1 : 0}"
                title="${escapeHtml(t.nodo_destino_nombre || '')}">
          ${escapeHtml(t.etiqueta || 'Continuar')}
        </button>
      `).join('');
    } else {
      transWrap.style.display = 'none';
      transBtns.innerHTML = '';
    }

    updateTransitionButtonsState();
  }

  async function loadNodoMeta(projectId, nodoIdOrNull) {
    const qs = nodoIdOrNull ? `?nodo_id=${encodeURIComponent(nodoIdOrNull)}` : '';
    const res = await fetch(`/projects/${projectId}/start-node${qs}`, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    });
    if (!res.ok) return null;
    const data = await res.json().catch(() => null);
    if (!data || !data.ok) return null;
    return data.nodo || null;
  }

  window.openCreateTaskModal = async function () {
    document.getElementById('taskModalTitle').textContent = 'Nueva tarea';
    document.getElementById('task_id').value = '';
    document.getElementById('taskError').classList.add('d-none');

    resetNodoUI();

    document.getElementById('task_title').value = '';
    document.getElementById('task_description').value = '';
    document.getElementById('task_priority').value = '';
    document.getElementById('task_start_at').value = '';
    document.getElementById('task_due_at').value = '';

    const projectId = document.getElementById('task_project_id')?.value;
    if (!projectId) return;

    try {
      const nodo = await loadNodoMeta(projectId, null);
      if (nodo) {
        console.log('Nodo cargado para modal:', nodo);
        console.log('Transiciones recibidas:', nodo.transiciones || []);
        renderNodoMeta(nodo);
        document.getElementById('task_title').value = nodo.nombre || 'Inicio';
        document.getElementById('task_description').value = nodo.descripcion || '';
      }
    } catch (e) {
      console.error(e);
    }
  };

  window.openEditTaskModal = async function (task) {
    document.getElementById('taskModalTitle').textContent = 'Editar tarea';
    document.getElementById('task_id').value = task.id || '';
    document.getElementById('task_title').value = task.title || '';
    document.getElementById('task_description').value = task.description || '';
    document.getElementById('task_status_id').value = task.status_id || '';
    document.getElementById('task_priority').value = (task.priority ?? '');
    document.getElementById('task_start_at').value = (task.start_at ?? '');
    document.getElementById('task_due_at').value = (task.due_at ?? '');
    document.getElementById('taskError').classList.add('d-none');
    document.getElementById('task_status_name').value = (task.status_name || task.status || '');

    resetNodoUI();

    const projectId = document.getElementById('task_project_id')?.value;
    const nodoId = task.nodo_id || null;

    if (projectId && nodoId) {
      try {
        const nodo = await loadNodoMeta(projectId, nodoId);
        if (nodo) renderNodoMeta(nodo, task);
      } catch (e) {
        console.error(e);
      }
    }

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTask')).show();

    if (typeof window.loadTaskEvidences === 'function' && task.id) {
      window.loadTaskEvidences(task.id);
    }
  };

  window.openTaskEvidencesModal = async function (task) {
    await window.openEditTaskModal(task);

    setTimeout(() => {
      const wrap = document.getElementById('taskEvidenciasWrap');
      if (!wrap) return;

      wrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
      wrap.classList.add('border', 'rounded', 'p-2');

      setTimeout(() => {
        wrap.classList.remove('border', 'rounded', 'p-2');
      }, 1800);
    }, 250);
  };

  document.addEventListener('click', (e) => {
    if (e.target.closest('.js-open-files')) return;
    if (e.target.closest('#modalFiles')) return;
    if (e.target.closest('#modalTaskChain')) return;
    if (e.target.closest('button, a, input, select, textarea, label')) return;

    const el = e.target.closest('.js-task');
    if (!el) return;

    const raw = el.getAttribute('data-task');
    if (!raw) return;

    try {
      const task = JSON.parse(raw);
      window.openEditTaskModal(task);
    } catch (err) {
      console.error('data-task inválido', err);
    }
  });

  async function refreshProjectArea() {
    const projectId = document.getElementById('task_project_id')?.value;
    const viewMode = (window.GP_DASHBOARD?.viewMode || 'tablero');
    if (!projectId) return;

    const url = `/dashboard?project_id=${projectId}&view=${encodeURIComponent(viewMode)}`;
    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const html = await res.text();

    const tmp = document.createElement('div');
    tmp.innerHTML = html;

    const newArea = tmp.querySelector('#gpProjectArea');
    const curArea = document.querySelector('#gpProjectArea');

    if (newArea && curArea) {
      curArea.innerHTML = newArea.innerHTML;
    } else {
      window.location.href = url;
    }
  }
  window.refreshProjectArea = refreshProjectArea;
  window.refreshTaskTransitionState = updateTransitionButtonsState;

  document.getElementById('formTask')?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const projectId = document.getElementById('task_project_id').value;
    const id = document.getElementById('task_id').value;

    const statusSelect = document.getElementById('task_status_id');
    const selectedOption = statusSelect?.options?.[statusSelect.selectedIndex] || null;
    const selectedStatusText = (selectedOption?.text || '').trim().toUpperCase();

    const payload = {
      title: document.getElementById('task_title').value,
      description: document.getElementById('task_description').value || null,
      status_id: document.getElementById('task_status_id').value,
      priority: document.getElementById('task_priority').value || null,
      start_at: document.getElementById('task_start_at').value || null,
      due_at: document.getElementById('task_due_at').value || null,
      nodo_id: document.getElementById('task_nodo_id').value || null,
      view: (window.GP_DASHBOARD?.viewMode || 'tablero'),
    };

    const isDoneStatus =
      selectedStatusText === 'DONE' ||
      selectedStatusText === 'FINALIZADO' ||
      selectedStatusText === 'COMPLETADO';

    if (isDoneStatus) {
      const ok = confirm('¿Seguro que deseas finalizar esta tarea? Se marcará como Done.');
      if (!ok) return;
    }

    let url, method;
    if (id) { url = `/tasks/${id}`; method = 'PATCH'; }
    else { url = `/projects/${projectId}/tasks`; method = 'POST'; }

    try {
      const res = await fetch(url, {
        method,
        headers: csrfHeaders({ 'Content-Type': 'application/json', 'Accept': 'application/json' }),
        body: JSON.stringify(payload)
      });

      if (!res.ok) {
        const data = await res.json().catch(() => ({}));
        const msg = data.message || 'No se pudo guardar la tarea.';
        const errEl = document.getElementById('taskError');
        errEl.textContent = msg;
        errEl.classList.remove('d-none');
        return;
      }

      bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTask')).hide();
      await refreshProjectArea();

    } catch (err) {
      console.error(err);
      const errEl = document.getElementById('taskError');
      errEl.textContent = 'Error de red o servidor.';
      errEl.classList.remove('d-none');
    }
  });
})();
