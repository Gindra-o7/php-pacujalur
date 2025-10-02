<?php

namespace Routes;

use App\Helpers\ResponseHelper;
use Exception;

class Router
{
  private string $requestUri;
  private string $requestMethod;
  private array $routes;
  private string $basePath;

  public function __construct()
  {
    $this->requestUri = $_SERVER['REQUEST_URI'];
    $this->requestMethod = $_SERVER['REQUEST_METHOD'];
    $this->basePath = $_ENV['BASE_PATH'];
    $this->routes = $this->defineRoutes();
  }

  private function defineRoutes(): array
  {
    return array_merge_recursive(
      GlobalRoute::routes(),
      JalurRoute::routes(),
      GaleriRoute::routes(),
      AcaraRoute::routes(),
      AuthRoute::routes(),
      PenginapanRoute::routes(),
      TribunRoute::routes(),
      PublicRoute::routes()
    );
  }

  public function handleRequest(): void
  {
    $path = $this->getCleanPath();
    $methodRoutes = $this->routes[$this->requestMethod] ?? [];

    foreach ($methodRoutes as $route => $handler) {
      $routePattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9-]+)', $route);
      if (preg_match("#^$routePattern$#", $path, $matches)) {
        array_shift($matches);
        $this->executeHandler($handler, $matches);
        return;
      }
    }

    $this->sendResponse(ResponseHelper::notFound('Anda salah haluan mas, silahkan pindah ke jalur yang benar!'));
  }

  private function getCleanPath(): string
  {
    $path = parse_url($this->requestUri, PHP_URL_PATH);
    if (strpos($path, $this->basePath) === 0) {
      $path = substr($path, strlen($this->basePath));
    }
    return rtrim($path, '/') ?: '/';
  }

  private function executeHandler(array $handler, array $params = []): void
  {
    [$class, $method] = $handler;

    if (!class_exists($class) || !method_exists($class, $method)) {
      $this->sendResponse(ResponseHelper::error('Handler not found'));
      return;
    }

    try {
      $instance = new $class();
      $instance->$method(...$params);
    } catch (Exception $e) {
      $this->sendResponse(ResponseHelper::error('Handler execution failed: ' . $e->getMessage()));
    }
  }

  private function sendResponse(array $response): void
  {
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
  }
}
