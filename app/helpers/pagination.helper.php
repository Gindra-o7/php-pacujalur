<?php

namespace App\Helpers;

class PaginationHelper
{
  public static function getOffset(int $page, int $limit): int
  {
    return ($page - 1) * $limit;
  }

  public static function buildPaginatedResponse(array $data, int $page, int $limit, int $total): array
  {
    return [
      'data' => $data,
      'pagination' => [
        'page' => $page,
        'limit' => $limit,
        'total' => $total,
        'totalPages' => ceil($total / $limit),
      ],
    ];
  }

  public static function parsePaginationQuery(): array
  {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 10;
    return ['page' => $page, 'limit' => $limit];
  }
}
