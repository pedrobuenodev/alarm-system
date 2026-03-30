<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Alarmes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="/">
            <i class="bi bi-bell-fill text-warning me-2"></i>AlarmSystem Grupo Assist 
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= isActive('/') ?>" href="/">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isActive('/equipments') ?>" href="/equipments">
                        <i class="bi bi-cpu me-1"></i>Equipamentos
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= isActive('/alarms') ?>" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-bell me-1"></i>Alarmes
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/alarms"><i class="bi bi-list-ul me-2"></i>Gerenciar Alarmes</a></li>
                        <li><a class="dropdown-item" href="/alarms/events"><i class="bi bi-activity me-2"></i>Alarmes Atuados</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container-fluid py-4 px-4">

<?php
function isActive(string $path): string {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if ($path === '/') {
        return $uri === '/' ? 'active' : '';
    }
    return str_starts_with($uri, $path) ? 'active' : '';
}

function flashAlert(): void {
    $type = match($_GET['success'] ?? '') {
        'created' => ['success', 'Registro criado com sucesso!'],
        'updated' => ['success', 'Registro atualizado com sucesso!'],
        default   => null,
    };
    if (($_GET['error'] ?? '') === 'not_found') {
        $type = ['danger', 'Registro não encontrado.'];
    }
    if ($type) {
        echo "<div class=\"alert alert-{$type[0]} alert-dismissible fade show\" role=\"alert\">
                <i class=\"bi bi-check-circle me-2\"></i>{$type[1]}
                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>
              </div>";
    }
}

flashAlert();
?>
