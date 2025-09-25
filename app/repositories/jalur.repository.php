<?php

namespace App\Repositories;

use Config\DatabaseConfig; // PERBAIKAN: Gunakan namespace yang benar
use PDO;
use Exception;

class JalurRepository
{
  private PDO $db;
  
  public function __construct()
  {
    $this->db = DatabaseConfig::getInstance();
  }

  /**
   * Mengambil semua jalur dengan paginasi dan relasi
   */
  public function findAll(int $offset, int $limit): array
  {
    // Hitung total data
    $stmt = $this->db->query("SELECT COUNT(*) as total FROM jalur");
    $total = (int)$stmt->fetchColumn();

    // Query utama untuk mengambil jalur
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

    $jalurData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ambil medsos dan galeri untuk setiap jalur
    foreach ($jalurData as &$jalur) {
      $jalur['medsos'] = $this->getMedsosByJalurId($jalur['id']);
      $jalur['galeri'] = $this->getGaleriByJalurId($jalur['id']);
    }

    return ['data' => $jalurData, 'total' => $total];
  }

  /**
   * Mengambil jalur berdasarkan ID dengan relasi lengkap
   */
  public function findById(string $id): ?array
  {
    $stmt = $this->db->prepare("SELECT * FROM jalur WHERE id = ?");
    $stmt->execute([$id]);
    $jalur = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($jalur) {
      $jalur['medsos'] = $this->getMedsosByJalurId($id);
      $jalur['galeri'] = $this->getGaleriByJalurId($id);
      return $jalur;
    }

    return null;
  }

  /**
   * Membuat jalur baru dengan transaksi
   */
  public function create(array $data): array
  {
    $this->db->beginTransaction();
    try {
      // Insert jalur utama
      $jalurSql = "
        INSERT INTO jalur (nama, desa, kecamatan, kabupaten, provinsi, deskripsi)
        VALUES (:nama, :desa, :kecamatan, :kabupaten, :provinsi, :deskripsi)
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

      // PERBAIKAN: Ambil ID dari lastInsertId, bukan string
      $jalurId = $this->db->lastInsertId();
      
      // Ambil data jalur yang baru dibuat
      $jalurBaru = $this->getJalurById($jalurId);

      $medsosBaru = [];
      if (!empty($data['medsos']) && is_array($data['medsos'])) {
        $medsosSql = "INSERT INTO medsos (media, link, jalur_id) VALUES (:media, :link, :jalur_id)";
        $stmt = $this->db->prepare($medsosSql);
        
        foreach ($data['medsos'] as $item) {
          $stmt->execute([
            'media' => $item['media'],
            'link' => $item['link'],
            'jalur_id' => $jalurId
          ]);
          
          $medsosId = $this->db->lastInsertId();
          $medsosBaru[] = $this->getMedsosById($medsosId);
        }
      }

      $galeriBaru = [];
      if (!empty($data['galeri']) && is_array($data['galeri'])) {
        $galeriSql = "INSERT INTO galeri (image_url, judul, caption, jalur_id) VALUES (:image_url, :judul, :caption, :jalur_id)";
        $stmt = $this->db->prepare($galeriSql);
        
        foreach ($data['galeri'] as $item) {
          $stmt->execute([
            'image_url' => $item['image_url'],
            'judul' => $item['judul'] ?? null,
            'caption' => $item['caption'] ?? null,
            'jalur_id' => $jalurId
          ]);
          
          $galeriId = $this->db->lastInsertId();
          $galeriBaru[] = $this->getGaleriById($galeriId);
        }
      }

      $this->db->commit();

      return array_merge($jalurBaru, [
        'medsos' => $medsosBaru, 
        'galeri' => $galeriBaru
      ]);
      
    } catch (Exception $e) {
      $this->db->rollBack();
      throw new Exception("Transaksi gagal: " . $e->getMessage());
    }
  }

  /**
   * Helper methods untuk mengambil data terkait
   */
  private function getMedsosByJalurId(string $jalurId): array
  {
    $stmt = $this->db->prepare("SELECT * FROM medsos WHERE jalur_id = ? ORDER BY media");
    $stmt->execute([$jalurId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  private function getGaleriByJalurId(string $jalurId): array
  {
    $stmt = $this->db->prepare("SELECT * FROM galeri WHERE jalur_id = ? ORDER BY judul");
    $stmt->execute([$jalurId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  private function getJalurById(string $id): ?array
  {
    $stmt = $this->db->prepare("SELECT * FROM jalur WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
  }

  private function getMedsosById(string $id): ?array
  {
    $stmt = $this->db->prepare("SELECT * FROM medsos WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
  }

  private function getGaleriById(string $id): ?array
  {
    $stmt = $this->db->prepare("SELECT * FROM galeri WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
  }
}