<?php

namespace App\Services;

use App\Helpers\PaginationHelper;
use App\Repositories\JalurRepository;
use Exception;

class JalurService
{
  private JalurRepository $jalurRepository;

  public function __construct()
  {
    $this->jalurRepository = new JalurRepository();
  }

  public function getAll(int $page, int $limit): array
  {
    try {
      $offset = PaginationHelper::getOffset($page, $limit);
      ['data' => $data, 'total' => $total] = $this->jalurRepository->findAll($offset, $limit);

      return PaginationHelper::buildPaginatedResponse($data, $page, $limit, $total);
    } catch (Exception $e) {
      throw new Exception("Error getting jalur data: " . $e->getMessage());
    }
  }

  public function getById(string $id): ?array
  {
    try {
      if (empty($id)) {
        throw new Exception("ID jalur tidak boleh kosong");
      }

      return $this->jalurRepository->findById($id);
    } catch (Exception $e) {
      throw new Exception("Error getting jalur by ID: " . $e->getMessage());
    }
  }

  public function create(array $data): array
  {
    try {
      // Validasi data yang masuk
      $this->validateJalurData($data);

      return $this->jalurRepository->create($data);
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  private function validateJalurData(array $data): void
  {
    $requiredFields = ['nama', 'desa', 'kecamatan', 'kabupaten', 'provinsi'];

    foreach ($requiredFields as $field) {
      if (empty($data[$field])) {
        throw new Exception("Field '{$field}' harus diisi");
      }
    }

    // Validasi medsos jika ada
    if (isset($data['medsos']) && is_array($data['medsos'])) {
      $validMedia = ['FACEBOOK', 'INSTAGRAM', 'TWITTER', 'TIKTOK', 'YOUTUBE'];

      foreach ($data['medsos'] as $medsos) {
        if (empty($medsos['media']) || empty($medsos['link'])) {
          throw new Exception("Data medsos tidak lengkap - media dan link harus diisi");
        }

        if (!in_array($medsos['media'], $validMedia)) {
          throw new Exception("Media sosial '{$medsos['media']}' tidak valid. Gunakan: " . implode(', ', $validMedia));
        }

        if (!filter_var($medsos['link'], FILTER_VALIDATE_URL)) {
          throw new Exception("Link medsos '{$medsos['link']}' tidak valid");
        }
      }
    }

    if (isset($data['galeri']) && is_array($data['galeri'])) {
      foreach ($data['galeri'] as $galeri) {
        if (empty($galeri['image_url'])) {
          throw new Exception("Image URL galeri harus diisi");
        }

        if (!filter_var($galeri['image_url'], FILTER_VALIDATE_URL)) {
          throw new Exception("Image URL '{$galeri['image_url']}' tidak valid");
        }
      }
    }
  }
}
