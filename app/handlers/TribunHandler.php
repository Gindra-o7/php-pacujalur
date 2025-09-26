<?php

namespace App\Handlers;

use App\Services\TribunService;
use App\Helpers\ResponseHelper;

class TribunHandler
{
  private $tribunService;

  public function __construct()
  {
    $this->tribunService = new TribunService();
  }

  public function getAllByAcaraId($acara_id)
  {
    $tribun = $this->tribunService->getAllByAcaraId($acara_id);
    echo json_encode(ResponseHelper::success($tribun), JSON_PRETTY_PRINT);
  }

  public function getTribunById($id)
  {
    $tribun = $this->tribunService->getById($id);
    if ($tribun) {
      echo json_encode(ResponseHelper::success($tribun), JSON_PRETTY_PRINT);
    } else {
      echo json_encode(ResponseHelper::error($id), JSON_PRETTY_PRINT);
    }
  }

  public function create()
  {
    $data = json_decode(file_get_contents('php://input'), true);
    if ($this->tribunService->create($data)) {
      echo json_encode(ResponseHelper::success($data), JSON_PRETTY_PRINT);
    } else {
      echo json_encode(ResponseHelper::error('Failed to create tribun'), JSON_PRETTY_PRINT);
    }
  }

  public function update($id)
  {
    $data = json_decode(file_get_contents('php://input'), true);
    if ($this->tribunService->update($id, $data)) {
      echo json_encode(ResponseHelper::success($data), JSON_PRETTY_PRINT);
    } else {
      echo json_encode(ResponseHelper::error('Failed to update tribun'), JSON_PRETTY_PRINT);
    }
  }

  public function delete($id)
  {
    if ($this->tribunService->delete($id)) {
      echo json_encode(ResponseHelper::success(['message' => 'Tribun deleted successfully']), JSON_PRETTY_PRINT);
    } else {
      echo json_encode(ResponseHelper::error('Failed to delete tribun'), JSON_PRETTY_PRINT);
    }
  }
}
