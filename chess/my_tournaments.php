<?php
session_start();
define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');


// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$current_page = 'my_tournaments';

try {
    // Получаем турниры, в которых участвует пользователь
    $query = "SELECT t.*, 
              COUNT(DISTINCT p.user_id) as participants_count,
              1 as is_participant,
              tp.registration_date
              FROM tournaments t 
              JOIN tournament_participants tp ON t.id = tp.tournament_id
              LEFT JOIN tournament_participants p ON t.id = p.tournament_id 
              WHERE tp.user_id = :user_id
              GROUP BY t.id
              ORDER BY t.start_date DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои турниры - Шахматный портал</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tournaments.css">
</head>
<body>
    
    <?php include INCLUDES_PATH . './header.php'?>
    <main class="container">
        <div class="page-header">
            <h1><i class="fas fa-trophy"></i> Мои Турниры</h1>
            <p>Здесь отображаются все турниры, в которых вы участвуете.</p>
        </div>
        

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif (empty($tournaments)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                Вы пока не участвуете ни в одном турнире. 
                <a href="tournaments.php" class="alert-link">Просмотреть доступные турниры</a>
            </div>
        <?php else: ?>
            <div class="tournament-grid">
                <?php foreach ($tournaments as $tournament): ?>
                    <div class="tournament-card">
                        <div class="tournament-info">
                            <div class="tournament-header">
                                <h3 class="tournament-name"><?php echo htmlspecialchars($tournament['name']); ?></h3>
                                <div class="tournament-status <?php echo htmlspecialchars($tournament['status']); ?>">
                                    <?php echo getTournamentStatus($tournament['status']); ?>
                                </div>
                            </div>
                            <p class="tournament-description"><?php echo htmlspecialchars($tournament['description']); ?></p>
                            <div class="tournament-meta">
                                <div class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo formatDateTime($tournament['start_date']); ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-users"></i>
                                    <span>Участники: <?php echo (int)$tournament['participants_count']; ?> / <?php echo (int)$tournament['max_participants']; ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-clock"></i>
                                    <span>Регистрация: <?php echo formatDateTime($tournament['registration_date']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="tournament-actions">
                            <?php if ($tournament['status'] === 'ongoing'): ?>
                                <a href="play.php?tournament_id=<?php echo (int)$tournament['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-chess"></i> Играть
                                </a>
                            <?php endif; ?>
                            <a href="tournament_details.php?id=<?php echo (int)$tournament['id']; ?>" class="btn btn-secondary">
                                <i class="fas fa-info-circle"></i> Подробнее
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
