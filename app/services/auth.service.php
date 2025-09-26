<?php

namespace App\Services;

use App\Repositories\AuthRepository;
use Middlewares\AuthMiddleware;
use Exception;

class AuthService
{
  private AuthRepository $authRepository;

  public function __construct()
  {
    $this->authRepository = new AuthRepository();
  }

  /**
   * Login admin
   */
  public function loginAdmin(array $data): array
  {
    $this->validateLoginData($data);

    $user = $this->authRepository->findAdminByEmail($data['email']);

    if (!$user) {
      throw new Exception('Email atau password salah');
    }

    if (!AuthMiddleware::verifyPassword($data['password'], $user['password'])) {
      throw new Exception('Email atau password salah');
    }

    // Generate token
    $token = AuthMiddleware::generateToken($user);

    // Remove password dari response
    unset($user['password']);

    return [
      'user' => $user,
      'token' => $token,
      'token_type' => 'Bearer',
      'expires_in' => 24 * 60 * 60 // 24 jam dalam detik
    ];
  }

  /**
   * Register admin baru
   */
  public function registerAdmin(array $data): array
  {
    $this->validateRegistrationData($data);

    // Cek apakah email sudah terdaftar
    if ($this->authRepository->findAdminByEmail($data['email'])) {
      throw new Exception('Email sudah terdaftar');
    }

    // Hash password
    $data['password'] = AuthMiddleware::hashPassword($data['password']);
    $data['role'] = 'ADMIN'; // Force role admin

    $user = $this->authRepository->createAdmin($data);

    // Generate token
    $token = AuthMiddleware::generateToken($user);

    // Remove password dari response
    unset($user['password']);

    return [
      'user' => $user,
      'token' => $token,
      'token_type' => 'Bearer',
      'expires_in' => 24 * 60 * 60
    ];
  }

  /**
   * Get profile admin yang sedang login
   */
  public function getProfile(): array
  {
    $user = AuthMiddleware::getCurrentUser();

    if (!$user) {
      throw new Exception('User tidak ditemukan');
    }

    return $this->authRepository->findAdminByEmail($user['email'], false);
  }

  /**
   * Validasi data login
   */
  private function validateLoginData(array $data): void
  {
    if (empty($data['email'])) {
      throw new Exception('Email harus diisi');
    }

    if (empty($data['password'])) {
      throw new Exception('Password harus diisi');
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
      throw new Exception('Format email tidak valid');
    }
  }

  /**
   * Validasi data registrasi
   */
  private function validateRegistrationData(array $data): void
  {
    $requiredFields = ['email', 'password', 'full_name'];

    foreach ($requiredFields as $field) {
      if (empty($data[$field])) {
        throw new Exception("Field '{$field}' harus diisi");
      }
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
      throw new Exception('Format email tidak valid');
    }

    if (strlen($data['password']) < 6) {
      throw new Exception('Password minimal 6 karakter');
    }

    if (isset($data['phone']) && !empty($data['phone'])) {
      if (!preg_match('/^[0-9]{10,13}$/', $data['phone'])) {
        throw new Exception('Format nomor telepon tidak valid (10-13 digit)');
      }
    }
  }
}
