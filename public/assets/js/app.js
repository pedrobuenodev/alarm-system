/**
 * Alarm System – Global JS Utilities
 * Expõe showToast() globalmente para reutilização nas views.
 */

'use strict';

/**
 * Exibe um Bootstrap Toast com mensagem e tipo (success | danger | warning | info).
 *
 * @param {string} message
 * @param {'success'|'danger'|'warning'|'info'} type
 */
window.showToast = function (message, type = 'success') {
    const containerId = 'global-toast-container';
    let container = document.getElementById(containerId);

    if (!container) {
        container = document.createElement('div');
        container.id = containerId;
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '1100';
        document.body.appendChild(container);
    }

    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-bg-${type} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body fw-semibold">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>`;

    container.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
};

/**
 * Confirmação genérica de exclusão via fetch + método DELETE.
 * Pode ser reutilizada em qualquer view.
 *
 * @param {string} url
 * @param {Function} onSuccess
 */
window.confirmDelete = async function (url, onSuccess) {
    try {
        const res  = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: '_method=DELETE',
        });
        const data = await res.json();

        if (data.success) {
            onSuccess();
        } else {
            showToast(data.error || 'Erro ao excluir.', 'danger');
        }
    } catch {
        showToast('Erro de comunicação com o servidor.', 'danger');
    }
};
