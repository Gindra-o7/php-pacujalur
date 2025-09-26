<?php

namespace App\Handlers;

use App\Services\AcaraService;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidationHelper;

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
    echo json_encode(ResponseHelper::success($result), JSON_UNESCAPED_UNICODE);
  }

  public function getById($id)
  {
    $result = $this->acaraService->getById($id);
    echo json_encode(ResponseHelper::success($result), JSON_UNESCAPED_UNICODE);
  }

  public function create()
  {
    $data = json_decode(file_get_contents('php://input'), true);
    $validatedData = $this->validateAcaraData($data);
    $result = $this->acaraService->create($validatedData);
    echo json_encode(ResponseHelper::created($result, "Acara berhasil ditambahkan"), JSON_UNESCAPED_UNICODE);
  }

  public function update($id)
  {
    $data = json_decode(file_get_contents('php://input'), true);
    $validatedData = $this->validateAcaraData($data);
    $result = $this->acaraService->update($id, $validatedData);
    echo json_encode(ResponseHelper::success($result, "Acara berhasil diperbarui"), JSON_UNESCAPED_UNICODE);
  }

  public function delete($id)
  {
    $result = $this->acaraService->delete($id); // Assuming delete returns a success indicator
    echo json_encode(ResponseHelper::success($result, "Acara berhasil dihapus"), JSON_UNESCAPED_UNICODE);
  }

  private function validateAcaraData($data)
  {
    $rules = [
      'nama' => 'required|max_length[255]',
      'lokasi' => 'required|max_length[255]',
      'tgl_mulai' => 'required|valid_date',
      'tgl_selesai' => 'required|valid_date'
    ];

    ValidationHelper::validateRequired($data, ['nama', 'lokasi', 'tgl_mulai', 'tgl_selesai']);
    ValidationHelper::validateLength($data['nama'], 1, 255, 'Nama');
    ValidationHelper::validateLength($data['lokasi'], 1, 255, 'Lokasi');
    ValidationHelper::validateDate($data['tgl_mulai'], 'Tanggal Mulai');
    ValidationHelper::validateDate($data['tgl_selesai'], 'Tanggal Selesai');

    if (!empty($data['image_url'])) {
      ValidationHelper::validateUrl($data['image_url']);
    }
    if (!empty($data['deskripsi'])) {
      ValidationHelper::validateLength($data['deskripsi'], 0, 2000, 'Deskripsi');
    }
    return $data;
  }
}
