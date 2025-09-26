<?php

namespace App\Services;

use App\Repositories\TribunRepository;

class TribunService
{
  private $tribunRepository;

  public function __construct()
  {
    $this->tribunRepository = new TribunRepository();
  }

  public function getAllByAcaraId($acara_id)
  {
    return $this->tribunRepository->getAllByAcaraId($acara_id);
  }

  public function getById($id)
  {
    return $this->tribunRepository->getById($id);
  }

  public function create($data)
  {
    // Add any validation or business logic here
    return $this->tribunRepository->create($data);
  }

  public function update($id, $data)
  {
    // Add any validation or business logic here
    return $this->tribunRepository->update($id, $data);
  }

  public function delete($id)
  {
    // Add any validation or business logic here
    return $this->tribunRepository->delete($id);
  }
}
