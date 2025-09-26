<?php

namespace App\Repositories;

use Configs\DatabaseConfig;

class PenginapanRepository
{
  private $db;

  public function __construct()
  {
    $this->db = DatabaseConfig::getInstance();
  }

  public function getTotalCount()
  {
    $stmt = $this->db->prepare("SELECT COUNT(id) as total FROM penginapan");
    $stmt->execute();
    return (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total'];
  }

  public function getAllWithFasilitas($limit, $offset)
  {
    $stmt = $this->db->prepare("
            SELECT p.*, GROUP_CONCAT(f.nama SEPARATOR ', ') as fasilitas
            FROM penginapan p
            LEFT JOIN fasilitas f ON p.id = f.penginapan_id
            GROUP BY p.id
            ORDER BY p.nama ASC
            LIMIT :limit OFFSET :offset
        ");
    $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function getByIdWithFasilitas($id)
  {
    $stmt = $this->db->prepare("
            SELECT p.*, GROUP_CONCAT(f.nama SEPARATOR ', ') as fasilitas
            FROM penginapan p
            LEFT JOIN fasilitas f ON p.id = f.penginapan_id
            WHERE p.id = :id
            GROUP BY p.id
        ");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(\PDO::FETCH_ASSOC);
  }

  public function create($data)
  {
    $stmt = $this->db->prepare("INSERT INTO penginapan (nama, tipe, harga, image_url, deskripsi, rating, maps_url) VALUES (:nama, :tipe, :harga, :image_url, :deskripsi, :rating, :maps_url)");
    $stmt->bindParam(':nama', $data['nama']);
    $stmt->bindParam(':tipe', $data['tipe']);
    $stmt->bindParam(':harga', $data['harga']);
    $stmt->bindParam(':image_url', $data['image_url']);
    $stmt->bindParam(':deskripsi', $data['deskripsi']);
    $stmt->bindParam(':rating', $data['rating']);
    $stmt->bindParam(':maps_url', $data['maps_url']);
    $stmt->execute();
    return $this->db->lastInsertId();
  }

  public function update($id, $data)
  {
    $stmt = $this->db->prepare("UPDATE penginapan SET nama = :nama, tipe = :tipe, harga = :harga, image_url = :image_url, deskripsi = :deskripsi, rating = :rating, maps_url = :maps_url WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':nama', $data['nama']);
    $stmt->bindParam(':tipe', $data['tipe']);
    $stmt->bindParam(':harga', $data['harga']);
    $stmt->bindParam(':image_url', $data['image_url']);
    $stmt->bindParam(':deskripsi', $data['deskripsi']);
    $stmt->bindParam(':rating', $data['rating']);
    $stmt->bindParam(':maps_url', $data['maps_url']);
    return $stmt->execute();
  }

  public function delete($id)
  {
    $stmt = $this->db->prepare("DELETE FROM penginapan WHERE id = :id");
    $stmt->bindParam(':id', $id);
    return $stmt->execute();
  }

  public function addFasilitas($penginapanId, $fasilitas)
  {
    $stmt = $this->db->prepare("INSERT INTO fasilitas (penginapan_id, nama) VALUES (:penginapan_id, :nama)");
    foreach ($fasilitas as $nama) {
      $stmt->bindParam(':penginapan_id', $penginapanId);
      $stmt->bindParam(':nama', $nama);
      $stmt->execute();
    }
  }

  public function deleteFasilitas($penginapanId)
  {
    $stmt = $this->db->prepare("DELETE FROM fasilitas WHERE penginapan_id = :penginapan_id");
    $stmt->bindParam(':penginapan_id', $penginapanId);
    return $stmt->execute();
  }
}
