<?php

namespace Routes;

use App\Handlers\AcaraHandler;
use Middlewares\AuthMiddleware;

class AcaraRoute
{
  public static function routes(): array
  {
    return [
      'GET' => [
        '/acara' => [AcaraHandler::class, 'getAll'],
        '/acara/{id}' => [AcaraHandler::class, 'getById'],
      ],
      'POST' => [
        '/acara' => [AcaraHandler::class, 'create'],
        '/acara/upload-image' => [AcaraHandler::class, 'uploadImage'],
      ],
      'PUT' => [
        '/acara/{id}' => [AcaraHandler::class, 'update'],
      ],
      'DELETE' => [
        '/acara/{id}' => [AcaraHandler::class, 'delete'],
      ]
    ];
  }
}
