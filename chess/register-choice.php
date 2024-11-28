<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');

$current_page = 'register';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Выбор типа регистрации - Шахматный портал</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tournaments.css">
    <style>
        .choice-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        .choice-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        .choice-card {
            background: var(--background-dark);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            transition: transform 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: var(--text-light);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }
        .choice-card:hover {
            transform: translateY(-5px);
        }
        .choice-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        .choice-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 10px;
        }
        .choice-description {
            color: var(--text-muted);
            margin-bottom: 20px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <?php include INCLUDES_PATH . '/header.php'; ?>

    <main class="container">
        <div class="choice-container">
            <h1 class="page-title text-center">Регистрация на портале</h1>
            <div class="choice-grid">
                <a href="register-pupil.php" class="choice-card">
                    <i class="fas fa-user-graduate choice-icon"></i>
                    <div class="choice-content">
                        <h2 class="choice-title">Я ученик</h2>
                        <p class="choice-description">
                            Зарегистрируйтесь как ученик, чтобы участвовать в турнирах,
                            отслеживать свой прогресс и получать рекомендации от тренеров
                        </p>
                    </div>
                </a>
                
                <a href="register-coach.php" class="choice-card">
                    <i class="fas fa-chalkboard-teacher choice-icon"></i>
                    <div class="choice-content">
                        <h2 class="choice-title">Я тренер</h2>
                        <p class="choice-description">
                            Зарегистрируйтесь как тренер, чтобы делиться своим опытом,
                            проводить занятия и помогать ученикам развиваться
                        </p>
                    </div>
                </a>
            </div>
        </div>
    </main>

    <?php include INCLUDES_PATH . '/footer.php'; ?>
</body>
</html>
