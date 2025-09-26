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
        '/penginapan/upload-main-image' => [PenginapanHandler::class, 'uploadMainImage'],
        '/penginapan/upload-galeri' => [PenginapanHandler::class, 'uploadGaleriImages'],
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