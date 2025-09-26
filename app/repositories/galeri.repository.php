<?php

namespace App\Repositories;

use Configs\DatabaseConfig; // PERBAIKAN: Gunakan namespace yang benar
use PDO;

class GaleriRepository
{
  private PDO $db;

  public function __construct()
  {
    $this->db = DatabaseConfig::getInstance();
  }

  public function findAll(int $offset, int $limit): array
  {
    $stmt = $this->db->query("SELECT COUNT(*) as total FROM galeri");
    $total = (int)$stmt->fetchColumn();

    $query = "
            SELECT id, image_url, judul, caption, jalur_id
            FROM galeri
            ORDER BY judul
            LIMIT :limit OFFSET :offset
        ";

    $stmt = $this->db->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $galeriData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return ['data' => $galeriData, 'total' => $total];
  }
}
