<?php

namespace App\Handlers;

use App\Helpers\PaginationHelper;
use App\Helpers\ResponseHelper;
use App\Services\GaleriService;
use Exception;

class GaleriHandler
{
  private GaleriService $galeriService;

  public function __construct()
  {
    $this->galeriService = new GaleriService();
  }

  public function getAll()
  {
    try {
      ['page' => $page, 'limit' => $limit] = PaginationHelper::parsePaginationQuery();

      $result = $this->galeriService->getAll($page, $limit);

      echo json_encode(ResponseHelper::success($result, "Data galeri berhasil didapatkan"), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
      echo json_encode(ResponseHelper::error("Gagal mendapatkan data galeri: " . $e->getMessage()));
    }
  }
}
