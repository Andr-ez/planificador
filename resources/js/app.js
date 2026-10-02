const api = {
    async request(path, options = {}) {
        const response = await fetch(`/api/${path}`, {
            headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
            ...options,
        });
        const body = await response.json().catch(() => ({}));
        if (!response.ok) {
            const error = new Error(body.message || 'No se pudo completar la solicitud.');
            error.errors = body.errors || {};
            throw error;
        }
        return body;
    },
};

const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
}[character]));

const formatDate = (value) => value
    ? new Intl.DateTimeFormat('es', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value))
    : 'Sin fecha';

const showMessage = (element, message, error = false) => {
    element.textContent = message;
    element.className = `mt-6 rounded-lg border p-4 text-sm ${error ? 'border-red-200 bg-red-50 text-red-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700'}`;
};

const validationMessage = (error) => Object.values(error.errors || {}).flat().join(' ') || error.message;

const subjectCard = (subject) => {
    const progress = Math.min(100, Math.max(0, Number(subject.progress || 0)));
    return `<article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div><h2 class="font-semibold">${escapeHtml(subject.name)}</h2><p class="mt-1 text-sm text-slate-500">${subject.credits} créditos</p></div>
            <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">${progress.toFixed(0)}%</span>
        </div>
        <div class="mt-5"><div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-indigo-600" style="width:${progress}%"></div></div>
        <p class="mt-2 text-xs text-slate-500">${Number(subject.total_hours_required || 0)} horas objetivo</p></div>
        <a href="/subjects/${subject.id}" class="mt-5 inline-flex text-sm font-semibold text-indigo-600 hover:text-indigo-700">Ver tareas &rarr;</a>
    </article>`;
};

const initDashboard = () => {
    const root = document.querySelector('[data-dashboard]');
    if (!root) return;
    const grid = root.querySelector('[data-subjects-grid]');
    const loading = root.querySelector('[data-subjects-loading]');
    const empty = root.querySelector('[data-subjects-empty]');
    const message = root.querySelector('[data-dashboard-message]');
    const modal = root.querySelector('[data-subject-modal]');
    const form = root.querySelector('[data-subject-form]');
    const errors = root.querySelector('[data-form-errors]');
    const openModal = () => { modal.classList.remove('hidden'); modal.classList.add('flex'); form.elements.name.focus(); };
    const closeModal = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); form.reset(); errors.classList.add('hidden'); };
    root.querySelectorAll('[data-open-subject-modal]').forEach((button) => button.addEventListener('click', openModal));
    root.querySelectorAll('[data-close-subject-modal]').forEach((button) => button.addEventListener('click', closeModal));
    const render = (subjects) => {
        loading.classList.add('hidden');
        empty.classList.toggle('hidden', subjects.length > 0);
        grid.classList.toggle('hidden', subjects.length === 0);
        grid.innerHTML = subjects.map(subjectCard).join('');
    };
    api.request('subjects').then(render).catch((error) => {
        loading.classList.add('hidden');
        showMessage(message, error.message, true);
    });
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = form.querySelector('[type="submit"]');
        button.disabled = true;
        errors.classList.add('hidden');
        try {
            const subject = await api.request('subjects', { method: 'POST', body: JSON.stringify({ name: form.elements.name.value, credits: Number(form.elements.credits.value) }) });
            closeModal();
            const subjects = await api.request('subjects');
            render(subjects);
            showMessage(message, `${subject.name || 'La materia'} se creó correctamente.`);
        } catch (error) {
            errors.textContent = validationMessage(error);
            errors.classList.remove('hidden');
        } finally { button.disabled = false; }
    });
};

