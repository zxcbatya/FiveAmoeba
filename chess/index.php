<?php
session_start();

// Define base path constants
define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');

// Include required files
require_once CONFIG_PATH . '/database.php';
require_once INCLUDES_PATH . '/functions.php';

$page_title = 'Главная';
$current_page = 'home';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Шахматный портал - Главная</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tournaments.css">
    <link rel="stylesheet" href="css/notifications.css">
</head>
<body>

    <?php include INCLUDES_PATH . '/header.php'; ?>
    
    <main class="container">
        <h1 class="page-title">
            <i class="fas fa-chess"></i>
            Добро пожаловать в шахматный портал
        </h1>

        <div class="content-section">
            <div class="info-cards">
                <div class="info-card">
                    <div class="info-card-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3>Турниры</h3>
                    <p>Участвуйте в турнирах разного уровня, повышайте свой рейтинг.</p>
                    <a href="tournaments.php" class="btn">Перейти к турнирам</a>
                </div>

                <div class="info-card">
                    <div class="info-card-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3>Тренировки</h3>
                    <p>Улучшайте свои навыки с помощью наших тренировочных программ и упражнений.</p>
                    <a href="training.php" class="btn">Начать тренировку</a>
                </div>

                <div class="info-card">
                    <div class="info-card-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3>Тесты</h3>
                    <p>Проверьте свои знания и навыки с помощью интерактивных тестов.</p>
                    <a href="tests.php" class="btn">Пройти тесты</a>
                </div>

                <div class="info-card">
                    <div class="info-card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Сообщество</h3>
                    <p>Общайтесь с другими игроками, делитесь опытом и находите партнеров для игры.</p>
                    <a href="players.php" class="btn">Найти игроков</a>
                </div>
            </div>
        </div>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="cta-section">
                <h2>Присоединяйтесь к нам</h2>
                <p>Создайте аккаунт, чтобы участвовать в турнирах и отслеживать свой прогресс</p>
                <div class="cta-buttons">
                    <a href="register-choice.php" class="btn">
                        <i class="fas fa-user-plus"></i> Регистрация
                    </a>
                    <a href="login.php" class="btn btn-secondary">
                        <i class="fas fa-sign-in-alt"></i> Вход
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php include INCLUDES_PATH . '/footer.php'; ?>
</body>
</html>
