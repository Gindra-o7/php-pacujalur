<?php

namespace Routes;

use App\Handlers\AuthHandler;

class AuthRoute
{
  public static function routes(): array
  {
    return [
      'POST' => [
        '/auth/login' => [AuthHandler::class, 'login'],
        '/auth/register' => [AuthHandler::class, 'register'],
        '/auth/logout' => [AuthHandler::class, 'logout'],
        '/auth/refresh' => [AuthHandler::class, 'refreshToken'],
      ],
      'GET' => [
        '/auth/profile' => [AuthHandler::class, 'getProfile'],
      ]
    ];
  }
}
