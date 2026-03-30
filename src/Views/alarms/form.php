<?php
$isEdit   = !empty($alarm['id']);
$title    = $isEdit ? 'Editar Alarme' : 'Novo Alarme';
$action   = $isEdit ? "/alarms/{$alarm['id']}" : '/alarms';
$classLabels = ['urgent' => 'Urgente', 'emergent' => 'Emergente', 'ordinary' => 'Ordinário'];
$classDesc   = [
    'urgent'   => 'Dispara notificação por e-mail ao ser ativado.',
    'emergent' => 'Alta prioridade, requer atenção imediata.',
    'ordinary' => 'Monitoramento de rotina.',
];
$classBadge = ['urgent' => 'danger', 'emergent' => 'warning', 'ordinary' => 'secondary'];
?>

<div class="row justify-content-center">
<div class="col-12 col-md-8 col-lg-6">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="bi bi-bell me-2 text-primary"></i><?= $title ?></h2>
    <a href="/alarms" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="<?= $action ?>" novalidate>
            <?php if ($isEdit): ?>
                <input type="hidden" name="_method" value="PUT">
            <?php endif; ?>

            <!-- Descrição -->
            <div class="mb-3">
                <label for="description" class="form-label fw-semibold">Descrição do Alarme <span class="text-danger">*</span></label>
                <input type="text"
                       id="description"
                       name="description"
                       class="form-control <?= !empty($errors['description']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($alarm['description'] ?? '') ?>"
                       maxlength="255"
                       placeholder="Ex: Sobretensão na fase A"
                       required>
                <?php foreach ($errors['description'] ?? [] as $err): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($err) ?></div>
                <?php endforeach; ?>
            </div>

            <!-- Classificação -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Classificação <span class="text-danger">*</span></label>
                <div class="row g-2">
                    <?php foreach ($classLabels as $value => $label): ?>
                    <div class="col-4">
                        <input type="radio"
                               class="btn-check"
                               name="classification"
                               id="class_<?= $value ?>"
                               value="<?= $value ?>"
                               <?= ($alarm['classification'] ?? '') === $value ? 'checked' : '' ?>>
                        <label class="btn btn-outline-<?= $classBadge[$value] ?> w-100 text-start" for="class_<?= $value ?>">
                            <span class="fw-semibold d-block"><?= $label ?></span>
                            <small class="text-muted d-none d-md-block" style="font-size:.75rem"><?= $classDesc[$value] ?></small>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php foreach ($errors['classification'] ?? [] as $err): ?>
                    <div class="text-danger small mt-1"><?= htmlspecialchars($err) ?></div>
                <?php endforeach; ?>
            </div>

            <!-- Equipamento -->
            <div class="mb-4">
                <label for="equipment_id" class="form-label fw-semibold">Equipamento <span class="text-danger">*</span></label>
                <select id="equipment_id"
                        name="equipment_id"
                        class="form-select <?= !empty($errors['equipment_id']) ? 'is-invalid' : '' ?>"
                        required>
                    <option value="">Selecione um equipamento...</option>
                    <?php foreach ($equipments as $eq): ?>
                        <option value="<?= $eq['id'] ?>"
                            <?= (int)($alarm['equipment_id'] ?? 0) === (int)$eq['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($eq['name']) ?> &mdash; <small><?= htmlspecialchars($eq['serial']) ?></small>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php foreach ($errors['equipment_id'] ?? [] as $err): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($err) ?></div>
                <?php endforeach; ?>
                <?php if (empty($equipments)): ?>
                    <div class="form-text text-danger">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        Nenhum equipamento cadastrado. <a href="/equipments/create">Cadastre um equipamento</a> antes.
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" <?= empty($equipments) ? 'disabled' : '' ?>>
                    <i class="bi bi-floppy me-1"></i><?= $isEdit ? 'Salvar Alterações' : 'Cadastrar' ?>
                </button>
                <a href="/alarms" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

</div>
</div>
