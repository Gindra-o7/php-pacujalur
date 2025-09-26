<?php

namespace App\Handlers;

use App\Services\PenginapanService;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidationHelper;
use App\Helpers\UploadHelper;
use Exception;

class PenginapanHandler
{
  private $penginapanService;

  public function __construct()
  {
    $this->penginapanService = new PenginapanService();
  }

  public function getAll()
  {
    try {
      $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
      $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

      $result = $this->penginapanService->getAll($page, $limit);

      // Convert image paths to full URLs
      if (isset($result['data'])) {
        foreach ($result['data'] as &$penginapan) {
          if (!empty($penginapan['image_url']) && !filter_var($penginapan['image_url'], FILTER_VALIDATE_URL)) {
            $penginapan['image_url'] = UploadHelper::getImageUrl($penginapan['image_url']);
          }

          // Convert galeri images jika ada
          if (isset($penginapan['galeri']) && is_array($penginapan['galeri'])) {
            $penginapan['galeri'] = $this->convertGaleriImageUrls($penginapan['galeri']);
          }
        }
      }

      echo json_encode(ResponseHelper::success($result), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
      echo json_encode(ResponseHelper::error("Gagal mendapatkan data penginapan: " . $e->getMessage()));
    }
  }

  public function getById($id)
  {
    try {
      $result = $this->penginapanService->getById($id);

      if (!$result) {
        echo json_encode(ResponseHelper::notFound('Penginapan tidak ditemukan'));
        return;
      }

      // Convert main image URL
      if (!empty($result['image_url']) && !filter_var($result['image_url'], FILTER_VALIDATE_URL)) {
        $result['image_url'] = UploadHelper::getImageUrl($result['image_url']);
      }

      // Convert galeri images jika ada
      if (isset($result['galeri']) && is_array($result['galeri'])) {
        $result['galeri'] = $this->convertGaleriImageUrls($result['galeri']);
      }

      echo json_encode(ResponseHelper::success($result), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
      echo json_encode(ResponseHelper::error("Gagal mendapatkan data penginapan: " . $e->getMessage()));
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

      $this->validateData($data);

      // Handle main image upload dari base64
      if (!empty($data['image_base64'])) {
        $imagePath = UploadHelper::uploadBase64Image($data['image_base64'], 'penginapan');
        $data['image_url'] = $imagePath;

        // Resize main image
        UploadHelper::resizeImage($imagePath, 800, 600);

        unset($data['image_base64']);
      }

      // Handle galeri images
      if (isset($data['galeri']) && is_array($data['galeri'])) {
        $data['galeri'] = $this->processGaleriImages($data['galeri']);
      }

      $result = $this->penginapanService->create($data);

      // Get created penginapan with converted URLs
      $newPenginapan = $this->penginapanService->getById($result['id']);

      if (!empty($newPenginapan['image_url']) && !filter_var($newPenginapan['image_url'], FILTER_VALIDATE_URL)) {
        $newPenginapan['image_url'] = UploadHelper::getImageUrl($newPenginapan['image_url']);
      }

      if (isset($newPenginapan['galeri'])) {
        $newPenginapan['galeri'] = $this->convertGaleriImageUrls($newPenginapan['galeri']);
      }

      echo json_encode(ResponseHelper::created($newPenginapan, "Penginapan berhasil ditambahkan"), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
      echo json_encode(ResponseHelper::badRequest($e->getMessage()));
    }
  }

  public function update($id)
  {
    try {
      $data = json_decode(file_get_contents('php://input'), true);

      if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(ResponseHelper::badRequest('Data JSON tidak valid'));
        return;
      }

      $this->validateData($data, false); // false = update mode

      $existing = $this->penginapanService->getById($id);
      if (!$existing) {
        echo json_encode(ResponseHelper::notFound('Penginapan tidak ditemukan'));
        return;
      }

      // Handle main image update
      if (!empty($data['image_base64'])) {
        // Delete old main image
        if (!empty($existing['image_url']) && !filter_var($existing['image_url'], FILTER_VALIDATE_URL)) {
          UploadHelper::deleteImage($existing['image_url']);
        }

        $imagePath = UploadHelper::uploadBase64Image($data['image_base64'], 'penginapan');
        $data['image_url'] = $imagePath;

        UploadHelper::resizeImage($imagePath, 800, 600);

        unset($data['image_base64']);
      } elseif (!isset($data['image_url']) && !empty($existing['image_url'])) {
        // Pertahankan image lama jika tidak ada image baru
        $data['image_url'] = $existing['image_url'];
      }

      // Handle galeri images update
      if (isset($data['galeri']) && is_array($data['galeri'])) {
        // Cleanup old galeri images
        $this->cleanupOldGaleriImages($existing['galeri'] ?? [], $data['galeri']);

        // Process new galeri images
        $data['galeri'] = $this->processGaleriImages($data['galeri']);
      }

      $this->penginapanService->update($id, $data);

      // Get updated penginapan with converted URLs
      $updatedPenginapan = $this->penginapanService->getById($id);

      if (!empty($updatedPenginapan['image_url']) && !filter_var($updatedPenginapan['image_url'], FILTER_VALIDATE_URL)) {
        $updatedPenginapan['image_url'] = UploadHelper::getImageUrl($updatedPenginapan['image_url']);
      }

      if (isset($updatedPenginapan['galeri'])) {
        $updatedPenginapan['galeri'] = $this->convertGaleriImageUrls($updatedPenginapan['galeri']);
      }

      echo json_encode(ResponseHelper::success($updatedPenginapan, "Penginapan berhasil diperbarui"), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
      echo json_encode(ResponseHelper::badRequest($e->getMessage()));
    }
  }

  public function delete($id)
  {
    try {
      $existing = $this->penginapanService->getById($id);
      if (!$existing) {
        echo json_encode(ResponseHelper::notFound('Penginapan tidak ditemukan'));
        return;
      }

      $this->penginapanService->delete($id);

      // Delete main image
      if (!empty($existing['image_url']) && !filter_var($existing['image_url'], FILTER_VALIDATE_URL)) {
        UploadHelper::deleteImage($existing['image_url']);
      }

      // Delete galeri images
      if (isset($existing['galeri']) && is_array($existing['galeri'])) {
        foreach ($existing['galeri'] as $galeri) {
          if (!empty($galeri['image_url']) && !filter_var($galeri['image_url'], FILTER_VALIDATE_URL)) {
            UploadHelper::deleteImage($galeri['image_url']);
          }
        }
      }

      echo json_encode(ResponseHelper::success(['message' => 'Penginapan berhasil dihapus']), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
      echo json_encode(ResponseHelper::error("Gagal menghapus penginapan: " . $e->getMessage()));
    }
  }

  /**
   * Upload main image untuk penginapan
   */
  public function uploadMainImage()
  {
    try {
      if (!isset($_FILES['image'])) {
        echo json_encode(ResponseHelper::badRequest('File gambar tidak ditemukan'));
        return;
      }

      $imagePath = UploadHelper::uploadImageFile($_FILES['image'], 'penginapan');

      // Resize main image
      UploadHelper::resizeImage($imagePath, 800, 600);

      $result = [
        'image_path' => $imagePath,
        'image_url' => UploadHelper::getImageUrl($imagePath)
      ];

      echo json_encode(ResponseHelper::success($result, "Gambar utama berhasil diupload"), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
      echo json_encode(ResponseHelper::badRequest($e->getMessage()));
    }
  }

  /**
   * Upload multiple images untuk galeri penginapan
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

            $imagePath = UploadHelper::uploadImageFile($file, 'penginapan/galeri');

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
        $imagePath = UploadHelper::uploadImageFile($files, 'penginapan/galeri');
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
        $imagePath = UploadHelper::uploadBase64Image($galeri['image_base64'], 'penginapan/galeri');

        // Resize untuk galeri
        UploadHelper::resizeImage($imagePath, 600, 400);

        $galeri['image_url'] = $imagePath;
        unset($galeri['image_base64']);
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
   * Cleanup old galeri images
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

  private function validateData($data, $isCreate = true)
  {
    $requiredFields = ['nama', 'tipe', 'harga'];

    if ($isCreate) {
      ValidationHelper::validateRequired($data, $requiredFields);
    } else {
      // Untuk update, hanya validasi field yang ada
      foreach ($requiredFields as $field) {
        if (isset($data[$field]) && empty(trim($data[$field]))) {
          throw new Exception("Field '{$field}' tidak boleh kosong");
        }
      }
    }

    if (isset($data['nama'])) {
      ValidationHelper::validateLength($data['nama'], 1, 255, 'Nama');
    }

    if (isset($data['tipe'])) {
      ValidationHelper::validateLength($data['tipe'], 1, 100, 'Tipe');
    }

    if (isset($data['harga'])) {
      ValidationHelper::validateLength($data['harga'], 1, 100, 'Harga');
    }

    // Validasi image_url jika berupa URL eksternal
    if (!empty($data['image_url']) && filter_var($data['image_url'], FILTER_VALIDATE_URL)) {
      ValidationHelper::validateUrl($data['image_url']);
    }

    if (!empty($data['deskripsi'])) {
      ValidationHelper::validateLength($data['deskripsi'], 0, 2000, 'Deskripsi');
    }

    if (!empty($data['maps_url'])) {
      ValidationHelper::validateUrl($data['maps_url']);
    }

    if (isset($data['fasilitas']) && !is_array($data['fasilitas'])) {
      throw new Exception('Fasilitas harus berupa array');
    }
  }
}
