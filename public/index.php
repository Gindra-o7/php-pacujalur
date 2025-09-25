<?php

require_once '../configs/db.config.php';
require_once '../app/helpers/response.helper.php';
require_once '../app/helpers/pagination.helper.php';
require_once '../app/services/global.service.php';
require_once '../app/services/jalur.service.php';
require_once '../app/repositories/jalur.repository.php';
require_once '../app/handlers/global.handler.php';
require_once '../app/handlers/jalur.handler.php';

if (file_exists('../.env')) {
  $lines = file('../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
      [$key, $value] = explode('=', $line, 2);
      $_ENV[trim($key)] = trim($value);
    }
  }
}

use App\Helpers\ResponseHelper; 
use App\Handlers\GlobalHandler;
use App\Handlers\JalurHandler;

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

$basePath = '/be-pacujalur/public';
$path = parse_url($requestUri, PHP_URL_PATH);

if (strpos($path, $basePath) === 0) {
  $path = substr($path, strlen($basePath));
}

$path = trim($path, '/');

try {
  switch ($requestMethod) {
    case 'GET':
      switch ($path) {
        case '':
          GlobalHandler::introduce();
          break;

        case 'health':
          GlobalHandler::health();
          break;

        case 'jalur':
          $jalurHandler = new JalurHandler();
          $jalurHandler->getAll();
          break;

        default:
          echo json_encode(ResponseHelper::notFound('Route not found'));
          break;
      }
      break;

    case 'POST':
      switch ($path) {
        case 'jalur':
          $jalurHandler = new JalurHandler();
          $jalurHandler->create();
          break;

        default:
          echo json_encode(ResponseHelper::notFound('Route not found'));
          break;
      }
      break;

    case 'PUT':
      echo json_encode(ResponseHelper::notFound('PUT method not implemented yet'));
      break;

    case 'DELETE':
      echo json_encode(ResponseHelper::notFound('DELETE method not implemented yet'));
      break;

    default:
      echo json_encode(ResponseHelper::badRequest('Method not allowed'));
      break;
  }
} catch (Exception $e) {
  echo json_encode(ResponseHelper::error("Internal server error: " . $e->getMessage()));
}
