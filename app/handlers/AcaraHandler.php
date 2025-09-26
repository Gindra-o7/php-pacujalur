<?php

namespace App\Handlers;

use App\Services\AcaraService;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidationHelper;
use App\Helpers\UploadHelper;

class AcaraHandler
{
  private $acaraService;

  public function __construct()
  {
    $this->acaraService = new AcaraService();
  }

  public function getAll()
  {
    $result = $this->acaraService->getAll();

    // Convert image paths to full URLs
    foreach ($result['terdekat'] as &$acara) {
      if (!empty($acara['image_url']) && !filter_var($acara['image_url'], FILTER_VALIDATE_URL)) {
        $acara['image_url'] = UploadHelper::getImageUrl($acara['image_url']);
      }
    }

    foreach ($result['lainnya'] as &$acara) {
      if (!empty($acara['image_url']) && !filter_var($acara['image_url'], FILTER_VALIDATE_URL)) {
        $acara['image_url'] = UploadHelper::getImageUrl($acara['image_url']);
      }
    }

    echo json_encode(ResponseHelper::success($result), JSON_UNESCAPED_UNICODE);
  }

  public function getById($id)
  {
    $result = $this->acaraService->getById($id);

    // Convert image path to full URL
    if ($result && !empty($result['image_url']) && !filter_var($result['image_url'], FILTER_VALIDATE_URL)) {
      $result['image_url'] = UploadHelper::getImageUrl($result['image_url']);
    }

    echo json_encode(ResponseHelper::success($result), JSON_UNESCAPED_UNICODE);
  }

