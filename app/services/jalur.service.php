<?php

namespace App\Services;

use App\Helpers\PaginationHelper;
use App\Repositories\JalurRepository;
use Exception;

class JalurService
{
  private JalurRepository $jalurRepository;

  public function __construct()
  {
    $this->jalurRepository = new JalurRepository();
  }

  /**
   * Mengambil semua data jalur dengan paginasi.
   */
  public function getAll(int $page, int $limit): array
  {
    $offset = PaginationHelper::getOffset($page, $limit);
    ['data' => $data, 'total' => $total] = $this->jalurRepository->findAll($offset, $limit);

    return PaginationHelper::buildPaginatedResponse($data, $page, $limit, $total);
  }

  /**
   * Membuat data jalur baru beserta medsos dan galerinya.
   */
  public function create(array $data): array
  {
    // Validasi data yang masuk
    if (
      empty($data['nama']) ||
      empty($data['desa']) ||
      empty($data['kecamatan']) ||
      empty($data['kabupaten']) ||
      empty($data['provinsi'])
    ) {
      throw new Exception("Alamat lengkap (nama, desa, kecamatan, kabupaten, provinsi) harus diisi");
    }

    return $this->jalurRepository->create($data);
  }
}
