<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Router
{
    /** @var array<string, array<string, callable>> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, callable $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, callable $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri    = $request->uri();

        // Support method override via POST field _method
        if ($method === 'POST' && $request->input('_method')) {
            $method = strtoupper($request->input('_method'));
        }

        $methodRoutes = $this->routes[$method] ?? [];

        // Rotas estáticas (sem parâmetros) têm prioridade sobre dinâmicas
        uksort($methodRoutes, fn($a, $b) => str_contains($a, '{') <=> str_contains($b, '{'));

        foreach ($methodRoutes as $pattern => $handler) {
            $params = $this->match($pattern, $uri);

            if ($params !== null) {
                $handler($request, new Response(), $params);
                return;
            }
        }

        http_response_code(404);
        include __DIR__ . '/../Views/errors/404.php';
    }

    /**
     * Matches a route pattern against a URI and extracts named params.
     * Pattern example: /equipments/{id}
     *
     * @return array<string, string>|null
     */
    private function match(string $pattern, string $uri): ?array
    {
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $uri, $matches)) {
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }

        return null;
    }
}
