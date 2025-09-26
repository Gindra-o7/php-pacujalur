<?php

namespace App\Handlers;

use App\Services\GlobalService;
use App\Helpers\ResponseHelper;
use Exception;

class GlobalHandler
{
  public static function introduce(): void
  {
    try {
      $introduceMessage = GlobalService::introduce();
      echo json_encode(ResponseHelper::success($introduceMessage), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
      echo json_encode(ResponseHelper::error("Terjadi kesalahan internal."));
    }
  }

  public static function health(): void
  {
    try {
      $healthMessage = GlobalService::health();
      echo json_encode(ResponseHelper::success($healthMessage), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
      echo json_encode(ResponseHelper::error("Terjadi kesalahan internal."));
    }
  }
}
