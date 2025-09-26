<?php

namespace Routes;

use App\Handlers\TribunHandler;

class TribunRoute
{
  public static function routes(): array
  {
    return [
      'GET' => [
        '/acara/{acara_id}/tribun' => [TribunHandler::class, 'getAllByAcaraId'],
        '/tribun/{id}' => [TribunHandler::class, 'getById'],
      ],
      'POST' => [
        '/tribun' => [TribunHandler::class, 'create'],
      ],
      'PUT' => [
        '/tribun/{id}' => [TribunHandler::class, 'update'],
      ],
      'DELETE' => [
        '/tribun/{id}' => [TribunHandler::class, 'delete'],
      ]
    ];
  }
}
