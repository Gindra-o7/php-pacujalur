<?php

namespace Routes;

use App\Handlers\JalurHandler;

class JalurRoute
{
  public static function routes(): array
  {
    return [
      'GET' => [
        '/jalur' => [JalurHandler::class, 'getAll'],
        '/jalur/{id}' => [JalurHandler::class, 'getById'],
      ],
      'POST' => [
        '/jalur' => [JalurHandler::class, 'create'],
        '/jalur/upload-galeri' => [JalurHandler::class, 'uploadGaleriImages'],
      ],
    ];
  }
}
