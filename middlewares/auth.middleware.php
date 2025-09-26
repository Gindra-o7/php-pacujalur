<?php

namespace Middlewares;

use App\Helpers\ResponseHelper;
use Configs\DatabaseConfig;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;
use PDO;

class AuthMiddleware
{
  private static PDO $db;
  private static string $jwtSecret;

  public static function init(): void
  {
    self::$db = DatabaseConfig::getInstance();
    self::$jwtSecret = $_ENV['JWT_SECRET'] ?? 'default_secret_key_change_this';
  }

  /**
   * Middleware untuk memverifikasi token JWT dan role admin
   */
  public static function verifyAdminToken(): bool
  {
    self::init();

    // Ambil authorization header
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

    if (!$authHeader) {
      self::sendUnauthorizedResponse('Token tidak ditemukan');
      return false;
    }

    // Extract token dari "Bearer TOKEN"
    if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
      self::sendUnauthorizedResponse('Format token tidak valid');
      return false;
    }

    $token = $matches[1];

    try {
      // Decode JWT token
      $decoded = JWT::decode($token, new Key(self::$jwtSecret, 'HS256'));
      
      // Verifikasi user masih ada di database
      $stmt = self::$db->prepare("SELECT email, role FROM users WHERE email = ? AND role = 'ADMIN'");
      $stmt->execute([$decoded->email]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$user) {
        self::sendUnauthorizedResponse('User tidak ditemukan atau bukan admin');
        return false;
      }

      // Set user data untuk digunakan handler lainnya
      $_SESSION['user'] = $user;
      
      return true;

    } catch (Exception $e) {
      self::sendUnauthorizedResponse('Token tidak valid: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Generate JWT token
   */
  public static function generateToken(array $userData): string
  {
    self::init();

    $payload = [
      'iss' => $_ENV['APP_NAME'] ?? 'PacuJalur API',
      'iat' => time(),
      'exp' => time() + (24 * 60 * 60), // 24 jam
      'email' => $userData['email'],
      'role' => $userData['role']
    ];

    return JWT::encode($payload, self::$jwtSecret, 'HS256');
  }

  /**
   * Verify password
   */
  public static function verifyPassword(string $password, string $hash): bool
  {
    return password_verify($password, $hash);
  }

  /**
   * Hash password
   */
  public static function hashPassword(string $password): string
  {
    return password_hash($password, PASSWORD_DEFAULT);
  }

  /**
   * Send unauthorized response
   */
  private static function sendUnauthorizedResponse(string $message): void
  {
    http_response_code(401);
    echo json_encode(ResponseHelper::error($message, 401));
    exit;
  }

  /**
   * Get current authenticated user
   */
  public static function getCurrentUser(): ?array
  {
    return $_SESSION['user'] ?? null;
  }
}