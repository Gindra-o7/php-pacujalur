<?php

// Manual Autoloader
spl_autoload_register(function ($class) {
    // Definisikan namespace prefix dan direktori dasarnya
    $prefixes = [
        'App\\' => __DIR__ . '/../app/',
        'Configs\\' => __DIR__ . '/../configs/',
        'Middlewares\\' => __DIR__ . '/../middlewares/',
        'Routes\\' => __DIR__ . '/../routes/',
    ];

    foreach ($prefixes as $prefix => $base_dir) {
        // Cek apakah kelas menggunakan prefix ini
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        // Dapatkan nama kelas relatif
        $relative_class = substr($class, $len);

        // Ganti namespace separator dengan directory separator dan tambahkan .php
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        // Jika file ada, include file tersebut
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});


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