<?php

namespace App\Services;

use App\Repositories\PublicRepository;
use App\Helpers\UploadHelper;

class PublicService
{
  private $publicRepository;

  public function __construct()
  {
    $this->publicRepository = new PublicRepository();
  }

  public function getPublicData()
  {
    $stats = $this->publicRepository->getStats();
    $upcoming_acara = $this->publicRepository->getUpcomingAcara(5);
    $random_galeri = $this->publicRepository->getRandomGaleri(6);
    $top_penginapan = $this->publicRepository->getTopPenginapan(3);
    $random_jalur_images = $this->publicRepository->getRandomJalurImages(4);

    // Convert image URLs
    foreach ($upcoming_acara as &$item) {
      if (!empty($item['image_url']) && !filter_var($item['image_url'], FILTER_VALIDATE_URL)) {
        $item['image_url'] = UploadHelper::getImageUrl($item['image_url']);
      }
    }

    foreach ($random_galeri as &$item) {
      if (!empty($item['image_url']) && !filter_var($item['image_url'], FILTER_VALIDATE_URL)) {
        $item['image_url'] = UploadHelper::getImageUrl($item['image_url']);
      }
    }

    foreach ($top_penginapan as &$item) {
      if (!empty($item['image_url']) && !filter_var($item['image_url'], FILTER_VALIDATE_URL)) {
        $item['image_url'] = UploadHelper::getImageUrl($item['image_url']);
      }
    }

    foreach ($random_jalur_images as &$item) {
      if (!empty($item['image_url']) && !filter_var($item['image_url'], FILTER_VALIDATE_URL)) {
        $item['image_url'] = UploadHelper::getImageUrl($item['image_url']);
      }
    }

    return [
      'stats' => $stats,
      'jadwal_terdekat' => $upcoming_acara,
      'galeri_acak' => $random_galeri,
      'penginapan_terbaik' => $top_penginapan,
      'foto_pacu_jalur' => $random_jalur_images,
    ];
  }
}
