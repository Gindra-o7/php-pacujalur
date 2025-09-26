<?php

namespace App\Repositories;

use Configs\DatabaseConfig;

class TribunRepository
{
  private $db;

  public function __construct()
  {
    $this->db = DatabaseConfig::getInstance();
  }

  public function getAllByAcaraId($acara_id)
  {
    $stmt = $this->db->prepare("SELECT * FROM tribun WHERE acara_id = ?");
    $stmt->execute([$acara_id]);
    return $stmt->fetchAll();
  }

  public function getById($id)
  {
    $stmt = $this->db->prepare("SELECT * FROM tribun WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
  }

  public function create($data)
  {
    $stmt = $this->db->prepare("INSERT INTO tribun (acara_id, nama_penyedia, kontak_penyedia, nama_tribun, kategori, harga_per_orang, total_kursi, deskripsi) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([
      $data['acara_id'],
      $data['nama_penyedia'],
      $data['kontak_penyedia'],
      $data['nama_tribun'],
      $data['kategori'],
      $data['harga_per_orang'],
      $data['total_kursi'],
      $data['deskripsi']
    ]);
  }

  public function update($id, $data)
  {
    $stmt = $this->db->prepare("UPDATE tribun SET nama_penyedia = ?, kontak_penyedia = ?, nama_tribun = ?, kategori = ?, harga_per_orang = ?, total_kursi = ?, deskripsi = ? WHERE id = ?");
    return $stmt->execute([
      $data['nama_penyedia'],
      $data['kontak_penyedia'],
      $data['nama_tribun'],
      $data['kategori'],
      $data['harga_per_orang'],
      $data['total_kursi'],
      $data['deskripsi'],
      $id
    ]);
  }

  public function delete($id)
  {
    $stmt = $this->db->prepare("DELETE FROM tribun WHERE id = ?");
    return $stmt->execute([$id]);
  }
}