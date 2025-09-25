<?php

namespace App\Handlers;

use App\Services\GlobalService;
use App\Helpers\ResponseHelper;
use Exception;

class GlobalHandler
{
  public static function introduce()
  {
    try {
      $introduceMessage = GlobalService::introduce();
      ResponseHelper::success($introduceMessage);
    } catch (Exception $e) {
      ResponseHelper::error("Terjadi kesalahan internal.");
    }
  }

  public static function health()
  {
    try {
      $healthMessage = GlobalService::health();
      ResponseHelper::success($healthMessage);
    } catch (Exception $e) {
      ResponseHelper::error("Terjadi kesalahan internal.");
    }
  }
}