  public function create()
  {
    try {
      $data = json_decode(file_get_contents('php://input'), true);

      if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(ResponseHelper::badRequest('Data JSON tidak valid'));
        return;
      }

      $validatedData = $this->validateAcaraData($data);

      // Handle image upload jika ada base64 image
      if (!empty($validatedData['image_base64'])) {
        $imagePath = UploadHelper::uploadBase64Image($validatedData['image_base64'], 'acara');
        $validatedData['image_url'] = $imagePath;

        // Resize image untuk optimasi
        UploadHelper::resizeImage($imagePath, 800, 600);

        // Hapus data base64 setelah upload
        unset($validatedData['image_base64']);
      }

      $result = $this->acaraService->create($validatedData);

      // Get created acara with full image URL
      $createdAcara = $this->acaraService->getById($result['id']);
      if (!empty($createdAcara['image_url']) && !filter_var($createdAcara['image_url'], FILTER_VALIDATE_URL)) {
        $createdAcara['image_url'] = UploadHelper::getImageUrl($createdAcara['image_url']);
      }

      echo json_encode(ResponseHelper::created($createdAcara, "Acara berhasil ditambahkan"), JSON_UNESCAPED_UNICODE);
    } catch (\Exception $e) {
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

      $validatedData = $this->validateAcaraData($data, false); // false = update mode

      // Get existing acara untuk mendapatkan image lama
      $existingAcara = $this->acaraService->getById($id);

      // Handle image upload jika ada base64 image baru
      if (!empty($validatedData['image_base64'])) {
        // Hapus gambar lama jika ada
        if (!empty($existingAcara['image_url']) && !filter_var($existingAcara['image_url'], FILTER_VALIDATE_URL)) {
          UploadHelper::deleteImage($existingAcara['image_url']);
        }

        $imagePath = UploadHelper::uploadBase64Image($validatedData['image_base64'], 'acara');
        $validatedData['image_url'] = $imagePath;

        // Resize image untuk optimasi
        UploadHelper::resizeImage($imagePath, 800, 600);

        // Hapus data base64 setelah upload
        unset($validatedData['image_base64']);
      } elseif (!isset($validatedData['image_url']) && !empty($existingAcara['image_url'])) {
        // Pertahankan image lama jika tidak ada image baru
        $validatedData['image_url'] = $existingAcara['image_url'];
      }

      $result = $this->acaraService->update($id, $validatedData);

      // Get updated acara with full image URL
      $updatedAcara = $this->acaraService->getById($id);
      if (!empty($updatedAcara['image_url']) && !filter_var($updatedAcara['image_url'], FILTER_VALIDATE_URL)) {
        $updatedAcara['image_url'] = UploadHelper::getImageUrl($updatedAcara['image_url']);
      }

      echo json_encode(ResponseHelper::success($updatedAcara, "Acara berhasil diperbarui"), JSON_UNESCAPED_UNICODE);
    } catch (\Exception $e) {
      echo json_encode(ResponseHelper::badRequest($e->getMessage()));
    }
  }

  public function delete($id)
  {
    try {
      // Get existing acara untuk mendapatkan image path
      $existingAcara = $this->acaraService->getById($id);

      $result = $this->acaraService->delete($id);

      // Hapus file gambar jika ada
      if ($existingAcara && !empty($existingAcara['image_url']) && !filter_var($existingAcara['image_url'], FILTER_VALIDATE_URL)) {
        UploadHelper::deleteImage($existingAcara['image_url']);
      }

      echo json_encode(ResponseHelper::success($result, "Acara berhasil dihapus"), JSON_UNESCAPED_UNICODE);
    } catch (\Exception $e) {
      echo json_encode(ResponseHelper::error($e->getMessage()));
    }
  }

  /**
   * Upload image via multipart/form-data
   */
  public function uploadImage()
  {
    try {
      if (!isset($_FILES['image'])) {
        echo json_encode(ResponseHelper::badRequest('File gambar tidak ditemukan'));
        return;
      }

      $imagePath = UploadHelper::uploadImageFile($_FILES['image'], 'acara');

      // Resize image untuk optimasi
      UploadHelper::resizeImage($imagePath, 800, 600);

      $result = [
        'image_path' => $imagePath,
        'image_url' => UploadHelper::getImageUrl($imagePath)
      ];

      echo json_encode(ResponseHelper::success($result, "Gambar berhasil diupload"), JSON_UNESCAPED_UNICODE);
    } catch (\Exception $e) {
      echo json_encode(ResponseHelper::badRequest($e->getMessage()));
    }
  }

  private function validateAcaraData($data, $isCreate = true)
  {
    $requiredFields = ['nama', 'lokasi', 'tgl_mulai', 'tgl_selesai'];

    if ($isCreate) {
      ValidationHelper::validateRequired($data, $requiredFields);
    } else {
      // Untuk update, hanya validasi field yang ada
      foreach ($requiredFields as $field) {
        if (isset($data[$field]) && empty(trim($data[$field]))) {
          throw new \Exception("Field '{$field}' tidak boleh kosong");
        }
      }
    }

    if (isset($data['nama'])) {
      ValidationHelper::validateLength($data['nama'], 1, 255, 'Nama');
    }

    if (isset($data['lokasi'])) {
      ValidationHelper::validateLength($data['lokasi'], 1, 255, 'Lokasi');
    }

    if (isset($data['tgl_mulai'])) {
      ValidationHelper::validateDate($data['tgl_mulai'], 'Tanggal Mulai');
    }

    if (isset($data['tgl_selesai'])) {
      ValidationHelper::validateDate($data['tgl_selesai'], 'Tanggal Selesai');
    }

    // Validasi image_url jika berupa URL eksternal
    if (!empty($data['image_url']) && filter_var($data['image_url'], FILTER_VALIDATE_URL)) {
      ValidationHelper::validateUrl($data['image_url']);
    }

    if (!empty($data['deskripsi'])) {
      ValidationHelper::validateLength($data['deskripsi'], 0, 2000, 'Deskripsi');
    }

    // Validasi tanggal mulai tidak lebih dari tanggal selesai
    if (isset($data['tgl_mulai']) && isset($data['tgl_selesai'])) {
      $tglMulai = new \DateTime($data['tgl_mulai']);
      $tglSelesai = new \DateTime($data['tgl_selesai']);

      if ($tglMulai > $tglSelesai) {
        throw new \Exception('Tanggal mulai tidak boleh lebih dari tanggal selesai');
      }
    }

    return $data;
  }
}
