<?php
namespace App\Helpers;

use DateTime;

class PublicHelper
{

  public function formatNumber($number)
  {
    if ($number >= 1000000) {
      return number_format($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
      return number_format($number / 1000, 0) . 'K';
    }
    return number_format($number);
  }

  public function formatEventDate($startDate, $endDate)
  {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);

    $months = [
      1 => 'Jan',
      2 => 'Feb',
      3 => 'Mar',
      4 => 'Apr',
      5 => 'Mei',
      6 => 'Jun',
      7 => 'Jul',
      8 => 'Agu',
      9 => 'Sep',
      10 => 'Okt',
      11 => 'Nov',
      12 => 'Des'
    ];

    if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
      // Same day
      return $start->format('d') . ' ' . $months[(int)$start->format('n')] . ' ' . $start->format('Y');
    } elseif ($start->format('Y-m') === $end->format('Y-m')) {
      // Same month
      return $start->format('d') . '-' . $end->format('d') . ' ' . $months[(int)$start->format('n')] . ' ' . $start->format('Y');
    } else {
      // Different months
      return $start->format('d') . ' ' . $months[(int)$start->format('n')] . ' - ' .
        $end->format('d') . ' ' . $months[(int)$end->format('n')] . ' ' . $end->format('Y');
    }
  }

  public function formatRating($rating)
  {
    $stars = '';
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;

    // Full stars
    for ($i = 0; $i < $fullStars; $i++) {
      $stars .= '★';
    }

    // Half star
    if ($hasHalfStar) {
      $stars .= '☆';
    }

    // Empty stars
    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
    for ($i = 0; $i < $emptyStars; $i++) {
      $stars .= '☆';
    }

    return [
      'stars' => $stars,
      'numeric' => number_format($rating, 1)
    ];
  }

  public function truncateText($text, $length = 100)
  {
    if (strlen($text) <= $length) {
      return $text;
    }
    return substr($text, 0, $length) . '...';
  }

  public function getMonthRangeLabel($activeMonths)
  {
    if ($activeMonths <= 2) {
      return 'Bulan Aktif';
    } else {
      return 'Periode';
    }
  }

  public function getMonthRangeText($activeMonths)
  {
    $months = [
      'Jan',
      'Feb',
      'Mar',
      'Apr',
      'Mei',
      'Jun',
      'Jul',
      'Agu',
      'Sep',
      'Okt',
      'Nov',
      'Des'
    ];

    if ($activeMonths <= 2) {
      return $activeMonths . ' Bulan';
    } else {
      // For multiple months, show as range (example: Jun-Jul)
      $currentMonth = (int)date('n');
      $startMonth = max(1, $currentMonth - floor($activeMonths / 2));
      $endMonth = min(12, $startMonth + $activeMonths - 1);

      return $months[$startMonth - 1] . '-' . $months[$endMonth - 1];
    }
  }

  public function formatCurrency($amount)
  {
    return 'Rp ' . number_format($amount, 0, ',', '.');
  }

  public function getImageUrl($imagePath)
  {
    if (empty($imagePath)) {
      return '/assets/images/placeholder.jpg';
    }

    // Check if it's already a full URL
    if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
      return $imagePath;
    }

    // If it's a relative path, prepend base URL
    return '/uploads/' . ltrim($imagePath, '/');
  }

  public function timeAgo($datetime)
  {
    $time = time() - strtotime($datetime);

    if ($time < 60) return 'baru saja';
    if ($time < 3600) return floor($time / 60) . ' menit lalu';
    if ($time < 86400) return floor($time / 3600) . ' jam lalu';
    if ($time < 2592000) return floor($time / 86400) . ' hari lalu';
    if ($time < 31104000) return floor($time / 2592000) . ' bulan lalu';
    return floor($time / 31104000) . ' tahun lalu';
  }

  public function generateSlug($text)
  {
    // Convert to lowercase
    $text = strtolower($text);

    // Remove special characters and replace with dash
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);

    // Replace spaces and multiple dashes with single dash
    $text = preg_replace('/[\s-]+/', '-', $text);

    // Trim dashes from beginning and end
    return trim($text, '-');
  }

  public function isValidEmail($email)
  {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
  }

  public function sanitizeInput($input)
  {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
  }
}
