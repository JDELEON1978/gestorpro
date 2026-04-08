(function () {
  const { escapeHtml, csrfHeaders } = window.GP;

  function chainStatusBadge(st) {
    const v = (st || '').toString().toUpperCase();
    const map = { 'TO DO': 'text-bg-secondary', 'IN PROGRESS': 'text-bg-warning', 'DONE': 'text-bg-success' };
    const cls = map[v] || 'text-bg-secondary';
    return `<span class="badge ${cls}">${escapeHtml(v || '—')}</span>`;
  }

  function fmtMinutes(min) {
    if (min == null || isNaN(min)) return '—';
    min = parseInt(min, 10);
    const d = Math.floor(min / 1440); min -= d * 1440;
    const h = Math.floor(min / 60); min -= h * 60;
    const m = min;

    const parts = [];
    if (d) parts.push(`${d}d`);
    if (h || d) parts.push(`${h}h`);
    parts.push(`${m}m`);
    return parts.join(' ');
  }

  async function loadStartTasks() {
    const projectId = document.getElementById('task_project_id')?.value;
    const sel = document.getElementById('cmbStartTasks');
    if (!projectId || !sel) return;

    sel.innerHTML = `<option value="">— Tareas de inicio —</option>`;

    try {
      const res = await fetch(`/projects/${projectId}/start-tasks`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
      });

      const data = await res.json().catch(() => ({}));
      if (!res.ok || !data.ok) return;

      (data.tasks || []).forEach(t => {
        const opt = document.createElement('option');
        opt.value = String(t.id);
        opt.textContent = t.title || (`Tarea #${t.id}`);
        opt.dataset.task = JSON.stringify(t);
        sel.appendChild(opt);
      });
    } catch (err) {
      console.error(err);
    }
  }

  function renderChainTable(tasks, totals) {
    const tbody = document.getElementById('chainTableBody');
    const tPlan = document.getElementById('chainTotalPlanned');
    const tReal = document.getElementById('chainTotalActual');

    if (!tbody) return;

    if (!tasks || !tasks.length) {
      tbody.innerHTML = `<tr><td colspan="9" class="text-muted">Sin datos</td></tr>`;
      if (tPlan) tPlan.textContent = '—';
      if (tReal) tReal.textContent = '—';
      return;
    }

    tbody.innerHTML = tasks.map((t, idx) => `
      <tr>
        <td class="text-muted">${idx + 1}</td>
        <td class="text-truncate" style="max-width:360px;">
          <div class="fw-semibold">${escapeHtml(t.title || ('Tarea #' + t.id))}</div>
          <div class="small text-muted">ID: ${t.id} ${t.parent_task_id ? `• Parent: ${t.parent_task_id}` : ''}</div>
        </td>
        <td>${chainStatusBadge(t.status_name || t.status)}</td>
        <td class="text-center">${escapeHtml(String(t.priority ?? '—'))}</td>
        <td class="small text-muted">${escapeHtml((t.start_at || '—').toString().slice(0, 10))}</td>
        <td class="small text-muted">${escapeHtml((t.due_at || '—').toString().slice(0, 10))}</td>
        <td>${fmtMinutes(t.planned_minutes)}</td>
        <td>${fmtMinutes(t.actual_minutes)}</td>
        <td class="text-end">
          <button type="button" class="btn btn-sm gp-btn js-open-chain-task" data-task='${escapeHtml(JSON.stringify(t))}'>
            Abrir
          </button>
        </td>
      </tr>
    `).join('');

    if (tPlan) tPlan.textContent = fmtMinutes(totals?.planned_minutes);
    if (tReal) tReal.textContent = fmtMinutes(totals?.actual_minutes);
  }

  async function openChainModal(rootTaskId, rootTitle) {
    const modalEl = document.getElementById('modalTaskChain');
    const bodyEl = document.getElementById('chainTableBody');
    const errEl = document.getElementById('chainError');
    const titleEl = document.getElementById('chainTitle');
    const hidden = document.getElementById('chain_root_task_id');

    if (errEl) {
      errEl.classList.add('d-none');
      errEl.textContent = '';
    }

    if (bodyEl) {
      bodyEl.innerHTML = `<tr><td colspan="9" class="text-muted">Cargando recorrido...</td></tr>`;
    }

    if (hidden) {
      hidden.value = String(rootTaskId || '');
      console.log('chain_root_task_id cargado =', hidden.value);
    }

    if (titleEl) {
      titleEl.textContent = rootTitle || (`Tarea #${rootTaskId}`);
    }

    if (modalEl) {
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    try {
      const res = await fetch(`/tasks/${rootTaskId}/chain`, {
        headers: csrfHeaders({ 'Accept': 'application/json' })
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok || !data.ok) {
        if (errEl) {
          errEl.textContent = data.message || 'No se pudo cargar el recorrido.';
          errEl.classList.remove('d-none');
        }
        renderChainTable([], null);
        return;
      }

      renderChainTable(data.tasks || [], data.totals || {});
    } catch (err) {
      console.error(err);
      if (errEl) {
        errEl.textContent = 'Error de red o servidor.';
        errEl.classList.remove('d-none');
      }
      renderChainTable([], null);
    }
  }

  document.addEventListener('change', (e) => {
    const sel = e.target.closest('#cmbStartTasks');
    if (!sel) return;

    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) return;

    let task = null;
    try { task = JSON.parse(opt.dataset.task || '{}'); } catch {}

    const rootId = opt.value;
    const title = task?.title || opt.textContent;

    openChainModal(rootId, title);
    sel.value = '';
  });

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-open-chain-audit');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const taskId = document.getElementById('chain_root_task_id')?.value || '';
    console.log('Click auditoría chain. taskId =', taskId);

    if (!taskId) {
      console.warn('No existe chain_root_task_id');
      return;
    }

    if (typeof window.openAuditReview === 'function') {
      window.openAuditReview(taskId);
    } else {
      console.error('window.openAuditReview no está disponible');
    }
  });

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-open-chain-task');
    if (!btn) return;

    try {
      const task = JSON.parse(btn.getAttribute('data-task') || '{}');
      if (task?.id && typeof window.openEditTaskModal === 'function') {
        window.openEditTaskModal(task);
      }
    } catch (err) {
      console.error(err);
    }
  });

  document.addEventListener('DOMContentLoaded', () => loadStartTasks());

  const _refresh = window.refreshProjectArea;
  if (typeof _refresh === 'function') {
    window.refreshProjectArea = async function () {
      await _refresh();
      await loadStartTasks();
    };
  } else {
    window.refreshProjectArea = async function () {
      await loadStartTasks();
    };
  }

  window.loadStartTasks = loadStartTasks;
})();