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

  public function getAll()
  {
    try {
      ['page' => $page, 'limit' => $limit] = PaginationHelper::parsePaginationQuery();

      $result = $this->jalurService->getAll($page, $limit);

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
        ResponseHelper::badRequest('Data JSON tidak valid');
        return;
      }

      $result = $this->jalurService->create($data);

      echo json_encode(ResponseHelper::created($result, "Data jalur berhasil ditambahkan"), JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
      echo json_encode(ResponseHelper::badRequest($e->getMessage()));
    }
  }
}
