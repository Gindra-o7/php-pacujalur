<?php

namespace App\Services;

use App\Repositories\PenginapanRepository;
use App\Helpers\PaginationHelper;

class PenginapanService
{
  private $penginapanRepository;

  public function __construct()
  {
    $this->penginapanRepository = new PenginapanRepository();
  }

  public function getAll($page, $limit)
  {
    $totalItems = $this->penginapanRepository->getTotalCount();
    $pagination = PaginationHelper::buildPaginatedResponse([], $page, $limit, $totalItems)['pagination'];

    $offset = ($page - 1) * $limit;
    $data = $this->penginapanRepository->getAllWithFasilitas($limit, $offset);

    return [
      'data' => $data,
      'pagination' => $pagination,
    ];
  }

  public function getById($id)
  {
    return $this->penginapanRepository->getByIdWithFasilitas($id);
  }

  public function create($data)
  {
    $fasilitas = $data['fasilitas'] ?? [];
    unset($data['fasilitas']);

    $penginapanId = $this->penginapanRepository->create($data);

    if ($penginapanId && !empty($fasilitas)) {
      $this->penginapanRepository->addFasilitas($penginapanId, $fasilitas);
    }

    return ['id' => $penginapanId];
  }

  public function update($id, $data)
  {
    $fasilitas = $data['fasilitas'] ?? [];
    unset($data['fasilitas']);

    $this->penginapanRepository->update($id, $data);

    $this->penginapanRepository->deleteFasilitas($id);
    if (!empty($fasilitas)) {
      $this->penginapanRepository->addFasilitas($id, $fasilitas);
    }

    return ['success' => true];
  }

  public function delete($id)
  {
    return $this->penginapanRepository->delete($id);
  }
}
