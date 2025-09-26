<?php

namespace App\Helpers;

use Exception;

class UploadHelper
{
  private static string $uploadDir = 'uploads/';
  private static array $allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
  ];
  private static int $maxFileSize = 5 * 1024 * 1024; // 5MB

  /**
   * Upload gambar dari base64 string
   */
  public static function uploadBase64Image(string $base64Data, string $folder = 'images'): string
  {
    try {
      // Pastikan folder upload ada
      self::ensureUploadDirectory($folder);

      // Parse base64 data
      $imageData = self::parseBase64Image($base64Data);

      // Validasi gambar
      self::validateImage($imageData);

      // Generate nama file unik
      $filename = self::generateUniqueFilename($imageData['extension']);
      $filePath = self::$uploadDir . $folder . '/' . $filename;

      // Simpan file
      if (!file_put_contents($filePath, $imageData['data'])) {
        throw new Exception('Gagal menyimpan file gambar');
      }

      // Return URL relatif
      return $folder . '/' . $filename;
    } catch (Exception $e) {
      throw new Exception('Upload gagal: ' . $e->getMessage());
    }
  }

  /**
   * Upload gambar dari file upload (multipart/form-data)
   */
  public static function uploadImageFile(array $file, string $folder = 'images'): string
  {
    try {
      // Validasi file upload
      self::validateUploadedFile($file);

      // Pastikan folder upload ada
      self::ensureUploadDirectory($folder);

      // Validasi tipe file
      if (!isset(self::$allowedTypes[$file['type']])) {
        throw new Exception('Tipe file tidak didukung. Gunakan: JPG, PNG');
      }

      // Validasi ukuran file
      if ($file['size'] > self::$maxFileSize) {
        throw new Exception('Ukuran file terlalu besar. Maksimal ' . (self::$maxFileSize / 1024 / 1024) . 'MB');
      }

      // Generate nama file unik
      $extension = self::$allowedTypes[$file['type']];
      $filename = self::generateUniqueFilename($extension);
      $targetPath = self::$uploadDir . $folder . '/' . $filename;

      // Pindahkan file
      if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Gagal memindahkan file upload');
      }

      return $folder . '/' . $filename;
    } catch (Exception $e) {
      throw new Exception('Upload gagal: ' . $e->getMessage());
    }
  }

  /**
   * Hapus file gambar
   */
  public static function deleteImage(string $imagePath): bool
  {
    $fullPath = self::$uploadDir . $imagePath;

    if (file_exists($fullPath)) {
      return unlink($fullPath);
    }

    return false;
  }

  /**
   * Resize gambar
   */
  public static function resizeImage(string $imagePath, int $maxWidth = 800, int $maxHeight = 600): bool
  {
    try {
      $fullPath = self::$uploadDir . $imagePath;

      if (!file_exists($fullPath)) {
        throw new Exception('File tidak ditemukan');
      }

      $imageInfo = getimagesize($fullPath);
      if (!$imageInfo) {
        throw new Exception('File bukan gambar yang valid');
      }

      [$width, $height, $type] = $imageInfo;

      // Skip jika sudah lebih kecil dari target
      if ($width <= $maxWidth && $height <= $maxHeight) {
        return true;
      }

      // Hitung dimensi baru dengan mempertahankan aspek rasio
      $ratio = min($maxWidth / $width, $maxHeight / $height);
      $newWidth = (int)($width * $ratio);
      $newHeight = (int)($height * $ratio);

      // Buat resource gambar berdasarkan tipe
      $sourceImage = self::createImageResource($fullPath, $type);

      // Buat gambar baru dengan ukuran yang diinginkan
      $targetImage = imagecreatetruecolor($newWidth, $newHeight);

      // Pertahankan transparansi untuk PNG dan GIF
      self::preserveTransparency($targetImage, $type);

      // Resize gambar
      imagecopyresampled($targetImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

      // Simpan gambar yang sudah diresize
      self::saveImage($targetImage, $fullPath, $type);

      // Bersihkan memory
      imagedestroy($sourceImage);
      imagedestroy($targetImage);

      return true;
    } catch (Exception $e) {
      throw new Exception('Resize gagal: ' . $e->getMessage());
    }
  }

  /**
   * Get URL lengkap untuk gambar
   */
  public static function getImageUrl(string $imagePath): string
  {
    $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost/public';
    return rtrim($baseUrl, '/') . '/' . self::$uploadDir . ltrim($imagePath, '/');
  }

  /**
   * Parse base64 image data
   */
  private static function parseBase64Image(string $base64Data): array
  {
    // Format: data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD...
    if (!preg_match('/^data:image\/(\w+);base64,(.+)$/', $base64Data, $matches)) {
      throw new Exception('Format base64 tidak valid');
    }

    $extension = $matches[1];
    $data = base64_decode($matches[2]);

    if ($data === false) {
      throw new Exception('Data base64 tidak valid');
    }

    // Convert extension ke format yang didukung
    $mimeType = 'image/' . $extension;
    if (!isset(self::$allowedTypes[$mimeType])) {
      throw new Exception('Format gambar tidak didukung: ' . $extension);
    }

    return [
      'data' => $data,
      'extension' => self::$allowedTypes[$mimeType],
      'mime_type' => $mimeType,
      'size' => strlen($data)
    ];
  }

  /**
   * Validasi data gambar
   */
  private static function validateImage(array $imageData): void
  {
    if ($imageData['size'] > self::$maxFileSize) {
      throw new Exception('Ukuran gambar terlalu besar. Maksimal ' . (self::$maxFileSize / 1024 / 1024) . 'MB');
    }

    // Validasi apakah benar-benar gambar dengan mengecek header
    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $detectedType = $finfo->buffer($imageData['data']);

    if (!in_array($detectedType, array_keys(self::$allowedTypes))) {
      throw new Exception('File bukan gambar yang valid');
    }
  }

  /**
   * Validasi file upload
   */
  private static function validateUploadedFile(array $file): void
  {
    if (!isset($file['error']) || is_array($file['error'])) {
      throw new Exception('Parameter file tidak valid');
    }

    switch ($file['error']) {
      case UPLOAD_ERR_OK:
        break;
      case UPLOAD_ERR_NO_FILE:
        throw new Exception('Tidak ada file yang diupload');
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
        throw new Exception('File terlalu besar');
      default:
        throw new Exception('Error upload yang tidak diketahui');
    }
  }

  /**
   * Pastikan direktori upload ada
   */
  private static function ensureUploadDirectory(string $folder): void
  {
    $fullPath = self::$uploadDir . $folder;

    if (!is_dir($fullPath)) {
      if (!mkdir($fullPath, 0755, true)) {
        throw new Exception('Gagal membuat direktori upload: ' . $fullPath);
      }
    }

    if (!is_writable($fullPath)) {
      throw new Exception('Direktori upload tidak dapat ditulis: ' . $fullPath);
    }
  }

  /**
   * Generate nama file unik
   */
  private static function generateUniqueFilename(string $extension): string
  {
    return uniqid() . '_' . time() . '.' . $extension;
  }

  /**
   * Buat resource gambar berdasarkan tipe
   */
  private static function createImageResource(string $path, int $type)
  {
    switch ($type) {
      case IMAGETYPE_JPEG:
        return imagecreatefromjpeg($path);
      case IMAGETYPE_PNG:
        return imagecreatefrompng($path);
      case IMAGETYPE_GIF:
        return imagecreatefromgif($path);
      case IMAGETYPE_WEBP:
        return imagecreatefromwebp($path);
      default:
        throw new Exception('Tipe gambar tidak didukung');
    }
  }

  /**
   * Pertahankan transparansi
   */
  private static function preserveTransparency($image, int $type): void
  {
    if ($type == IMAGETYPE_PNG) {
      imagealphablending($image, false);
      imagesavealpha($image, true);
      $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
      imagefilledrectangle($image, 0, 0, imagesx($image), imagesy($image), $transparent);
    } elseif ($type == IMAGETYPE_GIF) {
      $transparent = imagecolorallocate($image, 255, 255, 255);
      imagecolortransparent($image, $transparent);
    }
  }

  /**
   * Simpan gambar berdasarkan tipe
   */
  private static function saveImage($image, string $path, int $type): void
  {
    switch ($type) {
      case IMAGETYPE_JPEG:
        imagejpeg($image, $path, 90);
        break;
      case IMAGETYPE_PNG:
        imagepng($image, $path, 9);
        break;
      case IMAGETYPE_GIF:
        imagegif($image, $path);
        break;
      case IMAGETYPE_WEBP:
        imagewebp($image, $path, 90);
        break;
      default:
        throw new Exception('Tipe gambar tidak didukung untuk penyimpanan');
    }
  }
}
