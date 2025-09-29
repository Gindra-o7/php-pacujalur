<?php

// Manual Autoloader
// Helpers
require_once __DIR__ . '/../app/helpers/PaginationHelper.php';
require_once __DIR__ . '/../app/helpers/ResponseHelper.php';
require_once __DIR__ . '/../app/helpers/UploadHelper.php';
require_once __DIR__ . '/../app/helpers/ValidationHelper.php';

// Configs
require_once __DIR__ . '/../configs/DatabaseConfig.php';

// Middlewares
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';

// Repositories
require_once __DIR__ . '/../app/repositories/AcaraRepository.php';
require_once __DIR__ . '/../app/repositories/AuthRepository.php';
require_once __DIR__ . '/../app/repositories/GaleriRepository.php';
require_once __DIR__ . '/../app/repositories/JalurRepository.php';
require_once __DIR__ . '/../app/repositories/PenginapanRepository.php';
require_once __DIR__ . '/../app/repositories/TribunRepository.php';

// Services
require_once __DIR__ . '/../app/services/AcaraService.php';
require_once __DIR__ . '/../app/services/AuthService.php';
require_once __DIR__ . '/../app/services/GaleriService.php';
require_once __DIR__ . '/../app/services/GlobalService.php';
require_once __DIR__ . '/../app/services/JalurService.php';
require_once __DIR__ . '/../app/services/PenginapanService.php';
require_once __DIR__ . '/../app/services/TribunService.php';

// Handlers
require_once __DIR__ . '/../app/handlers/AcaraHandler.php';
require_once __DIR__ . '/../app/handlers/AuthHandler.php';
require_once __DIR__ . '/../app/handlers/GaleriHandler.php';
require_once __DIR__ . '/../app/handlers/GlobalHandler.php';
require_once __DIR__ . '/../app/handlers/JalurHandler.php';
require_once __DIR__ . '/../app/handlers/PenginapanHandler.php';
require_once __DIR__ . '/../app/handlers/TribunHandler.php';

// Routes
require_once __DIR__ . '/../routes/AcaraRoute.php';
require_once __DIR__ . '/../routes/AuthRoute.php';
require_once __DIR__ . '/../routes/GaleriRoute.php';
require_once __DIR__ . '/../routes/GlobalRoute.php';
require_once __DIR__ . '/../routes/JalurRoute.php';
require_once __DIR__ . '/../routes/PenginapanRoute.php';
require_once __DIR__ . '/../routes/TribunRoute.php';
require_once __DIR__ . '/../routes/Router.php';


if (file_exists('../.env')) {
  $lines = file('../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
      [$key, $value] = explode('=', $line, 2);
      $_ENV[trim($key)] = trim($value);
    }
  }
}

use Routes\Router;

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

$router = new Router();
$router->handleRequest();