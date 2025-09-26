<?php

namespace Routes;

use App\Handlers\GaleriHandler;

class GaleriRoute
{
  public static function routes(): array
  {
    return [
      'GET' => [
        '/galeri' => [GaleriHandler::class, 'getAll'],
      ]
    ];
  }
}
