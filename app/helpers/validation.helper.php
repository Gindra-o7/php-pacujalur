<?php

namespace App\Helpers;

use Exception;

class ValidationHelper
{
  public static function validateRequired(array $data, array $requiredFields): void
  {
    foreach ($requiredFields as $field) {
      if (!isset($data[$field]) || empty(trim($data[$field]))) {
        throw new Exception("Field '{$field}' harus diisi");
      }
    }
  }

  public static function validateEmail(string $email): void
  {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      throw new Exception("Format email tidak valid");
    }
  }

  public static function validatePassword(string $password, int $minLength = 6): void
  {
    if (strlen($password) < $minLength) {
      throw new Exception("Password minimal {$minLength} karakter");
    }
  }

  public static function validatePhone(string $phone): void
  {
    // Allow formats: 08123456789, +6281234567890, 081-234-567-890, dll
    if (!preg_match('/^(\+62|62|0)[0-9]{8,13}$/', preg_replace('/[-\s()]/', '', $phone))) {
      throw new Exception("Format nomor telepon tidak valid");
    }
  }

  public static function validateUrl(string $url): void
  {
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
      throw new Exception("Format URL tidak valid: {$url}");
    }
  }

  public static function validateLength(string $value, int $min, int $max, string $fieldName): void
  {
    $length = strlen(trim($value));
    
    if ($length < $min) {
      throw new Exception("{$fieldName} minimal {$min} karakter");
    }
    
    if ($length > $max) {
      throw new Exception("{$fieldName} maksimal {$max} karakter");
    }
  }

  public static function validateChoice(string $value, array $allowedValues, string $fieldName): void
  {
    if (!in_array($value, $allowedValues)) {
      throw new Exception("{$fieldName} harus salah satu dari: " . implode(', ', $allowedValues));
    }
  }

  public static function validateNumeric($value, string $fieldName): void
  {
    if (!is_numeric($value)) {
      throw new Exception("{$fieldName} harus berupa angka");
    }
  }

  public static function validateInteger($value, int $min = null, int $max = null, string $fieldName = 'Value'): void
  {
    if (!filter_var($value, FILTER_VALIDATE_INT)) {
      throw new Exception("{$fieldName} harus berupa bilangan bulat");
    }

    $intValue = (int)$value;
    
    if ($min !== null && $intValue < $min) {
      throw new Exception("{$fieldName} minimal {$min}");
    }
    
    if ($max !== null && $intValue > $max) {
      throw new Exception("{$fieldName} maksimal {$max}");
    }
  }

  public static function validateDate(string $date, string $fieldName = 'Date'): void
  {
    $dateObj = \DateTime::createFromFormat('Y-m-d', $date);
    
    if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
      throw new Exception("{$fieldName} harus dalam format YYYY-MM-DD");
    }
  }

  public static function validateDateTime(string $datetime, string $fieldName = 'DateTime'): void
  {
    $datetimeObj = \DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
    
    if (!$datetimeObj || $datetimeObj->format('Y-m-d H:i:s') !== $datetime) {
      throw new Exception("{$fieldName} harus dalam format YYYY-MM-DD HH:mm:ss");
    }
  }

  public static function validateArrayObjects(array $items, array $requiredFields, string $arrayName): void
  {
    if (!is_array($items)) {
      throw new Exception("{$arrayName} harus berupa array");
    }

    foreach ($items as $index => $item) {
      if (!is_array($item)) {
        throw new Exception("{$arrayName}[{$index}] harus berupa object");
      }

      foreach ($requiredFields as $field) {
        if (!isset($item[$field]) || empty(trim($item[$field]))) {
          throw new Exception("{$arrayName}[{$index}].{$field} harus diisi");
        }
      }
    }
  }

  public static function sanitizeString(string $input): string
  {
    return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
  }

  public static function sanitizeArray(array $data): array
  {
    $sanitized = [];
    
    foreach ($data as $key => $value) {
      if (is_string($value)) {
        $sanitized[$key] = self::sanitizeString($value);
      } elseif (is_array($value)) {
        $sanitized[$key] = self::sanitizeArray($value);
      } else {
        $sanitized[$key] = $value;
      }
    }
    
    return $sanitized;
  }

  public static function validateUuid(string $uuid, string $fieldName = 'ID'): void
  {
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid)) {
      throw new Exception("{$fieldName} harus berupa UUID yang valid");
    }
  }

  public static function validateImageExtension(string $filename): void
  {
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowedExtensions)) {
      throw new Exception("Format file tidak didukung. Gunakan: " . implode(', ', $allowedExtensions));
    }
  }

  public static function validateSocialMedia(string $platform): void
  {
    $allowedPlatforms = ['FACEBOOK', 'INSTAGRAM', 'TWITTER', 'TIKTOK', 'YOUTUBE'];
    self::validateChoice(strtoupper($platform), $allowedPlatforms, 'Platform media sosial');
  }

  public static function validateJalurData(array $data): array
  {
    $data = self::sanitizeArray($data);

    self::validateRequired($data, ['nama', 'desa', 'kecamatan', 'kabupaten', 'provinsi']);

    self::validateLength($data['nama'], 2, 255, 'Nama jalur');
    self::validateLength($data['desa'], 2, 255, 'Nama desa');
    self::validateLength($data['kecamatan'], 2, 255, 'Nama kecamatan');
    self::validateLength($data['kabupaten'], 2, 255, 'Nama kabupaten');
    self::validateLength($data['provinsi'], 2, 255, 'Nama provinsi');

    if (!empty($data['deskripsi'])) {
      self::validateLength($data['deskripsi'], 10, 2000, 'Deskripsi');
    }

    if (isset($data['medsos']) && is_array($data['medsos'])) {
      foreach ($data['medsos'] as $index => $medsos) {
        if (empty($medsos['media']) || empty($medsos['link'])) {
          throw new Exception("Data medsos[{$index}] tidak lengkap - media dan link harus diisi");
        }

        self::validateSocialMedia($medsos['media']);
        self::validateUrl($medsos['link']);
      }
    }

    if (isset($data['galeri']) && is_array($data['galeri'])) {
      foreach ($data['galeri'] as $index => $galeri) {
        if (empty($galeri['image_url'])) {
          throw new Exception("Image URL galeri[{$index}] harus diisi");
        }

        self::validateUrl($galeri['image_url']);

        if (!empty($galeri['judul'])) {
          self::validateLength($galeri['judul'], 2, 255, "Judul galeri[{$index}]");
        }

        if (!empty($galeri['caption'])) {
          self::validateLength($galeri['caption'], 2, 500, "Caption galeri[{$index}]");
        }
      }
    }

    return $data;
  }

  public static function validateAuthRegister(array $data): array
  {
    $data = self::sanitizeArray($data);

    self::validateRequired($data, ['email', 'password', 'full_name']);

    self::validateEmail($data['email']);

    self::validatePassword($data['password'], 6);

    self::validateLength($data['full_name'], 2, 255, 'Nama lengkap');

    if (!empty($data['phone'])) {
      self::validatePhone($data['phone']);
    }

    return $data;
  }
} 