<?php

namespace App\Handlers;

use App\Services\PublicService;
use App\Helpers\ResponseHelper;
use Exception;

class PublicHandler
{
    private $publicService;

    public function __construct()
    {
        $this->publicService = new PublicService();
    }

    public function get()
    {
        try {
            $result = $this->publicService->getPublicData();
            echo json_encode(ResponseHelper::success($result, "Data landing page berhasil didapatkan"), JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(ResponseHelper::error("Gagal mendapatkan data landing page: " . $e->getMessage()));
        }
    }
}