<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Controller, Request, Response};
use App\Models\Equipment;
use App\Services\AuditService;

final class EquipmentController extends Controller
{
    private Equipment    $equipment;
    private AuditService $audit;

    private const VALID_TYPES = ['voltage', 'current', 'oil'];

    public function __construct()
    {
        $this->equipment = new Equipment();
        $this->audit     = new AuditService();
    }

    public function index(Request $request, Response $response): void
    {
        $equipments = $this->equipment->all();
        $response->view('equipment/index', compact('equipments'));
    }

    public function create(Request $request, Response $response): void
    {
        $response->view('equipment/form', ['equipment' => null, 'errors' => []]);
    }

    public function store(Request $request, Response $response): void
    {
        $data   = $this->sanitize($request->all());
        $errors = $this->validateEquipment($data);

        if ($this->equipment->existsBySerial($data['serial'])) {
            $errors['serial'][] = 'Número de série já cadastrado.';
        }

        if (!empty($errors)) {
            $response->view('equipment/form', ['equipment' => $data, 'errors' => $errors]);
            return;
        }

        $id = $this->equipment->create($data);
        $this->audit->log($request, 'equipment', $id, 'created', $data);

        $response->redirect('/equipments?success=created');
    }

    public function edit(Request $request, Response $response, array $params): void
    {
        $equipment = $this->equipment->findById((int) $params['id']);

        if (!$equipment) {
            $response->redirect('/equipments?error=not_found');
            return;
        }

        $response->view('equipment/form', ['equipment' => $equipment, 'errors' => []]);
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $id        = (int) $params['id'];
        $equipment = $this->equipment->findById($id);

        if (!$equipment) {
            $response->redirect('/equipments?error=not_found');
            return;
        }

        $data   = $this->sanitize($request->all());
        $errors = $this->validateEquipment($data);

        if ($this->equipment->existsBySerial($data['serial'], $id)) {
            $errors['serial'][] = 'Número de série já cadastrado.';
        }

        if (!empty($errors)) {
            $data['id'] = $id;
            $response->view('equipment/form', ['equipment' => $data, 'errors' => $errors]);
            return;
        }

        $this->equipment->update($id, $data);
        $this->audit->log($request, 'equipment', $id, 'updated', $data);

        $response->redirect('/equipments?success=updated');
    }

    public function destroy(Request $request, Response $response, array $params): void
    {
        $id = (int) $params['id'];

        if ($this->equipment->hasActiveAlarms($id)) {
            $response->json(['error' => 'Não é possível excluir equipamento com alarmes vinculados.'], 422);
            return;
        }

        $deleted = $this->equipment->softDelete($id);

        if (!$deleted) {
            $response->json(['error' => 'Equipamento não encontrado.'], 404);
            return;
        }

        $this->audit->log($request, 'equipment', $id, 'deleted');
        $response->json(['success' => true]);
    }

    private function validateEquipment(array $data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'][] = 'Nome é obrigatório.';
        } elseif (strlen($data['name']) > 150) {
            $errors['name'][] = 'Nome deve ter no máximo 150 caracteres.';
        }

        if (empty($data['serial'])) {
            $errors['serial'][] = 'Número de série é obrigatório.';
        } elseif (strlen($data['serial']) > 100) {
            $errors['serial'][] = 'Número de série deve ter no máximo 100 caracteres.';
        }

        if (empty($data['type']) || !in_array($data['type'], self::VALID_TYPES, true)) {
            $errors['type'][] = 'Tipo inválido. Escolha: Tensão, Corrente ou Óleo.';
        }

        return $errors;
    }

    private function sanitize(array $data): array
    {
        return [
            'name'   => trim(htmlspecialchars($data['name']   ?? '', ENT_QUOTES, 'UTF-8')),
            'serial' => trim(htmlspecialchars($data['serial'] ?? '', ENT_QUOTES, 'UTF-8')),
            'type'   => trim($data['type'] ?? ''),
        ];
    }
}
