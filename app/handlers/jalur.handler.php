<?php

namespace App\Handlers;

use App\Helpers\PaginationHelper;
use App\Helpers\ResponseHelper;
use App\Services\JalurService;
use Exception;

class JalurHandler
{
  private JalurService $jalurService;

  public function __construct()
  {
    $this->jalurService = new JalurService();
  }

  /**
   * Meng-handle permintaan untuk mendapatkan semua data jalur dengan paginasi.
   */
  public function getAll()
  {
    try {
      // Ambil parameter paginasi dari query string (?page=...&limit=...)
      ['page' => $page, 'limit' => $limit] = PaginationHelper::parsePaginationQuery();

      $result = $this->jalurService->getAll($page, $limit);

      ResponseHelper::success($result, "Data jalur berhasil didapatkan");
    } catch (Exception $e) {
      ResponseHelper::error("Gagal mendapatkan data jalur: " . $e->getMessage());
    }
  }

  /**
   * Meng-handle permintaan untuk membuat data jalur baru.
   */
  public function create()
  {
    try {
      // Ambil data JSON dari body permintaan
      $data = json_decode(file_get_contents('php://input'), true);

      if (json_last_error() !== JSON_ERROR_NONE) {
        ResponseHelper::badRequest('Data JSON tidak valid');
        return;
      }

      $result = $this->jalurService->create($data);

      ResponseHelper::created($result, "Data jalur berhasil ditambahkan");
    } catch (Exception $e) {
      // Jika ada error validasi atau lainnya dari service
      ResponseHelper::badRequest($e->getMessage());
    }
  }
}
