<?php
$isEdit    = !empty($equipment['id']);
$title     = $isEdit ? 'Editar Equipamento' : 'Novo Equipamento';
$action    = $isEdit ? "/equipments/{$equipment['id']}" : '/equipments';
$typeLabels = ['voltage' => 'Tensão', 'current' => 'Corrente', 'oil' => 'Óleo'];
?>

<div class="row justify-content-center">
<div class="col-12 col-md-8 col-lg-6">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="bi bi-cpu me-2 text-primary"></i><?= $title ?></h2>
    <a href="/equipments" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="<?= $action ?>" novalidate>
            <?php if ($isEdit): ?>
                <input type="hidden" name="_method" value="PUT">
            <?php endif; ?>

            <!-- Nome -->
            <div class="mb-3">
                <label for="name" class="form-label fw-semibold">Nome do Equipamento <span class="text-danger">*</span></label>
                <input type="text"
                       id="name"
                       name="name"
                       class="form-control <?= !empty($errors['name']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($equipment['name'] ?? '') ?>"
                       maxlength="150"
                       placeholder="Ex: Transformador Principal"
                       required>
                <?php foreach ($errors['name'] ?? [] as $err): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($err) ?></div>
                <?php endforeach; ?>
            </div>

            <!-- Número de Série -->
            <div class="mb-3">
                <label for="serial" class="form-label fw-semibold">Número de Série <span class="text-danger">*</span></label>
                <input type="text"
                       id="serial"
                       name="serial"
                       class="form-control <?= !empty($errors['serial']) ? 'is-invalid' : '' ?>"
                       value="<?= htmlspecialchars($equipment['serial'] ?? '') ?>"
                       maxlength="100"
                       placeholder="Ex: SN-2024-001"
                       required>
                <?php foreach ($errors['serial'] ?? [] as $err): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($err) ?></div>
                <?php endforeach; ?>
            </div>

            <!-- Tipo -->
            <div class="mb-4">
                <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                <div class="<?= !empty($errors['type']) ? 'is-invalid' : '' ?>">
                    <?php foreach ($typeLabels as $value => $label): ?>
                    <div class="form-check">
                        <input class="form-check-input"
                               type="radio"
                               name="type"
                               id="type_<?= $value ?>"
                               value="<?= $value ?>"
                               <?= ($equipment['type'] ?? '') === $value ? 'checked' : '' ?>>
                        <label class="form-check-label" for="type_<?= $value ?>"><?= $label ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php foreach ($errors['type'] ?? [] as $err): ?>
                    <div class="text-danger small mt-1"><?= htmlspecialchars($err) ?></div>
                <?php endforeach; ?>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy me-1"></i><?= $isEdit ? 'Salvar Alterações' : 'Cadastrar' ?>
                </button>
                <a href="/equipments" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

</div>
</div>
