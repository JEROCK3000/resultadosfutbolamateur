<?php
declare(strict_types=1);

/**
 * Router.php — Enrutador principal del sistema
 * Mapea las URLs a sus controladores y métodos correspondientes.
 */
class Router
{
    /** @var array Rutas registradas */
    private array $routes = [];

    /**
     * Registra una ruta GET.
     */
    public function get(string $path, string $handler): void
    {
        $this->routes[] = ['method' => 'GET', 'path' => $path, 'handler' => $handler];
    }

    /**
     * Registra una ruta POST.
     */
    public function post(string $path, string $handler): void
    {
        $this->routes[] = ['method' => 'POST', 'path' => $path, 'handler' => $handler];
    }

    /**
     * Despacha la petición actual al controlador correspondiente.
     */
    public function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Eliminar el prefijo BASE_PATH de la URI
        $basePath = parse_url(BASE_URL, PHP_URL_PATH);
        if (str_starts_with($requestUri, $basePath)) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
        $requestUri = '/' . trim($requestUri, '/');
        if ($requestUri === '') $requestUri = '/';

        foreach ($this->routes as $route) {
            $pattern = $this->buildPattern($route['path']);

            if ($route['method'] === $requestMethod && preg_match($pattern, $requestUri, $matches)) {
                // Filtrar solo los parámetros nombrados
                $params = array_filter($matches, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);
                $this->callHandler($route['handler'], array_values($params));
                return;
            }
        }

        // Ruta no encontrada
        $this->render404();
    }

    /**
     * Convierte una ruta con parámetros ({id}) en una expresión regular.
     */
    private function buildPattern(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Instancia el controlador y llama al método correspondiente.
     *
     * @param string $handler  Formato: "NombreController@metodo"
     * @param array  $params   Parámetros extraídos de la URL
     */
    private function callHandler(string $handler, array $params = []): void
    {
        [$controllerName, $method] = explode('@', $handler);
        $controllerFile = BASE_PATH . '/app/Controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            writeLog('ERROR', "Controlador no encontrado: {$controllerName}");
            $this->render404();
            return;
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            writeLog('ERROR', "Clase no encontrada: {$controllerName}");
            $this->render404();
            return;
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $method)) {
            writeLog('ERROR', "Método no encontrado: {$controllerName}@{$method}");
            $this->render404();
            return;
        }

        call_user_func_array([$controller, $method], $params);
    }

    /**
     * Muestra la página de error 404.
     */
    private function render404(): void
    {
        http_response_code(404);
        $view404 = BASE_PATH . '/app/Views/errors/404.php';
        if (file_exists($view404)) {
            require_once $view404;
        } else {
            echo '<h1>404 — Página no encontrada</h1>';
        }
    }
}
