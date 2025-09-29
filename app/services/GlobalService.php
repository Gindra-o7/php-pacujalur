<?php

namespace App\Services;

define("START_TIME", microtime(true));

use DateTime;
use DateTimeZone;

class GlobalService
{
  public static function introduce(): array
  {
    $now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
    $formattedDate = $now->format('d/m/Y, H:i:s');

    return [
      'response' => true,
      'message' => 'Cihuy, Halow Semua 👋 ~ Selamat datang di API Pacu Jalur! 🎉',
      'version' => getenv('APP_VERSION') ?: '1.0.0',
      'contributor' => 'https://github.com/gindra-o7/php-pacujalur',
      'timezone' => "Asia/Jakarta ~ {$formattedDate} WIB",
    ];
  }
  public static function health(): array
  {
    $uptime = defined('START_TIME') ? (microtime(true) - START_TIME) : 0;

    return [
      'response' => true,
      'message' => 'Cihuy, API Pacu Jalur sehat-sehat saja! 😁',
      'status' => 'OK',
      'uptime' => $uptime,
      'memoryUsage' => [
        'current_bytes' => memory_get_usage(),
        'peak_bytes' => memory_get_peak_usage(),
      ],
    ];
  }
}