const taskCard = (task) => `<article class="rounded-xl border ${task.is_inactive ? 'border-amber-300' : 'border-slate-200'} bg-white p-5 shadow-sm" data-task-id="${task.id}">
    ${task.is_inactive ? '<div class="mb-4 rounded-lg bg-amber-50 px-3 py-2 text-sm font-medium text-amber-800">Atención: esta tarea no registra avances desde hace más de 3 días.</div>' : ''}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div><h2 class="font-semibold">${escapeHtml(task.title)}</h2><p class="mt-1 text-sm text-slate-500">Límite: ${formatDate(task.due_date)}</p></div>
        <span class="w-fit rounded-full px-2.5 py-1 text-xs font-semibold ${task.status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700'}">${task.status === 'completed' ? 'Completada' : 'Pendiente'}</span>
    </div>
    <ul class="mt-5 space-y-2 border-t border-slate-100 pt-4">${(task.task_items || []).map((item) => `<li><label class="flex items-center gap-3 text-sm ${item.is_completed ? 'text-slate-400 line-through' : 'text-slate-700'}"><input type="checkbox" data-toggle-item="${item.id}" ${item.is_completed ? 'checked' : ''} class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"><span>${escapeHtml(item.description)}</span></label></li>`).join('')}</ul>
    <form data-add-item-form class="mt-4 flex gap-2"><input name="description" required placeholder="Añadir ítem" class="min-w-0 flex-1 rounded-lg border-slate-300 px-3 py-2 text-sm"><button class="rounded-lg border border-indigo-200 px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">Añadir</button></form>
</article>`;

const initSubjectDetail = async () => {
    const root = document.querySelector('[data-subject-detail]');
    if (!root) return;
    const id = root.dataset.subjectId;
    const list = root.querySelector('[data-tasks-list]');
    const loading = root.querySelector('[data-tasks-loading]');
    const empty = root.querySelector('[data-tasks-empty]');
    const message = root.querySelector('[data-task-message]');
    let tasks = [];
    const renderTasks = () => {
        empty.classList.toggle('hidden', tasks.length > 0);
        list.innerHTML = tasks.map(taskCard).join('');
        list.querySelectorAll('[data-toggle-item]').forEach((checkbox) => checkbox.addEventListener('change', async () => {
            checkbox.disabled = true;
            try { await api.request(`task-items/${checkbox.dataset.toggleItem}/toggle`, { method: 'PATCH', body: '{}' }); window.location.reload(); }
            catch (error) { checkbox.checked = !checkbox.checked; showMessage(message, error.message, true); checkbox.disabled = false; }
        }));
        list.querySelectorAll('[data-add-item-form]').forEach((itemForm) => itemForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const task = itemForm.closest('[data-task-id]');
            try { await api.request(`tasks/${task.dataset.taskId}/items`, { method: 'POST', body: JSON.stringify({ description: itemForm.elements.description.value }) }); window.location.reload(); }
            catch (error) { showMessage(message, error.message, true); }
        }));
    };
    try {
        const subjects = await api.request('subjects');
        const subject = subjects.find((item) => String(item.id) === id);
        if (!subject) throw new Error('No se encontró la materia.');
        root.querySelector('[data-subject-name]').textContent = subject.name;
        root.querySelector('[data-subject-meta]').textContent = `${subject.credits} créditos · ${Number(subject.progress || 0).toFixed(0)}% de progreso`;
        tasks = await api.request(`subjects/${id}/tasks`);
        renderTasks();
    } catch (error) { showMessage(message, error.message, true); }
    finally { loading.classList.add('hidden'); }
    const panel = root.querySelector('[data-task-form-panel]');
    const itemList = root.querySelector('[data-task-items]');
    const addItem = () => { itemList.insertAdjacentHTML('beforeend', '<input name="task_items[]" required maxlength="255" placeholder="Descripción del ítem" class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm">'); };
    root.querySelector('[data-open-task-form]').addEventListener('click', () => { panel.classList.remove('hidden'); if (!itemList.children.length) addItem(); });
    root.querySelector('[data-close-task-form]').addEventListener('click', () => panel.classList.add('hidden'));
    root.querySelector('[data-add-task-item]').addEventListener('click', addItem);
    root.querySelector('[data-task-form]').addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        const errorBox = form.querySelector('[data-task-form-errors]');
        try {
            await api.request(`subjects/${id}/tasks`, { method: 'POST', body: JSON.stringify({ title: form.elements.title.value, due_date: form.elements.due_date.value, task_items: [...itemList.querySelectorAll('input')].map((input) => ({ description: input.value })) }) });
            window.location.reload();
        } catch (error) { errorBox.textContent = validationMessage(error); errorBox.classList.remove('hidden'); }
    });
};

initDashboard();
initSubjectDetail();
