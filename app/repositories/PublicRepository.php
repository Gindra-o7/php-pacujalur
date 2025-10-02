<?php

namespace App\Repositories;

use Configs\DatabaseConfig;
use PDO;

class PublicRepository
{
  private $db;

  public function __construct()
  {
    $this->db = DatabaseConfig::getInstance();
  }

  public function getStats()
  {
    // Event Rayon (upcoming events)
    $stmt_acara = $this->db->prepare("SELECT COUNT(id) as total FROM acara WHERE tgl_mulai >= CURDATE()");
    $stmt_acara->execute();
    $event_rayon = (int)$stmt_acara->fetch(PDO::FETCH_ASSOC)['total'];

    // Kursi Tersedia (from upcoming events)
    $stmt_kursi = $this->db->prepare("
            SELECT SUM(t.total_kursi) as total 
            FROM tribun t
            JOIN acara a ON t.acara_id = a.id
            WHERE a.tgl_mulai >= CURDATE()
        ");
    $stmt_kursi->execute();
    $kursi_tersedia = (int)$stmt_kursi->fetch(PDO::FETCH_ASSOC)['total'];

    // Tim Peserta (total jalur)
    $stmt_jalur = $this->db->prepare("SELECT COUNT(id) as total FROM jalur");
    $stmt_jalur->execute();
    $tim_peserta = (int)$stmt_jalur->fetch(PDO::FETCH_ASSOC)['total'];

    // Date range from the nearest event
    $stmt_date = $this->db->prepare("
            SELECT tgl_mulai 
            FROM acara 
            WHERE tgl_mulai >= CURDATE() 
            ORDER BY tgl_mulai ASC 
            LIMIT 1
        ");
    $stmt_date->execute();
    $next_event_date = $stmt_date->fetch(PDO::FETCH_ASSOC);

    $periode = "Coming Soon";
    if ($next_event_date) {
      $date = new \DateTime($next_event_date['tgl_mulai']);
      $month1 = $date->format('M');
      $date->modify('+1 month');
      $month2 = $date->format('M');
      $year = $date->format('Y');
      $periode = "{$month1}-{$month2} {$year}";
    }


    return [
      'event_rayon' => $event_rayon,
      'kursi_tersedia' => $kursi_tersedia,
      'tim_peserta' => $tim_peserta . '+',
      'periode' => $periode,
    ];
  }

  public function getUpcomingAcara($limit = 5)
  {
    $stmt = $this->db->prepare("
            SELECT id, nama, lokasi, image_url, tgl_mulai 
            FROM acara 
            WHERE tgl_mulai >= CURDATE() 
            ORDER BY tgl_mulai ASC 
            LIMIT :limit
        ");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getRandomGaleri($limit = 6)
  {
    $stmt = $this->db->prepare("
            SELECT id, image_url, judul 
            FROM galeri 
            ORDER BY RAND() 
            LIMIT :limit
        ");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getTopPenginapan($limit = 3)
  {
    $stmt = $this->db->prepare("
            SELECT id, nama, tipe, harga, image_url, rating 
            FROM penginapan 
            ORDER BY rating DESC 
            LIMIT :limit
        ");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getRandomJalurImages($limit = 4)
  {
    $stmt = $this->db->prepare("
            SELECT g.id, g.image_url, g.judul, j.nama as nama_jalur 
            FROM galeri g
            JOIN jalur j ON g.jalur_id = j.id
            ORDER BY RAND() 
            LIMIT :limit
        ");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
