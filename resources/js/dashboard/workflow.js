(function () {
  const { csrfHeaders } = window.GP;

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.js-task-trans');
    if (!btn) return;

    const taskId = document.getElementById('task_id')?.value;
    const projectId = document.getElementById('task_project_id')?.value;
    const nextNodoId = btn.getAttribute('data-next-nodo-id');
    const rawIsEnd = btn.getAttribute('data-is-end');
    const isEnd = String(rawIsEnd || '').trim() === '1';

    console.log('Click transicion workflow', {
      taskId,
      projectId,
      nextNodoId,
      rawIsEnd,
      isEnd,
      buttonHtml: btn.outerHTML
    });

    if (!taskId || !projectId) return;

    const msg = isEnd
      ? 'Seguro que deseas finalizar esta tarea? Se marcara como Done.'
      : 'Seguro que deseas terminar esta tarea y crear automaticamente la siguiente?';

    console.log('Mensaje confirm seleccionado:', msg);

    if (!confirm(msg)) return;

    try {
      const res = await fetch(`/tasks/${taskId}/advance`, {
        method: 'POST',
        headers: csrfHeaders({ 'Accept': 'application/json', 'Content-Type': 'application/json' }),
        body: JSON.stringify({ next_nodo_id: nextNodoId })
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok || !data.ok) {
        alert(data.message || 'No se pudo avanzar la tarea.');
        return;
      }

      bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTask')).hide();

      if (typeof window.refreshProjectArea === 'function') {
        await window.refreshProjectArea();
      }
    } catch (err) {
      console.error(err);
      alert('Error de red o servidor.');
    }
  });
})();
