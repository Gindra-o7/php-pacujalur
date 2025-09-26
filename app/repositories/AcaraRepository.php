<?php

namespace App\Repositories;

use Configs\DatabaseConfig;

class AcaraRepository
{
  private $db;

  public function __construct()
  {
    $this->db = DatabaseConfig::getInstance();
  }

  public function getAll()
  {
    $stmt = $this->db->prepare("SELECT * FROM acara ORDER BY tgl_mulai ASC");
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function getById($id)
  {
    $stmt = $this->db->prepare("SELECT * FROM acara WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(\PDO::FETCH_ASSOC);
  }

  public function create($data)
  {
    $stmt = $this->db->prepare("INSERT INTO acara (nama, lokasi, image_url, deskripsi, tgl_mulai, tgl_selesai) VALUES (:nama, :lokasi, :image_url, :deskripsi, :tgl_mulai, :tgl_selesai)");
    $stmt->bindParam(':nama', $data['nama']);
    $stmt->bindParam(':lokasi', $data['lokasi']);
    $stmt->bindParam(':image_url', $data['image_url']);
    $stmt->bindParam(':deskripsi', $data['deskripsi']);
    $stmt->bindParam(':tgl_mulai', $data['tgl_mulai']);
    $stmt->bindParam(':tgl_selesai', $data['tgl_selesai']);
    $stmt->execute();
    return ['id' => $this->db->lastInsertId()];
  }

  public function update($id, $data)
  {
    $stmt = $this->db->prepare("UPDATE acara SET nama = :nama, lokasi = :lokasi, image_url = :image_url, deskripsi = :deskripsi, tgl_mulai = :tgl_mulai, tgl_selesai = :tgl_selesai WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':nama', $data['nama']);
    $stmt->bindParam(':lokasi', $data['lokasi']);
    $stmt->bindParam(':image_url', $data['image_url']);
    $stmt->bindParam(':deskripsi', $data['deskripsi']);
    $stmt->bindParam(':tgl_mulai', $data['tgl_mulai']);
    $stmt->bindParam(':tgl_selesai', $data['tgl_selesai']);
    $stmt->execute();
    return ['success' => true];
  }

  public function delete($id)
  {
    $stmt = $this->db->prepare("DELETE FROM acara WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return ['success' => true];
  }
}
