<?php

namespace App\Helpers;

class ResponseHelper
{
  public static function success($data, string $message = "Success", int $statusCode = 200): array
  {
    http_response_code($statusCode);
    return [
      'success' => true,
      'message' => $message,
      'data' => $data
    ];
  }

  public static function error(string $message, int $statusCode = 500, ?string $error = null): array
  {
    http_response_code($statusCode);
    $response = [
      'success' => false,
      'message' => $message
    ];
    if ($error !== null) {
      $response['error'] = $error;
    }
    return $response;
  }

  public static function notFound(string $message = "Resource not found"): array
  {
    return self::error($message, 404);
  }

  public static function badRequest(string $message = "Bad request", ?string $error = null): array
  {
    return self::error($message, 400, $error);
  }

  public static function created($data, string $message = "Created successfully"): array
  {
    return self::success($data, $message, 201);
  }
}
