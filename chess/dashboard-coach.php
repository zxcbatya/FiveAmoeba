<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');

// Проверка авторизации и роли тренера
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_coach']) || !$_SESSION['is_coach']) {
    header("Location: login.php");
    exit();
}

$coach_id = $_SESSION['user_id'];

// Получение информации о тренере
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_coach = 1");
$stmt->execute([$coach_id]);
$coach = $stmt->fetch();

// Получение списка учеников тренера
$stmt = $pdo->prepare("
    SELECT u.*, 
           COUNT(g.id) as total_games,
           AVG(g.rating_change) as avg_rating_change
    FROM users u
    LEFT JOIN coach_students cs ON u.id = cs.student_id
    LEFT JOIN games g ON u.id = g.player_id
    WHERE cs.coach_id = ?
    GROUP BY u.id
");
$stmt->execute([$coach_id]);
$students = $stmt->fetchAll();

// Получение последних результатов учеников
$stmt = $pdo->prepare("
    SELECT g.*, u.username, u.full_name
    FROM games g
    JOIN users u ON g.player_id = u.id
    JOIN coach_students cs ON u.id = cs.student_id
    WHERE cs.coach_id = ?
    ORDER BY g.date DESC
    LIMIT 10
");
$stmt->execute([$coach_id]);
$recent_results = $stmt->fetchAll();

$current_page = 'dashboard';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет тренера - Шахматный портал</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tournaments.css">
    <style>
        .coach-info {
            background: white;
            border-radius: 8px;
            padding: 1.2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-top: 0.8rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .info-item {
            text-align: center;
            padding: 0.8rem;
            border-radius: 6px;
            background: #f8f9fa;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .info-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        }

        .info-item h3 {
            color: #6c757d;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.3rem;
        }

        .info-item a {
            color: #2c3e50;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
        }

        .coach-username {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            margin: 0.5rem 0 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .coach-username:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 6px rgba(0,0,0,0.2);
        }

        .coach-username i {
            margin-right: 0.4rem;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include INCLUDES_PATH . '/header.php'; ?>
        <div class="dashboard-container-top">
            <h1 class="page-title">
                <i class="fas fa-chalkboard-teacher"></i>
                Личный кабинет тренера
            </h1>
            <div class="coach-info">
                <p class="coach-username">
                    <i class="fas fa-user"></i>
                    <?php echo htmlspecialchars($coach['username']); ?>
                </p>
                <div class="info-grid">
                    <div class="info-item">
                        <h3>ФИО</h3>
                        <a><?php echo htmlspecialchars($coach['full_name']); ?></a>
                    </div>
                    <?php if ($coach['rating']): ?>
                    <div class="info-item">
                        <h3>Рейтинг</h3>
                        <a><?php echo $coach['rating']; ?></a>
                    </div>
                    <?php endif; ?>
                    <?php if ($coach['sports_rank']): ?>
                    <div class="info-item">
                        <h3>Разряд</h3>
                        <a><?php echo htmlspecialchars($coach['sports_rank']); ?></a>
                    </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <h3>Количество учеников</h3>
                        <a><?php echo count($students); ?></a>
                    </div>
                </div>
            </div>
        </div>  
        <div class="dashboard-container-bottom">
            <div class="dashboard-grid">

                <div class="dashboard-coach-card">
                    <h2>
                        <i class="fas fa-users"></i>
                        Мои ученики
                    </h2>
                    <ul class="student-list">
                        <?php foreach ($students as $student): ?>
                            <li class="student-item">
                                <div class="student-info">
                                    <div class="student-avatar">
                                        <?php echo strtoupper(substr($student['username'], 0, 1)); ?>
                                    </div>
                                    <div class="student-details">
                                        <h3><?php echo htmlspecialchars($student['full_name']); ?></h3>
                                        <p><?php echo htmlspecialchars($student['username']); ?></p>
                                    </div>
                                </div>
                                <div class="student-stats">
                                    <div class="rating">Рейтинг: <?php echo $student['rating'] ?? 'Нет'; ?></div>
                                    <div class="games">Игр: <?php echo $student['total_games']; ?></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="add-student.php" class="btn-add-student">
                        <i class="fas fa-user-plus"></i>
                        Добавить ученика
                    </a>
                </div>

                <div class="dashboard-coach-card">
                    <h2>
                        <i class="fas fa-chart-line"></i>
                        Последние результаты
                    </h2>
                    <ul class="result-list">
                        <?php foreach ($recent_results as $result): ?>
                            <li class="result-item">
                                <div class="result-info">
                                    <h3><?php echo htmlspecialchars($result['full_name']); ?></h3>
                                    <span><?php echo date('d.m.Y', strtotime($result['date'])); ?></span>
                                </div>
                                <div class="result-change <?php echo $result['rating_change'] >= 0 ? 'positive' : 'negative'; ?>">
                                    <?php echo $result['rating_change'] >= 0 ? '+' : ''; ?><?php echo $result['rating_change']; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="dashboard-coach-card">
                    <h2>
                        <i class="fas fa-users"></i>
                        Мой профиль
                    </h2>
                    <div class="profile-info">
                        <a href="edit-coach-profile.php" class="btn ">Редактировать профиль</a>
                    </div>
                </div>
            </div>
        </div>

    <?php include INCLUDES_PATH . '/footer.php'; ?>
</body>
</html>
