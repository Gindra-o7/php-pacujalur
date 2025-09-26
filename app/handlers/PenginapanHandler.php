<?php

namespace App\Handlers;

use App\Services\PenginapanService;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidationHelper;

class PenginapanHandler
{
  private $penginapanService;

  public function __construct()
  {
    $this->penginapanService = new PenginapanService();
  }

  public function getAll()
  {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

    $result = $this->penginapanService->getAll($page, $limit);

    echo json_encode(ResponseHelper::success($result), JSON_PRETTY_PRINT);
  }

  public function getById($id)
  {
    $result = $this->penginapanService->getById($id);
    if (!$result) {
      echo json_encode(ResponseHelper::error('Penginapan not found', 404), JSON_PRETTY_PRINT);
      return;
    }
    echo json_encode(ResponseHelper::success($result), JSON_PRETTY_PRINT);
  }

  public function create()
  {
    $data = json_decode(file_get_contents('php://input'), true);
    $this->validateData($data);
    $result = $this->penginapanService->create($data);

    $newPenginapan = $this->penginapanService->getById($result['id']);
    echo json_encode(ResponseHelper::success($newPenginapan), JSON_PRETTY_PRINT);
  }

  public function update($id)
  {
    $data = json_decode(file_get_contents('php://input'), true);
    $this->validateData($data);

    $existing = $this->penginapanService->getById($id);
    if (!$existing) {
      echo json_encode(ResponseHelper::error('Penginapan not found', 404), JSON_PRETTY_PRINT);
      return;
    }

    $this->penginapanService->update($id, $data);
    $updatedPenginapan = $this->penginapanService->getById($id);
    echo json_encode(ResponseHelper::success($updatedPenginapan), JSON_PRETTY_PRINT);
  }

  public function delete($id)
  {
    $existing = $this->penginapanService->getById($id);
    if (!$existing) {
      echo json_encode(ResponseHelper::error('Penginapan not found', 404), JSON_PRETTY_PRINT);
      return;
    }

    $this->penginapanService->delete($id);
    echo json_encode(ResponseHelper::success(['message' => 'Penginapan deleted successfully.']), JSON_PRETTY_PRINT);
  }

  private function validateData($data)
  {
    $rules = [
      'nama' => 'required|max_length[255]',
      'tipe' => 'required|max_length[100]',
      'harga' => 'max_length[100]',
      'fasilitas' => 'is_array'
    ];

    ValidationHelper::validateRequired($data, ['nama', 'tipe', 'harga']);
    ValidationHelper::validateLength($data['nama'], 1, 255, 'Nama');
    ValidationHelper::validateLength($data['tipe'], 1, 100, 'Tipe');
    ValidationHelper::validateLength($data['harga'], 1, 100, 'Harga');

    if (!empty($data['image_url'])) {
      ValidationHelper::validateUrl($data['image_url']);
    }
    if (!empty($data['deskripsi'])) {
      ValidationHelper::validateLength($data['deskripsi'], 0, 2000, 'Deskripsi');
    }
    if (!empty($data['maps_url'])) {
      ValidationHelper::validateUrl($data['maps_url']);
    }
  }
}
