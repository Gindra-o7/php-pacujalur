<?php

namespace Middlewares;

use App\Helpers\ResponseHelper;
use Configs\DatabaseConfig;
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

  // Fungsi helper untuk base64url encoding
  private static function base64url_encode(string $data): string
  {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }

  // Fungsi helper untuk base64url decoding
  private static function base64url_decode(string $data): string
  {
    return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
  }

  /**
   * Middleware untuk memverifikasi token JWT dan role admin
   */
  public static function verifyAdminToken(): bool
  {
    self::init();

    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

    if (!$authHeader) {
      self::sendUnauthorizedResponse('Token tidak ditemukan');
      return false;
    }

    if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
      self::sendUnauthorizedResponse('Format token tidak valid');
      return false;
    }

    $token = $matches[1];
    $parts = explode('.', $token);

    if (count($parts) !== 3) {
      self::sendUnauthorizedResponse('Token tidak valid: struktur salah');
      return false;
    }

    [$header, $payload, $signature] = $parts;
    
    $decoded_header = self::base64url_decode($header);
    $decoded_payload = self::base64url_decode($payload);
    
    // Verifikasi signature
    $expected_signature = hash_hmac('sha256', "$header.$payload", self::$jwtSecret, true);
    $expected_base64_signature = self::base64url_encode($expected_signature);

    if (!hash_equals($expected_base64_signature, $signature)) {
        self::sendUnauthorizedResponse('Token tidak valid: signature tidak cocok');
        return false;
    }

    $decoded = json_decode($decoded_payload);

    // Cek waktu kedaluwarsa
    if (isset($decoded->exp) && $decoded->exp < time()) {
        self::sendUnauthorizedResponse('Token telah kedaluwarsa');
        return false;
    }

    try {
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
    
    // Header
    $header = [
        'alg' => 'HS256',
        'typ' => 'JWT'
    ];
    $header_encoded = self::base64url_encode(json_encode($header));
    
    // Payload
    $payload = [
      'iss' => $_ENV['APP_NAME'] ?? 'PacuJalur API',
      'iat' => time(),
      'exp' => time() + (24 * 60 * 60), // 24 jam
      'email' => $userData['email'],
      'role' => $userData['role']
    ];
    $payload_encoded = self::base64url_encode(json_encode($payload));

    // Signature
    $signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", self::$jwtSecret, true);
    $signature_encoded = self::base64url_encode($signature);
    
    return "$header_encoded.$payload_encoded.$signature_encoded";
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