<?php

namespace Routes;

use App\Helpers\ResponseHelper;
use App\Handlers\GlobalHandler;
use App\Handlers\JalurHandler;
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
    $this->basePath = '/be-pacujalur/public'; // Sesuaikan dengan nama folder project
    $this->routes = $this->defineRoutes();
  }

  /**
   * Definisi semua route aplikasi
   */
  private function defineRoutes(): array
  {
    return [
      'GET' => [
        '/' => [GlobalHandler::class, 'introduce'],
        '/health' => [GlobalHandler::class, 'health'],
        '/jalur' => [JalurHandler::class, 'getAll'],
      ],
      'POST' => [
        '/jalur' => [JalurHandler::class, 'create'],
      ],
      'PUT' => [
        // '/jalur/{id}' => [JalurHandler::class, 'update'],
      ],
      'DELETE' => [
        // '/jalur/{id}' => [JalurHandler::class, 'delete'],
      ]
    ];
  }

  /**
   * Handle incoming request
   */
  public function handleRequest(): void
  {
    $path = $this->getCleanPath();

    if (!isset($this->routes[$this->requestMethod])) {
      $this->sendResponse(ResponseHelper::badRequest('Method not allowed'));
      return;
    }

    $methodRoutes = $this->routes[$this->requestMethod];

    // Cek exact match
    if (isset($methodRoutes[$path])) {
      $this->executeHandler($methodRoutes[$path]);
      return;
    }

    // Route tidak ditemukan
    $this->sendResponse(ResponseHelper::notFound('Route not found'));
  }

  /**
   * Membersihkan path dari query string dan base path
   */
  private function getCleanPath(): string
  {
    $path = parse_url($this->requestUri, PHP_URL_PATH);

    // Remove base path jika ada
    if (strpos($path, $this->basePath) === 0) {
      $path = substr($path, strlen($this->basePath));
    }

    $path = rtrim($path, '/');
    return $path === '' ? '/' : $path;
  }

  /**
   * Execute handler yang sesuai
   */
  private function executeHandler(array $handler): void
  {
    [$class, $method] = $handler;

    if (!class_exists($class)) {
      $this->sendResponse(ResponseHelper::error('Handler class not found'));
      return;
    }

    if (!method_exists($class, $method)) {
      $this->sendResponse(ResponseHelper::error('Handler method not found'));
      return;
    }

    try {
      if ($method === 'introduce' || $method === 'health') {
        // Static methods untuk GlobalHandler
        $class::$method();
      } else {
        // Instance methods untuk handler lainnya
        $instance = new $class();
        $instance->$method();
      }
    } catch (Exception $e) {
      $this->sendResponse(ResponseHelper::error('Handler execution failed: ' . $e->getMessage()));
    }
  }

  /**
   * Send JSON response
   */
  private function sendResponse(array $response): void
  {
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
  }

  /**
   * Set base path untuk routing
   */
  public function setBasePath(string $basePath): void
  {
    $this->basePath = $basePath;
  }
}
