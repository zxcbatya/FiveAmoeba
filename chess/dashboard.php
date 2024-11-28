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

// Получение данных пользователя
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Получение статистики игр
$stats = [
    'total_games' => 0,
    'wins' => 0,
    'losses' => 0
];

try {
    $query = "SELECT 
                COUNT(*) as total_games,
                SUM(CASE WHEN winner_id = ? THEN 1 ELSE 0 END) as wins,
                SUM(CASE WHEN (player1_id = ? OR player2_id = ?) AND winner_id != ? AND winner_id IS NOT NULL THEN 1 ELSE 0 END) as losses
              FROM games 
              WHERE player1_id = ? OR player2_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
    $db_stats = $stmt->fetch();
    
    if ($db_stats) {
        $stats = [
            'total_games' => (int)$db_stats['total_games'],
            'wins' => (int)$db_stats['wins'],
            'losses' => (int)$db_stats['losses']
        ];
    }
} catch (PDOException $e) {
    // Таблица может не существовать, используем значения по умолчанию
}

// Получение последних игр
$recent_games = [];
try {
    $query = "SELECT g.*, 
                u1.username as player1_name, 
                u2.username as player2_name
              FROM games g
              JOIN users u1 ON g.player1_id = u1.id
              JOIN users u2 ON g.player2_id = u2.id
              WHERE g.player1_id = ? OR g.player2_id = ?
              ORDER BY g.date_played DESC
              LIMIT 5";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $user_id]);
    $recent_games = $stmt->fetchAll();
} catch (PDOException $e) {
    // Таблица может не существовать
}

// Получение предстоящих турниров
$upcoming_tournaments = [];
try {
    $query = "SELECT t.*, 
                COUNT(p.tournament_id) as participants_count
              FROM tournaments t
              LEFT JOIN participants p ON t.id = p.tournament_id
              WHERE t.start_date > NOW()
              GROUP BY t.id
              ORDER BY t.start_date ASC
              LIMIT 3";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $upcoming_tournaments = $stmt->fetchAll();
} catch (PDOException $e) {
    // Таблица может не существовать
}

$current_page = 'dashboard';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - Шахматный портал</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tournaments.css">
</head>
<body>

    <?php include INCLUDES_PATH . './header.php'?>
    <main class="container">
        <div class="dashboard-header">
            <h1>Личный кабинет</h1>
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-details">
                    <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    <p class="username"><i class="fas fa-user"></i> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p>Рейтинг: <?php echo $user['rating'] ?? 'Не установлен'; ?></p>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- Статистика -->
            <div class="dashboard-card stats-card">
                <h3><i class="fas fa-chart-bar"></i> Статистика</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $stats['total_games']; ?></span>
                        <span class="stat-label">Всего игр</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $stats['wins']; ?></span>
                        <span class="stat-label">Победы</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $stats['losses']; ?></span>
                        <span class="stat-label">Поражения</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $stats['total_games'] > 0 ? round(($stats['wins'] / $stats['total_games']) * 100) : 0; ?>%</span>
                        <span class="stat-label">Винрейт</span>
                    </div>
                </div>
            </div>

            <!-- Последние игры -->
            <div class="dashboard-card recent-games-card">
                <h3><i class="fas fa-history"></i> Последние игры</h3>
                <div class="recent-games-list">
                    <?php if (empty($recent_games)): ?>
                        <p class="no-data">У вас пока нет сыгранных партий</p>
                    <?php else: ?>
                        <?php foreach ($recent_games as $game): ?>
                            <div class="game-item">
                                <div class="game-players">
                                    <span class="<?php echo $game['player1_id'] == $user_id ? 'current-player' : ''; ?>">
                                        <?php echo htmlspecialchars($game['player1_name']); ?>
                                    </span>
                                    <span class="vs">vs</span>
                                    <span class="<?php echo $game['player2_id'] == $user_id ? 'current-player' : ''; ?>">
                                        <?php echo htmlspecialchars($game['player2_name']); ?>
                                    </span>
                                </div>
                                <div class="game-result <?php echo $game['winner_id'] == $user_id ? 'win' : 'loss'; ?>">
                                    <?php echo $game['winner_id'] == $user_id ? 'Победа' : 'Поражение'; ?>
                                </div>
                                <div class="game-date">
                                    <?php echo date('d.m.Y', strtotime($game['date_played'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Предстоящие турниры -->
            <div class="dashboard-card tournaments-card">
                <h3><i class="fas fa-trophy"></i> Предстоящие турниры</h3>
                <div class="upcoming-tournaments-list">
                    <?php if (empty($upcoming_tournaments)): ?>
                        <p class="no-data">Нет предстоящих турниров</p>
                    <?php else: ?>
                        <?php foreach ($upcoming_tournaments as $tournament): ?>
                            <div class="tournament-item">
                                <div class="tournament-info">
                                    <h4><?php echo htmlspecialchars($tournament['name']); ?></h4>
                                    <p class="tournament-date">
                                        <i class="far fa-calendar-alt"></i>
                                        <?php echo date('d.m.Y', strtotime($tournament['start_date'])); ?>
                                    </p>
                                    <p class="tournament-participants">
                                        <i class="fas fa-users"></i>
                                        <?php echo $tournament['participants_count']; ?> участников
                                    </p>
                                </div>
                                <a href="tournament.php?id=<?php echo $tournament['id']; ?>" class="btn btn-primary">Подробнее</a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Настройки профиля -->
            <div class="dashboard-card settings-card">
                <h3><i class="fas fa-cog"></i> Настройки профиля</h3>
                <div class="settings-list">
                    <a href="edit-profile.php" class="settings-item">
                        <i class="fas fa-user-edit"></i>
                        <span>Редактировать профиль</span>
                    </a>
                    <a href="change-password.php" class="settings-item">
                        <i class="fas fa-key"></i>
                        <span>Изменить пароль</span>
                    </a>
                    <a href="notifications.php" class="settings-item">
                        <i class="fas fa-bell"></i>
                        <span>Настройки уведомлений</span>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
