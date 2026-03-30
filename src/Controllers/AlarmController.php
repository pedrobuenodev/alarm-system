<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Controller, Request, Response};
use App\Models\{Alarm, AlarmEvent, Equipment};
use App\Services\{AuditService, EmailService};

final class AlarmController extends Controller
{
    private Alarm        $alarm;
    private AlarmEvent   $alarmEvent;
    private Equipment    $equipment;
    private AuditService $audit;
    private EmailService $email;

    private const VALID_CLASSIFICATIONS = ['urgent', 'emergent', 'ordinary'];

    public function __construct()
    {
        $this->alarm      = new Alarm();
        $this->alarmEvent = new AlarmEvent();
        $this->equipment  = new Equipment();
        $this->audit      = new AuditService();
        $this->email      = new EmailService();
    }

    public function index(Request $request, Response $response): void
    {
        $alarms = $this->alarm->all();
        $response->view('alarms/index', compact('alarms'));
    }

    public function create(Request $request, Response $response): void
    {
        $equipments = $this->equipment->all();
        $response->view('alarms/form', ['alarm' => null, 'equipments' => $equipments, 'errors' => []]);
    }

    public function store(Request $request, Response $response): void
    {
        $data   = $this->sanitize($request->all());
        $errors = $this->validateAlarm($data);

        if (!empty($errors)) {
            $equipments = $this->equipment->all();
            $response->view('alarms/form', ['alarm' => $data, 'equipments' => $equipments, 'errors' => $errors]);
            return;
        }

        $id = $this->alarm->create($data);
        $this->audit->log($request, 'alarm', $id, 'created', $data);

        $response->redirect('/alarms?success=created');
    }

    public function edit(Request $request, Response $response, array $params): void
    {
        $alarm = $this->alarm->findById((int) $params['id']);

        if (!$alarm) {
            $response->redirect('/alarms?error=not_found');
            return;
        }

        $equipments = $this->equipment->all();
        $response->view('alarms/form', ['alarm' => $alarm, 'equipments' => $equipments, 'errors' => []]);
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $id    = (int) $params['id'];
        $alarm = $this->alarm->findById($id);

        if (!$alarm) {
            $response->redirect('/alarms?error=not_found');
            return;
        }

        $data   = $this->sanitize($request->all());
        $errors = $this->validateAlarm($data);

        if (!empty($errors)) {
            $equipments = $this->equipment->all();
            $data['id'] = $id;
            $response->view('alarms/form', ['alarm' => $data, 'equipments' => $equipments, 'errors' => $errors]);
            return;
        }

        $this->alarm->update($id, $data);
        $this->audit->log($request, 'alarm', $id, 'updated', $data);

        $response->redirect('/alarms?success=updated');
    }

    public function destroy(Request $request, Response $response, array $params): void
    {
        $id    = (int) $params['id'];
        $alarm = $this->alarm->findById($id);

        if (!$alarm) {
            $response->json(['error' => 'Alarme não encontrado.'], 404);
            return;
        }

        if ($alarm['status'] === 'on') {
            $response->json(['error' => 'Não é possível excluir um alarme ativo.'], 422);
            return;
        }

        $this->alarm->softDelete($id);
        $this->audit->log($request, 'alarm', $id, 'deleted');

        $response->json(['success' => true]);
    }

    /** POST /alarms/{id}/activate */
    public function activate(Request $request, Response $response, array $params): void
    {
        $id    = (int) $params['id'];
        $alarm = $this->alarm->findById($id);

        if (!$alarm) {
            $response->json(['error' => 'Alarme não encontrado.'], 404);
            return;
        }

        if ($alarm['status'] === 'on') {
            $response->json(['error' => 'Alarme já está ativo.'], 422);
            return;
        }

        $this->alarm->updateStatus($id, 'on');
        $this->alarmEvent->activate($id);
        $this->audit->log($request, 'alarm', $id, 'activated');

        // Regra: alarme urgente dispara e-mail
        if ($alarm['classification'] === 'urgent') {
            $this->email->sendUrgentAlarmNotification(
                $alarm['description'],
                $alarm['equipment_name']
            );
        }

        $response->json(['success' => true, 'status' => 'on']);
    }

    /** POST /alarms/{id}/deactivate */
    public function deactivate(Request $request, Response $response, array $params): void
    {
        $id    = (int) $params['id'];
        $alarm = $this->alarm->findById($id);

        if (!$alarm) {
            $response->json(['error' => 'Alarme não encontrado.'], 404);
            return;
        }

        if ($alarm['status'] === 'off') {
            $response->json(['error' => 'Alarme já está inativo.'], 422);
            return;
        }

        $this->alarm->updateStatus($id, 'off');
        $this->alarmEvent->deactivate($id);
        $this->audit->log($request, 'alarm', $id, 'deactivated');

        $response->json(['success' => true, 'status' => 'off']);
    }

    public function events(Request $request, Response $response): void
    {
        $search  = trim($request->query('search', ''));
        $sortBy  = $request->query('sort_by', 'entered_at');
        $sortDir = strtoupper($request->query('sort_dir', 'DESC'));
        $top3    = $this->alarm->topActuated(3);
        $events  = $this->alarmEvent->listActuated($search, $sortBy, $sortDir);

        $response->view('alarms/events', compact('events', 'top3', 'search', 'sortBy', 'sortDir'));
    }

    private function validateAlarm(array $data): array
    {
        $errors = [];

        if (empty($data['description'])) {
            $errors['description'][] = 'Descrição é obrigatória.';
        } elseif (strlen($data['description']) > 255) {
            $errors['description'][] = 'Descrição deve ter no máximo 255 caracteres.';
        }

        if (empty($data['classification']) || !in_array($data['classification'], self::VALID_CLASSIFICATIONS, true)) {
            $errors['classification'][] = 'Classificação inválida.';
        }

        if (empty($data['equipment_id'])) {
            $errors['equipment_id'][] = 'Equipamento é obrigatório.';
        } elseif (!$this->equipment->findById((int) $data['equipment_id'])) {
            $errors['equipment_id'][] = 'Equipamento não encontrado.';
        }

        return $errors;
    }

    private function sanitize(array $data): array
    {
        return [
            'description'    => trim(htmlspecialchars($data['description'] ?? '', ENT_QUOTES, 'UTF-8')),
            'classification' => trim($data['classification'] ?? ''),
            'equipment_id'   => (int) ($data['equipment_id'] ?? 0),
        ];
    }
}
