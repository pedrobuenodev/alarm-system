<?php
$classLabels = ['urgent' => 'Urgente', 'emergent' => 'Emergente', 'ordinary' => 'Ordinário'];
$classBadge  = ['urgent' => 'danger',  'emergent' => 'warning',   'ordinary' => 'secondary'];

function sortLink(string $column, string $label, string $currentSort, string $currentDir): string {
    $newDir = ($currentSort === $column && $currentDir === 'ASC') ? 'DESC' : 'ASC';
    $icon   = '';
    if ($currentSort === $column) {
        $icon = $currentDir === 'ASC' ? ' <i class="bi bi-caret-up-fill"></i>' : ' <i class="bi bi-caret-down-fill"></i>';
    }
    $search = htmlspecialchars($_GET['search'] ?? '');
    return "<a href=\"/alarms/events?sort_by={$column}&sort_dir={$newDir}&search={$search}\" class=\"text-white text-decoration-none\">{$label}{$icon}</a>";
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="bi bi-activity me-2 text-primary"></i>Alarmes Atuados</h2>
    <a href="/alarms" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Gerenciar Alarmes
    </a>
</div>

<!-- Top 3 -->
<?php if (!empty($top3)): ?>
<div class="alert alert-warning d-flex gap-3 align-items-start mb-4 border-0 shadow-sm">
    <i class="bi bi-trophy-fill fs-4 text-warning mt-1 flex-shrink-0"></i>
    <div>
        <strong>Top 3 alarmes que mais atuaram:</strong>
        <ol class="mb-0 mt-1">
            <?php foreach ($top3 as $t): ?>
            <li>
                <span class="badge bg-<?= $classBadge[$t['classification']] ?> me-1"><?= $classLabels[$t['classification']] ?></span>
                <strong><?= htmlspecialchars($t['description']) ?></strong>
                &mdash; <?= htmlspecialchars($t['equipment_name']) ?>
                <span class="badge bg-dark ms-1"><?= $t['activation_count'] ?>x</span>
            </li>
            <?php endforeach; ?>
        </ol>
    </div>
</div>
<?php endif; ?>

<!-- Filtro -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2 px-3">
        <form method="GET" action="/alarms/events" class="d-flex gap-2 align-items-center">
            <input type="hidden" name="sort_by"  value="<?= htmlspecialchars($sortBy) ?>">
            <input type="hidden" name="sort_dir" value="<?= htmlspecialchars($sortDir) ?>">
            <label class="text-muted small mb-0 text-nowrap"><i class="bi bi-search me-1"></i>Filtrar:</label>
            <input type="text"
                   name="search"
                   class="form-control form-control-sm"
                   placeholder="Descrição do alarme..."
                   value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-sm btn-primary">Buscar</button>
            <?php if ($search): ?>
                <a href="/alarms/events" class="btn btn-sm btn-outline-secondary">Limpar</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tabela -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($events)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                <?= $search ? 'Nenhum resultado para "' . htmlspecialchars($search) . '".' : 'Nenhum alarme atuado ainda.' ?>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th><?= sortLink('entered_at', 'Entrada', $sortBy, $sortDir) ?></th>
                        <th><?= sortLink('exited_at',  'Saída',   $sortBy, $sortDir) ?></th>
                        <th><?= sortLink('status', 'Status', $sortBy, $sortDir) ?></th>
                        <th><?= sortLink('alarm_description', 'Alarme', $sortBy, $sortDir) ?></th>
                        <th><?= sortLink('equipment_name', 'Equipamento', $sortBy, $sortDir) ?></th>
                        <th>Classificação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $ev): ?>
                    <tr>
                        <td><small><?= date('d/m/Y H:i:s', strtotime($ev['entered_at'])) ?></small></td>
                        <td>
                            <small>
                                <?= $ev['exited_at']
                                    ? date('d/m/Y H:i:s', strtotime($ev['exited_at']))
                                    : '<span class="text-muted">Em andamento</span>' ?>
                            </small>
                        </td>
                        <td>
                            <span class="badge <?= $ev['status'] === 'on' ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $ev['status'] === 'on' ? 'Ativo' : 'Encerrado' ?>
                            </span>
                        </td>
                        <td class="fw-semibold"><?= htmlspecialchars($ev['alarm_description']) ?></td>
                        <td><?= htmlspecialchars($ev['equipment_name']) ?></td>
                        <td>
                            <span class="badge bg-<?= $classBadge[$ev['classification']] ?>">
                                <?= $classLabels[$ev['classification']] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="px-3 py-2 text-muted small border-top">
            <?= count($events) ?> registro(s) encontrado(s)
            <?= $search ? ' para "' . htmlspecialchars($search) . '"' : '' ?>
        </div>
        <?php endif; ?>
    </div>
</div>
