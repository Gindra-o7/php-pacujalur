<?php

require_once '../configs/db.config.php';
require_once '../app/helpers/response.helper.php';
require_once '../app/helpers/pagination.helper.php';
require_once '../app/services/global.service.php';
require_once '../app/services/jalur.service.php';
require_once '../app/repositories/jalur.repository.php';
require_once '../app/handlers/global.handler.php';
require_once '../app/handlers/jalur.handler.php';

// Load environment variables dari file .env
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

// Set headers untuk API
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

// Ambil request info
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Base path untuk routing (sesuaikan dengan folder project Anda)
$basePath = '/be-pacujalur/public';
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove base path dari URL
if (strpos($path, $basePath) === 0) {
  $path = substr($path, strlen($basePath));
}

// Clean up path
$path = trim($path, '/');

// Debug info (hapus ini di production)
error_log("Request Method: " . $requestMethod);
error_log("Original URI: " . $requestUri);
error_log("Cleaned Path: " . $path);

try {
  switch ($requestMethod) {
    case 'GET':
      switch ($path) {
        case '':
        case '/':
          GlobalHandler::introduce();
          break;

        case 'health':
          GlobalHandler::health();
          break;

        case 'jalur':
          $jalurHandler = new JalurHandler();
          $jalurHandler->getAll();
          break;

        // Handle jalur dengan ID
        default:
          if (preg_match('/^jalur\/([a-zA-Z0-9-]+)$/', $path, $matches)) {
            $jalurHandler = new JalurHandler();
            // Set ID dalam $_GET untuk mudah diakses
            $_GET['id'] = $matches[1];
            $jalurHandler->getById();
          } else {
            echo json_encode(ResponseHelper::notFound('Route not found: GET /' . $path));
          }
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
          echo json_encode(ResponseHelper::notFound('Route not found: POST /' . $path));
          break;
      }
      break;

    case 'PUT':
      // Implementasi untuk update jalur
      if (preg_match('/^jalur\/([a-zA-Z0-9-]+)$/', $path, $matches)) {
        echo json_encode(ResponseHelper::error('PUT method not implemented yet'));
      } else {
        echo json_encode(ResponseHelper::notFound('Route not found: PUT /' . $path));
      }
      break;

    case 'DELETE':
      // Implementasi untuk delete jalur
      if (preg_match('/^jalur\/([a-zA-Z0-9-]+)$/', $path, $matches)) {
        echo json_encode(ResponseHelper::error('DELETE method not implemented yet'));
      } else {
        echo json_encode(ResponseHelper::notFound('Route not found: DELETE /' . $path));
      }
      break;

    default:
      echo json_encode(ResponseHelper::badRequest('Method not allowed: ' . $requestMethod));
      break;
  }
} catch (Exception $e) {
  // Log error untuk debugging
  error_log("API Error: " . $e->getMessage());
  error_log("Stack trace: " . $e->getTraceAsString());
  
  echo json_encode(ResponseHelper::error("Internal server error: " . $e->getMessage()));
}