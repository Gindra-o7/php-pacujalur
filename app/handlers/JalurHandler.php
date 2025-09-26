<?php

namespace App\Handlers;

use App\Helpers\PaginationHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\UploadHelper;
use App\Services\JalurService;
use Exception;

class JalurHandler
{
  private JalurService $jalurService;

  public function __construct()
  {
    $this->jalurService = new JalurService();
  }

  public function getAll()
  {
    try {
      ['page' => $page, 'limit' => $limit] = PaginationHelper::parsePaginationQuery();

      $result = $this->jalurService->getAll($page, $limit);

      // Convert image paths to full URLs for galeri
      if (isset($result['data'])) {
        foreach ($result['data'] as &$jalur) {
          $jalur['galeri'] = $this->convertGaleriImageUrls($jalur['galeri'] ?? []);
        }
      }

      echo json_encode(ResponseHelper::success($result, "Data jalur berhasil didapatkan"), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
      echo json_encode(ResponseHelper::error("Gagal mendapatkan data jalur: " . $e->getMessage()));
    }
  }

  public function getById()
  {
    try {
      // Extract ID from URL path
      $pathParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
      $id = end($pathParts);

      if (empty($id)) {
        echo json_encode(ResponseHelper::badRequest('ID jalur diperlukan'));
        return;
      }

      $result = $this->jalurService->getById($id);

      if ($result) {
        // Convert galeri image paths to full URLs
        $result['galeri'] = $this->convertGaleriImageUrls($result['galeri'] ?? []);

        echo json_encode(ResponseHelper::success($result, "Data jalur berhasil didapatkan"), JSON_UNESCAPED_UNICODE);
      } else {
        echo json_encode(ResponseHelper::notFound('Data jalur tidak ditemukan'));
      }
    } catch (Exception $e) {
      echo json_encode(ResponseHelper::error("Gagal mendapatkan data jalur: " . $e->getMessage()));
    }
  }

  public function create()
  {
    try {
      $data = json_decode(file_get_contents('php://input'), true);

      if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(ResponseHelper::badRequest('Data JSON tidak valid'));
        return;
      }

      // Process galeri images jika ada
      if (isset($data['galeri']) && is_array($data['galeri'])) {
        $data['galeri'] = $this->processGaleriImages($data['galeri']);
      }

      $result = $this->jalurService->create($data);

      // Convert galeri image paths to full URLs for response
      if (isset($result['galeri'])) {
        $result['galeri'] = $this->convertGaleriImageUrls($result['galeri']);
      }

      echo json_encode(ResponseHelper::created($result, "Data jalur berhasil ditambahkan"), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
      echo json_encode(ResponseHelper::badRequest($e->getMessage()));
    }
  }

  public function update()
  {
    try {
      // Extract ID from URL path
      $pathParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
      $id = end($pathParts);

      if (empty($id)) {
        echo json_encode(ResponseHelper::badRequest('ID jalur diperlukan'));
        return;
      }

      $data = json_decode(file_get_contents('php://input'), true);

      if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(ResponseHelper::badRequest('Data JSON tidak valid'));
        return;
      }

      // Get existing jalur untuk mendapatkan galeri lama
      $existingJalur = $this->jalurService->getById($id);

      // Process galeri images jika ada
      if (isset($data['galeri']) && is_array($data['galeri'])) {
        // Hapus gambar lama yang tidak digunakan lagi
        $this->cleanupOldGaleriImages($existingJalur['galeri'] ?? [], $data['galeri']);

        // Process gambar baru
        $data['galeri'] = $this->processGaleriImages($data['galeri']);
      }

      $result = $this->jalurService->update($id, $data);

      // Get updated jalur with converted URLs
      $updatedJalur = $this->jalurService->getById($id);
      if ($updatedJalur && isset($updatedJalur['galeri'])) {
        $updatedJalur['galeri'] = $this->convertGaleriImageUrls($updatedJalur['galeri']);
      }

      echo json_encode(ResponseHelper::success($updatedJalur, "Data jalur berhasil diperbarui"), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
      echo json_encode(ResponseHelper::badRequest($e->getMessage()));
    }
  }

  public function delete()
  {
    try {
      // Extract ID from URL path
      $pathParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
      $id = end($pathParts);

      if (empty($id)) {
        echo json_encode(ResponseHelper::badRequest('ID jalur diperlukan'));
        return;
      }

      // Get existing jalur untuk mendapatkan galeri images
      $existingJalur = $this->jalurService->getById($id);

      $result = $this->jalurService->delete($id);

      // Hapus semua file galeri
      if ($existingJalur && isset($existingJalur['galeri'])) {
        foreach ($existingJalur['galeri'] as $galeri) {
          if (!empty($galeri['image_url']) && !filter_var($galeri['image_url'], FILTER_VALIDATE_URL)) {
            UploadHelper::deleteImage($galeri['image_url']);
          }
        }
      }

      echo json_encode(ResponseHelper::success($result, "Data jalur berhasil dihapus"), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
      echo json_encode(ResponseHelper::error("Gagal menghapus data jalur: " . $e->getMessage()));
    }
  }

  /**
   * Upload multiple images untuk galeri
   */
  public function uploadGaleriImages()
  {
    try {
      if (!isset($_FILES['images'])) {
        echo json_encode(ResponseHelper::badRequest('File gambar tidak ditemukan'));
        return;
      }

      $uploadedImages = [];
      $files = $_FILES['images'];

      // Handle multiple file upload
      if (is_array($files['name'])) {
        $fileCount = count($files['name']);

        for ($i = 0; $i < $fileCount; $i++) {
          if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $file = [
              'name' => $files['name'][$i],
              'type' => $files['type'][$i],
              'tmp_name' => $files['tmp_name'][$i],
              'error' => $files['error'][$i],
              'size' => $files['size'][$i]
            ];

            $imagePath = UploadHelper::uploadImageFile($file, 'jalur/galeri');

            // Resize untuk galeri
            UploadHelper::resizeImage($imagePath, 600, 400);

            $uploadedImages[] = [
              'image_path' => $imagePath,
              'image_url' => UploadHelper::getImageUrl($imagePath)
            ];
          }
        }
      } else {
        // Single file upload
        $imagePath = UploadHelper::uploadImageFile($files, 'jalur/galeri');
        UploadHelper::resizeImage($imagePath, 600, 400);

        $uploadedImages[] = [
          'image_path' => $imagePath,
          'image_url' => UploadHelper::getImageUrl($imagePath)
        ];
      }

      echo json_encode(ResponseHelper::success($uploadedImages, "Gambar galeri berhasil diupload"), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
      echo json_encode(ResponseHelper::badRequest($e->getMessage()));
    }
  }

  /**
   * Process galeri images dari base64 atau URL
   */
  private function processGaleriImages(array $galeriData): array
  {
    foreach ($galeriData as &$galeri) {
      // Jika ada base64 image, convert ke file
      if (!empty($galeri['image_base64'])) {
        $imagePath = UploadHelper::uploadBase64Image($galeri['image_base64'], 'jalur/galeri');

        // Resize untuk galeri
        UploadHelper::resizeImage($imagePath, 600, 400);

        $galeri['image_url'] = $imagePath;
        unset($galeri['image_base64']);
      }

      // Jika image_url adalah URL eksternal, biarkan as-is
      // Jika image_url adalah path lokal, validasi filenya ada
      if (!empty($galeri['image_url']) && !filter_var($galeri['image_url'], FILTER_VALIDATE_URL)) {
        $fullPath = 'uploads/' . $galeri['image_url'];
        if (!file_exists($fullPath)) {
          throw new Exception("File gambar tidak ditemukan: " . $galeri['image_url']);
        }
      }
    }

    return $galeriData;
  }

  /**
   * Convert galeri image paths to full URLs
   */
  private function convertGaleriImageUrls(array $galeriData): array
  {
    foreach ($galeriData as &$galeri) {
      if (!empty($galeri['image_url']) && !filter_var($galeri['image_url'], FILTER_VALIDATE_URL)) {
        $galeri['image_url'] = UploadHelper::getImageUrl($galeri['image_url']);
      }
    }

    return $galeriData;
  }

  /**
   * Cleanup old galeri images yang tidak digunakan lagi
   */
  private function cleanupOldGaleriImages(array $oldGaleri, array $newGaleri): void
  {
    $newImagePaths = [];

    // Collect new image paths
    foreach ($newGaleri as $galeri) {
      if (!empty($galeri['image_url']) && !filter_var($galeri['image_url'], FILTER_VALIDATE_URL)) {
        $newImagePaths[] = $galeri['image_url'];
      }
    }

    // Delete old images yang tidak ada di new galeri
    foreach ($oldGaleri as $galeri) {
      if (!empty($galeri['image_url']) && !filter_var($galeri['image_url'], FILTER_VALIDATE_URL)) {
        if (!in_array($galeri['image_url'], $newImagePaths)) {
          UploadHelper::deleteImage($galeri['image_url']);
        }
      }
    }
  }
}
