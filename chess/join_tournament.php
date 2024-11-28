<?php
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Подключаем необходимые файлы
require_once __DIR__ . '/config/database.php';

// Получаем ID турнира
$tournament_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Проверяем существование турнира и возможность присоединения
    $query = "SELECT t.*, 
              COUNT(DISTINCT p.user_id) as participants_count,
              (SELECT COUNT(*) FROM tournament_participants WHERE tournament_id = t.id AND user_id = :user_id) as is_participant
              FROM tournaments t 
              LEFT JOIN tournament_participants p ON t.id = p.tournament_id 
              WHERE t.id = :tournament_id
              GROUP BY t.id";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'tournament_id' => $tournament_id,
        'user_id' => $_SESSION['user_id']
    ]);
    $tournament = $stmt->fetch(PDO::FETCH_ASSOC);

    // Проверяем условия для присоединения
    if (!$tournament) {
        throw new Exception('Турнир не найден');
    }

    if ($tournament['is_participant']) {
        throw new Exception('Вы уже участвуете в этом турнире');
    }

    if ($tournament['status'] !== 'upcoming') {
        throw new Exception('Регистрация на турнир закрыта');
    }

    if ($tournament['participants_count'] >= $tournament['max_participants']) {
        throw new Exception('Достигнуто максимальное количество участников');
    }

    // Добавляем пользователя в список участников
    $query = "INSERT INTO tournament_participants (tournament_id, user_id, registration_date) 
              VALUES (:tournament_id, :user_id, NOW())";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'tournament_id' => $tournament_id,
        'user_id' => $_SESSION['user_id']
    ]);

    // Перенаправляем на страницу турнира
    header('Location: tournament_details.php?id=' . $tournament_id);
    exit;

} catch (Exception $e) {
    // В случае ошибки перенаправляем на страницу турнира с сообщением об ошибке
    header('Location: tournament_details.php?id=' . $tournament_id . '&error=' . urlencode($e->getMessage()));
    exit;
}
