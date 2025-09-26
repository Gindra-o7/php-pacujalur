<?php

namespace App\Repositories;

use Configs\DatabaseConfig;
use PDO;
use Exception;

class AuthRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = DatabaseConfig::getInstance();
  }

  /**
   * Cari admin berdasarkan email
   */
  public function findAdminByEmail(string $email, bool $includePassword = true): ?array
  {
    $fields = $includePassword
      ? "email, password, full_name, role, phone, created_at"
      : "email, full_name, role, phone, created_at";

    $stmt = $this->db->prepare("
      SELECT {$fields}
      FROM users 
      WHERE email = ? AND role = 'ADMIN'
    ");

    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
  }

  /**
   * Buat admin baru
   */
  public function createAdmin(array $data): array
  {
    $this->db->beginTransaction();

    try {
      $stmt = $this->db->prepare("
        INSERT INTO users (email, password, full_name, role, phone)
        VALUES (:email, :password, :full_name, :role, :phone)
      ");

      $stmt->execute([
        'email' => $data['email'],
        'password' => $data['password'],
        'full_name' => $data['full_name'],
        'role' => $data['role'],
        'phone' => $data['phone'] ?? null
      ]);

      $this->db->commit();

      // Return user data tanpa password
      return $this->findAdminByEmail($data['email'], false);
    } catch (Exception $e) {
      $this->db->rollBack();
      throw new Exception("Gagal membuat akun admin: " . $e->getMessage());
    }
  }

  /**
   * Update data admin
   */
  public function updateAdmin(string $email, array $data): ?array
  {
    $this->db->beginTransaction();

    try {
      $updateFields = [];
      $params = ['email' => $email];

      if (isset($data['full_name'])) {
        $updateFields[] = "full_name = :full_name";
        $params['full_name'] = $data['full_name'];
      }

      if (isset($data['phone'])) {
        $updateFields[] = "phone = :phone";
        $params['phone'] = $data['phone'];
      }

      if (isset($data['password'])) {
        $updateFields[] = "password = :password";
        $params['password'] = $data['password'];
      }

      if (empty($updateFields)) {
        throw new Exception("Tidak ada data yang akan diupdate");
      }

      $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE email = :email AND role = 'ADMIN'";
      $stmt = $this->db->prepare($sql);
      $stmt->execute($params);

      if ($stmt->rowCount() === 0) {
        throw new Exception("Admin tidak ditemukan atau tidak ada perubahan data");
      }

      $this->db->commit();

      return $this->findAdminByEmail($email, false);
    } catch (Exception $e) {
      $this->db->rollBack();
      throw new Exception("Gagal update data admin: " . $e->getMessage());
    }
  }

  /**
   * Hapus admin
   */
  public function deleteAdmin(string $email): bool
  {
    $stmt = $this->db->prepare("DELETE FROM users WHERE email = ? AND role = 'ADMIN'");
    $stmt->execute([$email]);

    return $stmt->rowCount() > 0;
  }

  /**
   * Get semua admin (untuk super admin)
   */
  public function getAllAdmins(): array
  {
    $stmt = $this->db->prepare("
      SELECT email, full_name, role, phone, created_at
      FROM users 
      WHERE role = 'ADMIN'
      ORDER BY created_at DESC
    ");

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
