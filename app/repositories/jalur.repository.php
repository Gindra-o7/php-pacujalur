<?php

namespace App\Repositories;

use config\DatabaseConfig;
use PDO;
use Exception;

class JalurRepository
{
  private PDO $db;
  public function __construct()
  {
    $this->db = DatabaseConfig::getInstance();
  }
  public function findAll(int $offset, int $limit): array
  {
    $stmt = $this->db->query("SELECT COUNT(*) as total FROM jalur");
    $total = (int)$stmt->fetchColumn();

    $query = "
            SELECT id, nama, desa, kecamatan, kabupaten, provinsi, deskripsi
            FROM jalur
            ORDER BY nama
            LIMIT :limit OFFSET :offset
        ";
    $stmt = $this->db->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return ['data' => $data, 'total' => $total];
  }

  public function create(array $data): array
  {
    $this->db->beginTransaction();
    try {
      $jalurSql = "
                INSERT INTO jalur (nama, desa, kecamatan, kabupaten, provinsi, deskripsi)
                VALUES (:nama, :desa, :kecamatan, :kabupaten, :provinsi, :deskripsi);
            ";
      $stmt = $this->db->prepare($jalurSql);
      $stmt->execute([
        'nama' => $data['nama'],
        'desa' => $data['desa'],
        'kecamatan' => $data['kecamatan'],
        'kabupaten' => $data['kabupaten'],
        'provinsi' => $data['provinsi'],
        'deskripsi' => $data['deskripsi'] ?? null
      ]);
      $jalurId = $this->db->lastInsertId();

      $jalurBaru = $this->findById($jalurId);

      $medsosBaru = [];
      if (!empty($data['medsos']) && is_array($data['medsos'])) {
        $medsosSql = "INSERT INTO medsos (media, link, jalur_id) VALUES (:media, :link, :jalur_id);";
        $stmt = $this->db->prepare($medsosSql);
        foreach ($data['medsos'] as $item) {
          $stmt->execute([
            'media' => $item['media'],
            'link' => $item['link'],
            'jalur_id' => $jalurId
          ]);
          $medsosId = $this->db->lastInsertId();
          $medsosBaru[] = $this->findMedsosById($medsosId);
        }
      }

      $galeriBaru = [];
      if (!empty($data['galeri']) && is_array($data['galeri'])) {
        $galeriSql = "INSERT INTO galeri (image_url, judul, caption, jalur_id) VALUES (:image_url, :judul, :caption, :jalur_id);";
        $stmt = $this->db->prepare($galeriSql);
        foreach ($data['galeri'] as $item) {
          $stmt->execute([
            'image_url' => $item['image_url'],
            'judul' => $item['judul'] ?? null,
            'caption' => $item['caption'] ?? null,
            'jalur_id' => $jalurId
          ]);
          $galeriId = $this->db->lastInsertId();
          $galeriBaru[] = $this->findGaleriById($galeriId);
        }
      }

      $this->db->commit();

      return array_merge($jalurBaru, ['medsos' => $medsosBaru, 'galeri' => $galeriBaru]);
    } catch (Exception $e) {
      $this->db->rollBack();
      throw new Exception("Transaksi gagal: " . $e->getMessage());
    }
  }

  private function findById(string $id): ?array
  {
    $stmt = $this->db->prepare("SELECT * FROM jalur WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
  }

  private function findMedsosById(string $id): ?array
  {
    $stmt = $this->db->prepare("SELECT * FROM medsos WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
  }

  private function findGaleriById(string $id): ?array
  {
    $stmt = $this->db->prepare("SELECT * FROM galeri WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
  }
}
