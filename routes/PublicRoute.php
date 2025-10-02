<?php

namespace Routes;

use App\Handlers\PublicHandler;

class PublicRoute
{
  public static function routes(): array
  {
    return [
      'GET' => [
        '/landing' => [PublicHandler::class, 'get'],
      ]
    ];
  }
}
