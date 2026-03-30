<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);
        header("Location: {$url}");
        exit;
    }

    public function view(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewPath = __DIR__ . '/../Views/' . $template . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$template}");
        }

        require __DIR__ . '/../Views/layout/header.php';
        require $viewPath;
        require __DIR__ . '/../Views/layout/footer.php';
    }

    public function partialView(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewPath = __DIR__ . '/../Views/' . $template . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$template}");
        }

        require $viewPath;
    }
}
