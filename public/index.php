<?php

use App\Helpers\ResponseHelper;
use App\Handlers\GlobalHandler;
use Routes\GlobalRoute;

header("Content-Type: application/json; charset=UTF-8");

$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'GET') {
  $uri = parse_url($requestUri, PHP_URL_PATH);
  $uri = trim($uri, '/');

  switch ($uri) {
    case '':
      header('Content-Type: application/json');
      echo json_encode(ResponseHelper::success(GlobalHandler::introduce()));
      break;
    case 'health':
      header('Content-Type: application/json');
      echo json_encode(ResponseHelper::success(GlobalHandler::health()));
      break;
    default:
      header('Content-Type: application/json');
      echo json_encode(ResponseHelper::notFound());
      break;
  }
} else {
  header('Content-Type: application/json');
  echo json_encode(ResponseHelper::badRequest('Method not allowed'));
}
