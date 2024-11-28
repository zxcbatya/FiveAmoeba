<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');
// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Получение текущих настроек уведомлений
try {
    $query = "SELECT 
                email_notifications,
                tournament_notifications,
                game_invites,
                news_notifications
              FROM user_notifications 
              WHERE user_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetch(PDO::FETCH_ASSOC);

    // Если настройки не найдены, создаем их
    if (!$notifications) {
        $insert_query = "INSERT INTO user_notifications 
                        (user_id, email_notifications, tournament_notifications, game_invites, news_notifications)
                        VALUES (?, 1, 1, 1, 1)";
        $stmt = $pdo->prepare($insert_query);
        $stmt->execute([$user_id]);
        
        $notifications = [
            'email_notifications' => 1,
            'tournament_notifications' => 1,
            'game_invites' => 1,
            'news_notifications' => 1
        ];
    }
} catch (PDOException $e) {
    // Создаем таблицу, если она не существует
    $create_table = "CREATE TABLE IF NOT EXISTS user_notifications (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        email_notifications TINYINT(1) DEFAULT 1,
        tournament_notifications TINYINT(1) DEFAULT 1,
        game_invites TINYINT(1) DEFAULT 1,
        news_notifications TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        UNIQUE KEY unique_user (user_id)
    )";
    $pdo->exec($create_table);
    
    // Устанавливаем значения по умолчанию
    $notifications = [
        'email_notifications' => 1,
        'tournament_notifications' => 1,
        'game_invites' => 1,
        'news_notifications' => 1
    ];
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $tournament_notifications = isset($_POST['tournament_notifications']) ? 1 : 0;
    $game_invites = isset($_POST['game_invites']) ? 1 : 0;
    $news_notifications = isset($_POST['news_notifications']) ? 1 : 0;

    try {
        $update_query = "INSERT INTO user_notifications 
                        (user_id, email_notifications, tournament_notifications, game_invites, news_notifications)
                        VALUES (?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                        email_notifications = VALUES(email_notifications),
                        tournament_notifications = VALUES(tournament_notifications),
                        game_invites = VALUES(game_invites),
                        news_notifications = VALUES(news_notifications)";
        
        $stmt = $pdo->prepare($update_query);
        $stmt->execute([
            $user_id,
            $email_notifications,
            $tournament_notifications,
            $game_invites,
            $news_notifications
        ]);

        $notifications = [
            'email_notifications' => $email_notifications,
            'tournament_notifications' => $tournament_notifications,
            'game_invites' => $game_invites,
            'news_notifications' => $news_notifications
        ];

        $success_message = 'Настройки уведомлений успешно обновлены';
    } catch (PDOException $e) {
        $error_message = 'Ошибка при обновлении настроек';
    }
}

$current_page = 'dashboard';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Настройки уведомлений - Шахматный портал</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tournaments.css">
</head>
<body>
    <?php include INCLUDES_PATH . './header.php'?>

    <main class="container">
        <div class="dashboard-header">
            <h1>Настройки уведомлений</h1>
        </div>

        <div class="form-container">
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="notifications-form">
                <div class="notification-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="email_notifications" 
                               <?php echo $notifications['email_notifications'] ? 'checked' : ''; ?>>
                        <span class="checkbox-text">
                            <strong>Email уведомления</strong>
                            <span class="description">Получать уведомления на email</span>
                        </span>
                    </label>
                </div>

                <div class="notification-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="tournament_notifications"
                               <?php echo $notifications['tournament_notifications'] ? 'checked' : ''; ?>>
                        <span class="checkbox-text">
                            <strong>Турниры</strong>
                            <span class="description">Уведомления о новых турнирах и изменениях в текущих</span>
                        </span>
                    </label>
                </div>

                <div class="notification-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="game_invites"
                               <?php echo $notifications['game_invites'] ? 'checked' : ''; ?>>
                        <span class="checkbox-text">
                            <strong>Приглашения на игру</strong>
                            <span class="description">Уведомления о приглашениях сыграть партию</span>
                        </span>
                    </label>
                </div>

                <div class="notification-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="news_notifications"
                               <?php echo $notifications['news_notifications'] ? 'checked' : ''; ?>>
                        <span class="checkbox-text">
                            <strong>Новости</strong>
                            <span class="description">Уведомления о новостях и обновлениях портала</span>
                        </span>
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">Сохранить настройки</button>
                    <a href="dashboard.php" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
