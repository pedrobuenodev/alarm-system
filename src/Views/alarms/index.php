<?php
$classLabels = ['urgent' => 'Urgente', 'emergent' => 'Emergente', 'ordinary' => 'Ordinário'];
$classBadge  = ['urgent' => 'danger',  'emergent' => 'warning',   'ordinary' => 'secondary'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="bi bi-bell me-2 text-primary"></i>Gerenciar Alarmes</h2>
    <a href="/alarms/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Novo Alarme
    </a>
</div>

<!-- Toast de feedback -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1100">
    <div id="feedbackToast" class="toast align-items-center border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="toastMessage"></div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($alarms)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-bell-slash fs-1 d-block mb-2"></i>
                Nenhum alarme cadastrado.
                <a href="/alarms/create" class="d-block mt-2">Cadastrar agora</a>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Descrição</th>
                        <th>Classificação</th>
                        <th>Equipamento</th>
                        <th>Status</th>
                        <th>Cadastrado em</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alarms as $alarm): ?>
                    <tr id="alarm-row-<?= $alarm['id'] ?>">
                        <td class="text-muted small"><?= $alarm['id'] ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($alarm['description']) ?></td>
                        <td>
                            <span class="badge bg-<?= $classBadge[$alarm['classification']] ?>">
                                <?= $classLabels[$alarm['classification']] ?>
                            </span>
                        </td>
                        <td>
                            <small><?= htmlspecialchars($alarm['equipment_name']) ?></small>
                            <br><code class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($alarm['equipment_serial']) ?></code>
                        </td>
                        <td>
                            <span class="badge status-badge <?= $alarm['status'] === 'on' ? 'bg-success' : 'bg-secondary' ?>"
                                  id="status-badge-<?= $alarm['id'] ?>">
                                <i class="bi <?= $alarm['status'] === 'on' ? 'bi-toggle-on' : 'bi-toggle-off' ?> me-1"></i>
                                <?= $alarm['status'] === 'on' ? 'Ativo' : 'Inativo' ?>
                            </span>
                        </td>
                        <td><small><?= date('d/m/Y H:i', strtotime($alarm['created_at'])) ?></small></td>
                        <td class="text-end">
                            <!-- Toggle Ativo/Inativo -->
                            <button class="btn btn-sm <?= $alarm['status'] === 'on' ? 'btn-success' : 'btn-outline-success' ?> btn-toggle me-1"
                                    data-id="<?= $alarm['id'] ?>"
                                    data-status="<?= $alarm['status'] ?>"
                                    data-desc="<?= htmlspecialchars($alarm['description']) ?>"
                                    data-classification="<?= $alarm['classification'] ?>"
                                    title="<?= $alarm['status'] === 'on' ? 'Desativar' : 'Ativar' ?>"
                                    aria-label="<?= $alarm['status'] === 'on' ? 'Desativar alarme' : 'Ativar alarme' ?>">
                                <i class="bi <?= $alarm['status'] === 'on' ? 'bi-toggle-on' : 'bi-toggle-off' ?>"></i>
                            </button>
                            <a href="/alarms/<?= $alarm['id'] ?>/edit" class="btn btn-sm btn-outline-secondary me-1" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-danger btn-delete"
                                    data-id="<?= $alarm['id'] ?>"
                                    data-name="<?= htmlspecialchars($alarm['description']) ?>"
                                    title="Excluir">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal confirmação exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Deseja excluir o alarme <strong id="deleteAlarmName"></strong>?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDelete"><i class="bi bi-trash me-1"></i>Excluir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal confirmação ativação urgente -->
<div class="modal fade" id="urgentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Alarme Urgente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Você está prestes a ativar o alarme urgente:</p>
                <p class="fw-bold" id="urgentAlarmDesc"></p>
                <p class="text-danger">Um e-mail de notificação será enviado para <strong>abcd@abc.com.br</strong>.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmUrgent"><i class="bi bi-bell-fill me-1"></i>Confirmar Ativação</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const deleteModal  = new bootstrap.Modal(document.getElementById('deleteModal'));
    const urgentModal  = new bootstrap.Modal(document.getElementById('urgentModal'));
    const toastEl      = document.getElementById('feedbackToast');
    const toast        = new bootstrap.Toast(toastEl, { delay: 3500 });

    function showToastMsg(msg, type = 'success') {
        toastEl.className = `toast align-items-center text-bg-${type} border-0`;
        document.getElementById('toastMessage').textContent = msg;
        toast.show();
    }

    // -- Toggle ativar/desativar --
    let pendingToggleBtn = null;

    async function doToggle(btn) {
        const id     = btn.dataset.id;
        const status = btn.dataset.status;
        const action = status === 'off' ? 'activate' : 'deactivate';

        btn.disabled = true;
        try {
            const res  = await fetch(`/alarms/${id}/${action}`, { method: 'POST' });
            const data = await res.json();
            if (data.success) {
                const newStatus = data.status;
                const badge     = document.getElementById(`status-badge-${id}`);
                btn.dataset.status = newStatus;

                if (newStatus === 'on') {
                    badge.className  = 'badge status-badge bg-success';
                    badge.innerHTML  = '<i class="bi bi-toggle-on me-1"></i>Ativo';
                    btn.className    = 'btn btn-sm btn-success btn-toggle me-1';
                    btn.innerHTML    = '<i class="bi bi-toggle-on"></i>';
                    btn.title        = 'Desativar';
                } else {
                    badge.className  = 'badge status-badge bg-secondary';
                    badge.innerHTML  = '<i class="bi bi-toggle-off me-1"></i>Inativo';
                    btn.className    = 'btn btn-sm btn-outline-success btn-toggle me-1';
                    btn.innerHTML    = '<i class="bi bi-toggle-off"></i>';
                    btn.title        = 'Ativar';
                }
                showToastMsg(`Alarme ${newStatus === 'on' ? 'ativado' : 'desativado'} com sucesso.`);
            } else {
                showToastMsg(data.error, 'danger');
            }
        } catch {
            showToastMsg('Erro de comunicação com o servidor.', 'danger');
        } finally {
            btn.disabled = false;
        }
    }

    document.querySelectorAll('.btn-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.dataset.status === 'off' && btn.dataset.classification === 'urgent') {
                pendingToggleBtn = btn;
                document.getElementById('urgentAlarmDesc').textContent = btn.dataset.desc;
                urgentModal.show();
            } else {
                doToggle(btn);
            }
        });
    });

    document.getElementById('confirmUrgent').addEventListener('click', async () => {
        urgentModal.hide();
        if (pendingToggleBtn) await doToggle(pendingToggleBtn);
        pendingToggleBtn = null;
    });

    // -- Delete --
    let deleteId = null;
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', () => {
            deleteId = btn.dataset.id;
            document.getElementById('deleteAlarmName').textContent = btn.dataset.name;
            deleteModal.show();
        });
    });

    document.getElementById('confirmDelete').addEventListener('click', async () => {
        const btn = document.getElementById('confirmDelete');
        btn.disabled = true;
        try {
            const res  = await fetch(`/alarms/${deleteId}`, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: '_method=DELETE' });
            const data = await res.json();
            if (data.success) { location.reload(); }
            else { showToastMsg(data.error, 'danger'); deleteModal.hide(); }
        } finally {
            btn.disabled = false;
        }
    });
});
</script>
