<?php

namespace App\Services;

use App\Repositories\AcaraRepository;

class AcaraService
{
  private $acaraRepository;

  public function __construct()
  {
    $this->acaraRepository = new AcaraRepository();
  }

  public function getAll()
  {
    $acara = $this->acaraRepository->getAll();
    $terdekat = [];
    $lainnya = [];
    $now = new \DateTime();

    foreach ($acara as $item) {
      $tgl_mulai = new \DateTime($item['tgl_mulai']);
      if ($tgl_mulai >= $now) {
        $terdekat[] = $item;
      } else {
        $lainnya[] = $item;
      }
    }

    return [
      'terdekat' => $terdekat,
      'lainnya' => $lainnya
    ];
  }

  public function getById($id)
  {
    return $this->acaraRepository->getById($id);
  }

  public function create($data)
  {
    return $this->acaraRepository->create($data);
  }

  public function update($id, $data)
  {
    return $this->acaraRepository->update($id, $data);
  }

  public function delete($id)
  {
    return $this->acaraRepository->delete($id);
  }
}
