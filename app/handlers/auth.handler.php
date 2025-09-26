<?php

namespace App\Handlers;

use App\Helpers\ResponseHelper;
use App\Services\AuthService;
use Middlewares\AuthMiddleware;
use Exception;

class AuthHandler
{
  private AuthService $authService;

  public function __construct()
  {
    $this->authService = new AuthService();
  }

  /**
   * Login admin
   */
  public function loginAdmin(): void
  {
    try {
      $data = json_decode(file_get_contents('php://input'), true);

      if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(ResponseHelper::badRequest('Data JSON tidak valid'));
        return;
      }

      $result = $this->authService->loginAdmin($data);

      echo json_encode(ResponseHelper::success($result, "Login berhasil"), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
      echo json_encode(ResponseHelper::badRequest($e->getMessage()));
    }
  }

  /**
   * Register admin baru
   */
  public function registerAdmin(): void
  {
    try {
      $data = json_decode(file_get_contents('php://input'), true);

      if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(ResponseHelper::badRequest('Data JSON tidak valid'));
        return;
      }

      $result = $this->authService->registerAdmin($data);

      echo json_encode(ResponseHelper::created($result, "Registrasi admin berhasil"), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
      echo json_encode(ResponseHelper::badRequest($e->getMessage()));
    }
  }

  /**
   * Get profile admin yang sedang login
   */
  public function getProfile(): void
  {
    try {
      // Verifikasi token admin
      if (!AuthMiddleware::verifyAdminToken()) {
        return; // Response sudah dikirim di middleware
      }

      $result = $this->authService->getProfile();

      echo json_encode(ResponseHelper::success($result, "Data profile berhasil didapatkan"), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
      echo json_encode(ResponseHelper::error("Gagal mendapatkan profile: " . $e->getMessage()));
    }
  }

  /**
   * Logout admin (optional - untuk blacklist token jika diperlukan)
   */
  public function logoutAdmin(): void
  {
    try {
      // Verifikasi token admin
      if (!AuthMiddleware::verifyAdminToken()) {
        return; // Response sudah dikirim di middleware
      }

      // Hapus session jika ada
      if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
      }

      echo json_encode(ResponseHelper::success(null, "Logout berhasil"), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
      echo json_encode(ResponseHelper::error("Gagal logout: " . $e->getMessage()));
    }
  }

  /**
   * Refresh token
   */
  public function refreshToken(): void
  {
    try {
      // Verifikasi token admin
      if (!AuthMiddleware::verifyAdminToken()) {
        return;
      }

      $user = AuthMiddleware::getCurrentUser();
      $token = AuthMiddleware::generateToken($user);

      $result = [
        'token' => $token,
        'token_type' => 'Bearer',
        'expires_in' => 24 * 60 * 60
      ];

      echo json_encode(ResponseHelper::success($result, "Token berhasil direfresh"), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
      echo json_encode(ResponseHelper::error("Gagal refresh token: " . $e->getMessage()));
    }
  }
}