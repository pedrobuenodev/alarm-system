<?php
$typeLabels = ['voltage' => 'Tensão', 'current' => 'Corrente', 'oil' => 'Óleo'];
$classLabels = ['urgent' => 'Urgente', 'emergent' => 'Emergente', 'ordinary' => 'Ordinário'];
$classBadge  = ['urgent' => 'danger', 'emergent' => 'warning', 'ordinary' => 'secondary'];
$actionLabels = [
    'created' => ['label' => 'Criado', 'icon' => 'bi-plus-circle text-success'],
    'updated' => ['label' => 'Atualizado', 'icon' => 'bi-pencil text-warning'],
    'deleted' => ['label' => 'Excluído', 'icon' => 'bi-trash text-danger'],
    'activated'   => ['label' => 'Ativado', 'icon' => 'bi-toggle-on text-success'],
    'deactivated' => ['label' => 'Desativado', 'icon' => 'bi-toggle-off text-secondary'],
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard</h2>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box bg-primary-subtle rounded-3 p-3">
                    <i class="bi bi-cpu fs-3 text-primary"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold"><?= $totalEquipments ?></div>
                    <div class="text-muted small">Equipamentos</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box bg-warning-subtle rounded-3 p-3">
                    <i class="bi bi-bell fs-3 text-warning"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold"><?= $totalAlarms ?></div>
                    <div class="text-muted small">Alarmes</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box bg-danger-subtle rounded-3 p-3">
                    <i class="bi bi-bell-fill fs-3 text-danger"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold"><?= $activeAlarms ?></div>
                    <div class="text-muted small">Alarmes Ativos</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-box bg-success-subtle rounded-3 p-3">
                    <i class="bi bi-activity fs-3 text-success"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold"><?= $totalEvents ?></div>
                    <div class="text-muted small">Atuações Totais</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Top 3 -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent fw-semibold">
                <i class="bi bi-trophy-fill text-warning me-2"></i>Top 3 Alarmes que mais atuaram
            </div>
            <div class="card-body p-0">
                <?php if (empty($top3)): ?>
                    <p class="text-muted text-center py-4">Nenhuma atuação registrada.</p>
                <?php else: ?>
                    <ol class="list-group list-group-flush list-group-numbered">
                        <?php foreach ($top3 as $item): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-semibold"><?= htmlspecialchars($item['description']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($item['equipment_name']) ?></small>
                            </div>
                            <span class="badge bg-<?= $classBadge[$item['classification']] ?> rounded-pill me-2">
                                <?= $classLabels[$item['classification']] ?>
                            </span>
                            <span class="badge bg-dark rounded-pill"><?= $item['activation_count'] ?>x</span>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Audit log recente -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent fw-semibold d-flex justify-content-between">
                <span><i class="bi bi-journal-text me-2"></i>Atividade Recente</span>
                <small class="text-muted">últimas 10 ações</small>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recent)): ?>
                    <p class="text-muted text-center py-4">Nenhuma atividade ainda.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Entidade</th>
                                <th>Ação</th>
                                <th>IP</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent as $log): ?>
                            <?php $act = $actionLabels[$log['action']] ?? ['label' => $log['action'], 'icon' => 'bi-dot']; ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($log['entity']) ?> #<?= $log['entity_id'] ?></span></td>
                                <td><i class="bi <?= $act['icon'] ?> me-1"></i><?= $act['label'] ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars($log['ip']) ?></small></td>
                                <td><small><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
