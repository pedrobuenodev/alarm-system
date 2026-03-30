<?php
$typeLabels = ['voltage' => 'Tensão', 'current' => 'Corrente', 'oil' => 'Óleo'];
$typeBadge  = ['voltage' => 'primary', 'current' => 'info', 'oil' => 'warning'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="bi bi-cpu me-2 text-primary"></i>Equipamentos</h2>
    <a href="/equipments/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Novo Equipamento
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($equipments)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-cpu fs-1 d-block mb-2"></i>
                Nenhum equipamento cadastrado.
                <a href="/equipments/create" class="d-block mt-2">Cadastrar agora</a>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>Número de Série</th>
                        <th>Tipo</th>
                        <th>Cadastrado em</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($equipments as $eq): ?>
                    <tr>
                        <td class="text-muted small"><?= $eq['id'] ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($eq['name']) ?></td>
                        <td><code><?= htmlspecialchars($eq['serial']) ?></code></td>
                        <td>
                            <span class="badge bg-<?= $typeBadge[$eq['type']] ?? 'secondary' ?>">
                                <?= $typeLabels[$eq['type']] ?? $eq['type'] ?>
                            </span>
                        </td>
                        <td><small><?= date('d/m/Y H:i', strtotime($eq['created_at'])) ?></small></td>
                        <td class="text-end">
                            <a href="/equipments/<?= $eq['id'] ?>/edit" class="btn btn-sm btn-outline-secondary me-1" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger btn-delete"
                                    data-url="/equipments/<?= $eq['id'] ?>"
                                    data-name="<?= htmlspecialchars($eq['name']) ?>"
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

<!-- Modal de confirmação de exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Deseja excluir o equipamento <strong id="deleteName"></strong>? Esta ação não pode ser desfeita.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <i class="bi bi-trash me-1"></i>Excluir
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal       = new bootstrap.Modal(document.getElementById('deleteModal'));
    const deleteName  = document.getElementById('deleteName');
    const confirmBtn  = document.getElementById('confirmDelete');
    let   deleteUrl   = '';

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', () => {
            deleteUrl         = btn.dataset.url;
            deleteName.textContent = btn.dataset.name;
            modal.show();
        });
    });

    confirmBtn.addEventListener('click', async () => {
        confirmBtn.disabled = true;
        try {
            const res  = await fetch(deleteUrl, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: '_method=DELETE' });
            const data = await res.json();
            if (data.success) { location.reload(); }
            else { showToast(data.error, 'danger'); modal.hide(); }
        } finally {
            confirmBtn.disabled = false;
        }
    });
});
</script>
