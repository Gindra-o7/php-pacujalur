<?php

namespace Routes;

use App\Handlers\PenginapanHandler;
use Middlewares\AuthMiddleware;

class PenginapanRoute
{
  public static function routes(): array
  {
    return [
      'GET' => [
        '/penginapan' => [PenginapanHandler::class, 'getAll'],
        '/penginapan/{id}' => [PenginapanHandler::class, 'getById'],
      ],
      'POST' => [
        '/penginapan' => [PenginapanHandler::class, 'create'],
      ],
      'PUT' => [
        '/penginapan/{id}' => [PenginapanHandler::class, 'update'],
      ],
      'DELETE' => [
        '/penginapan/{id}' => [PenginapanHandler::class, 'delete'],
      ]
    ];
  }
}