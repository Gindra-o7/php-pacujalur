<?php

namespace App\Services;

use App\Helpers\PaginationHelper;
use App\Repositories\GaleriRepository;
use Exception;

class GaleriService
{
  private GaleriRepository $galeriRepository;

  public function __construct()
  {
    $this->galeriRepository = new GaleriRepository();
  }

  public function getAll(int $page, int $limit): array
  {
    try {
      $offset = PaginationHelper::getOffset($page, $limit);
      ['data' => $data, 'total' => $total] = $this->galeriRepository->findAll($offset, $limit);

      return PaginationHelper::buildPaginatedResponse($data, $page, $limit, $total);
    } catch (Exception $e) {
      throw new Exception("Error getting galeri data: " . $e->getMessage());
    }
  }
}
