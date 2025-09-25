<?php

namespace Routes;

use App\Handlers\GlobalHandler;

class GlobalRoute
{
  public static function handle(string $path): void
  {
    switch ($path) {
      case '/':
        GlobalHandler::introduce();
        break;
      case '/health':
        GlobalHandler::health();
        break;
      default:
        http_response_code(404);
        echo json_encode([
          'success' => false,
          'message' => 'Route not found'
        ]);
        break;
    }
  }

  public static function routes(): array
  {
    return [
      'GET' => [
        '/' => [GlobalHandler::class, 'introduce'],
        '/health' => [GlobalHandler::class, 'health']
      ]
    ];
  }
}
