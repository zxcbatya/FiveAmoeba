<?php

/**
 * Helper function to sanitize input data
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Helper function to validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Helper function to check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Helper function to redirect
 */
function redirect($location) {
    header("Location: $location");
    exit;
}

/**
 * Helper function to generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Helper function to validate CSRF token
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

/**
 * Helper function to format date
 */
function formatDate($date) {
    return date('d.m.Y', strtotime($date));
}

/**
 * Helper function to format date with time
 */
function formatDateTime($date) {
    return date('d.m.Y H:i', strtotime($date));
}

/**
 * Helper function to format date and time
 */
function formatDateTimeNew($datetime) {
    if (!$datetime) return 'Не указано';
    
    $date = new DateTime($datetime);
    return $date->format('d.m.Y H:i');
}

/**
 * Helper function to check if string contains only cyrillic characters
 */
function isCyrillic($string) {
    return preg_match('/^[\p{Cyrillic}\s]+$/u', $string);
}

/**
 * Helper function to get user rating color
 */
function getRatingColor($rating) {
    if ($rating >= 2400) return '#FF4081'; // Pink
    if ($rating >= 2200) return '#FF5722'; // Deep Orange
    if ($rating >= 2000) return '#FF9800'; // Orange
    if ($rating >= 1800) return '#FFC107'; // Amber
    if ($rating >= 1600) return '#FFEB3B'; // Yellow
    if ($rating >= 1400) return '#CDDC39'; // Lime
    return '#8BC34A'; // Light Green
}

/**
 * Helper function to get achievement icon
 */
function getAchievementIcon($type) {
    switch ($type) {
        case 'tournament_win': return 'trophy';
        case 'rating_milestone': return 'star';
        case 'games_played': return 'chess-board';
        case 'streak': return 'fire';
        case 'perfect_score': return 'crown';
        default: return 'award';
    }
}

/**
 * Helper function to format rating change
 */
function formatRatingChange($change) {
    if ($change > 0) {
        return '<span class="rating-up">+' . $change . '</span>';
    } elseif ($change < 0) {
        return '<span class="rating-down">' . $change . '</span>';
    }
    return '<span class="rating-neutral">0</span>';
}

/**
 * Helper function to get user level based on rating
 */
function getUserLevel($rating) {
    if ($rating >= 2400) return 'Гроссмейстер';
    if ($rating >= 2200) return 'Международный мастер';
    if ($rating >= 2000) return 'Мастер ФИДЕ';
    if ($rating >= 1800) return 'Кандидат в мастера';
    if ($rating >= 1600) return 'Перворазрядник';
    if ($rating >= 1400) return 'Второразрядник';
    return 'Новичок';
}

/**
 * Helper function to calculate win percentage
 */
function calculateWinPercentage($wins, $total) {
    if ($total == 0) return 0;
    return round(($wins / $total) * 100, 1);
}

/**
 * Helper function to get tournament type label
 */
function getTournamentTypeLabel($type) {
    switch ($type) {
        case 'blitz': return 'Блиц';
        case 'rapid': return 'Рапид';
        case 'classical': return 'Классика';
        case 'bullet': return 'Пуля';
        default: return 'Стандарт';
    }
}

/**
 * Helper function to get tournament status label
 */
function getTournamentStatus($status) {
    $statuses = [
        'upcoming' => 'Предстоящий',
        'ongoing' => 'В процессе',
        'completed' => 'Завершен'
    ];
    
    return $statuses[$status] ?? 'Неизвестно';
}

/**
 * Helper function to get profile image
 */
function getProfileImage($avatar) {
    if (!$avatar || !file_exists(__DIR__ . '/../uploads/avatars/' . $avatar)) {
        return 'images/default-avatar.png';
    }
    return 'uploads/avatars/' . $avatar;
}
