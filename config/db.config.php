<?php

namespace Config;

use PDO;
use PDOException;

class Database
{
  private static $instance = null;

  private function __construct() {}

  public static function getInstance(): PDO
  {
    if (self::$instance === null) {
      $host = $_ENV['DB_HOST'];
      $db   = $_ENV['DB_DATABASE'];
      $user = $_ENV['DB_USERNAME'];
      $pass = $_ENV['DB_PASSWORD'];
      $port = $_ENV['DB_PORT'];
      $charset = 'utf8mb4';

      $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
      $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
      ];

      try {
        self::$instance = new PDO($dsn, $user, $pass, $options);
      } catch (PDOException $e) {
        throw new PDOException($e->getMessage(), (int)$e->getCode());
      }
    }

    return self::$instance;
  }
}
