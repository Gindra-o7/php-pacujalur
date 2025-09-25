<?php

namespace Routes;

use Illuminate\Support\Facades\Route;
use App\Handlers\GlobalHandler;

class GlobalRoute
{
  public static function handle($path)
  {
    switch ($path) {
      case '/':
        GlobalHandler::introduce();
        break;
      case '/health':
        GlobalHandler::health();
        break;
    }
  }
}
