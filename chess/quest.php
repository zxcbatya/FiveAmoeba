<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');

require_once CONFIG_PATH . '/database.php';
require_once INCLUDES_PATH . '/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Онлайн-квест';
$current_page = 'quest';

echo "<!-- Database connection successful -->";

$user_id = $_SESSION['user_id'];
echo "<!-- Debug info: User ID = " . $user_id . " -->";

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as tournament_count FROM tournament_participants WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $tournament_data = $stmt->fetch();
    $tournament_count = $tournament_data['tournament_count'];
    echo "<!-- Debug info: Tournament count = " . $tournament_count . " -->";

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as solved_count 
        FROM user_progress 
        WHERE user_id = ? AND completed = 1 
        AND completed_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ");
    $stmt->execute([$user_id]);
    $solved_data = $stmt->fetch();
    $solved_count = $solved_data['solved_count'];
    echo "<!-- Debug info: Solved count = " . $solved_count . " -->";
} catch (PDOException $e) {
    echo "<!-- Debug error: " . $e->getMessage() . " -->";
    $tournament_count = 0;
    $solved_count = 0;
}

$quests = [
    [
        'id' => 1,
        'title' => 'Турнирный путь',
        'description' => 'Сыграйте в пяти турнирах и оцените каждую выигранную партию',
        'icon' => 'trophy',
        'progress' => min($tournament_count, 5),
        'total' => 5,
        'completed' => $tournament_count >= 5,
        'key_icon' => 'chess-rook'
    ],
    [
        'id' => 2,
        'title' => 'Ежедневные задачи',
        'description' => 'Решайте задачи неделю подряд (50 задач в день)',
        'icon' => 'tasks',
        'progress' => min($solved_count, 350),
        'total' => 350,
        'completed' => $solved_count >= 350,
        'key_icon' => 'chess-knight'
    ],
    [
        'id' => 3,
        'title' => 'Сеанс одновременной игры',
        'description' => 'Примите участие в сеансе одновременной игры',
        'icon' => 'users',
        'progress' => 0,
        'total' => 1,
        'completed' => false,
        'key_icon' => 'chess-bishop'
    ],
    [
        'id' => 4,
        'title' => 'Битва с тренером',
        'description' => 'Вызовите тренера на шахматный поединок',
        'icon' => 'user-graduate',
        'progress' => 0,
        'total' => 1,
        'completed' => false,
        'key_icon' => 'chess-queen'
    ],
    [
        'id' => 5,
        'title' => 'Обучающие видео',
        'description' => 'Посмотрите серию обучающих видео',
        'icon' => 'video',
        'progress' => 0,
        'total' => 5,
        'completed' => false,
        'key_icon' => 'chess-pawn'
    ],
    [
        'id' => 6,
        'title' => 'Наставник',
        'description' => 'Научите друга играть в шахматы',
        'icon' => 'hands-helping',
        'progress' => 0,
        'total' => 1,
        'completed' => false,
        'key_icon' => 'chess-king'
    ]
];

$completed_quests = array_filter($quests, function($quest) {
    return $quest['completed'];
});
$total_progress = count($completed_quests);
$all_completed = $total_progress === count($quests);

echo "<!-- Debug info: Total quests = " . count($quests) . ", Completed = " . $total_progress . " -->";
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Шахматный портал</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .quest-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 40px 20px;
            border-radius: 10px;
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .quest-header h1 {
            font-size: 2.5em;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .quest-header p {
            font-size: 1.1em;
            opacity: 0.9;
            margin: 0;
        }

        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border-radius: 15px;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .card.border-success {
            border-left: 4px solid #28a745 !important;
        }

        .card-body {
            padding: 1.5rem;
        }

        .quest-icon {
            font-size: 2.5em;
            color: #2a5298;
            margin-bottom: 1rem;
        }

        .quest-key {
            font-size: 1.8em;
            color: #ffd700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .card-title {
            font-size: 1.3em;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.8rem;
        }

        .card-text {
            color: #666;
            margin-bottom: 1.2rem;
            line-height: 1.5;
        }

        .progress {
            height: 10px;
            border-radius: 5px;
            background-color: #f5f5f5;
            margin-bottom: 0.5rem;
            overflow: hidden;
        }

        .progress-bar {
            background: linear-gradient(90deg, #2196F3 0%, #4CAF50 100%);
            transition: width 0.6s ease;
        }

        .completion-card {
            background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .completion-card .trophy-icon {
            font-size: 4em;
            color: #ffd700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            margin-bottom: 1rem;
        }

        .completion-card h2 {
            font-size: 2em;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .completion-card p {
            font-size: 1.1em;
            opacity: 0.9;
            margin-bottom: 1.5rem;
        }

        .btn-certificate {
            background: #ffd700;
            color: #2c3e50;
            border: none;
            padding: 0.8rem 2rem;
            font-size: 1.1em;
            font-weight: 600;
            border-radius: 50px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-certificate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0,0,0,0.2);
            background: #ffed4a;
            color: #2c3e50;
        }

        @media (max-width: 768px) {
            .quest-header h1 {
                font-size: 2em;
            }
            .quest-header p {
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
    <?php include INCLUDES_PATH . '/header.php'; ?>
    
    <div class="container py-4">
        <div class="quest-header text-center">
            <h1>Онлайн-квест</h1>
            <p>Выполняйте задания, собирайте ключи и получите сертификат участника окружной программы!</p>
        </div>

        <div class="row g-4">
            <?php foreach ($quests as $quest): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 <?php echo $quest['completed'] ? 'border-success' : ''; ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="quest-icon">
                                    <i class="fas fa-<?php echo $quest['icon']; ?>"></i>
                                </div>
                                <?php if ($quest['completed']): ?>
                                    <div class="quest-key">
                                        <i class="fas fa-<?php echo $quest['key_icon']; ?>"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <h5 class="card-title"><?php echo $quest['title']; ?></h5>
                            <p class="card-text"><?php echo $quest['description']; ?></p>
                            
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: <?php echo ($quest['progress'] / $quest['total']) * 100; ?>%">
                                </div>
                            </div>
                            <small class="text-muted">
                                Прогресс: <?php echo $quest['progress']; ?>/<?php echo $quest['total']; ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($all_completed): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card completion-card">
                    <div class="card-body text-center py-5">
                        <div class="trophy-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h2>Поздравляем!</h2>
                        <p>Вы успешно выполнили все задания квеста и собрали все ключи!</p>
                        <a href="#" class="btn btn-certificate">
                            <i class="fas fa-certificate me-2"></i>
                            Получить сертификат
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php include INCLUDES_PATH . '/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Анимация прогресс-баров
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100);
            });
        });
    </script>
</body>
</html>
