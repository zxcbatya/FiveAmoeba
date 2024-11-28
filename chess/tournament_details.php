<?php
session_start();

// Define base path constants
define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');

// Include required files
require_once CONFIG_PATH . '/database.php';
require_once INCLUDES_PATH . '/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$current_page = 'tournament_details';

// Получаем ID турнира из GET-параметра
$tournament_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Получаем информацию о турнире
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
        'user_id' => $_SESSION['user_id'] ?? 0
    ]);
    $tournament = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tournament) {
        throw new Exception('Турнир не найден');
    }

    // Получаем список участников турнира
    $query = "SELECT u.*, tp.registration_date
              FROM tournament_participants tp
              JOIN users u ON tp.user_id = u.id
              WHERE tp.tournament_id = :tournament_id
              ORDER BY tp.registration_date ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['tournament_id' => $tournament_id]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tournament['name'] ?? 'Турнир не найден'); ?> - Шахматный портал</title>
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tournament_details.css">
</head>
<body>
    <?php include INCLUDES_PATH . '/header.php'; ?>
    <main class="container">
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php else: ?>
            <div class="tournament-header">
                <h1><?php echo htmlspecialchars($tournament['name']); ?></h1>
                <div class="tournament-status <?php echo htmlspecialchars($tournament['status']); ?>">
                    <?php echo getTournamentStatus($tournament['status']); ?>
                </div>
            </div>

            <div class="tournament-content">
                <div class="tournament-info">
                    <div class="info-section">
                        <h2>О турнире</h2>
                        <p><?php echo nl2br(htmlspecialchars($tournament['description'])); ?></p>
                    </div>

                    <div class="info-section">
                        <h2>Детали турнира</h2>
                        <div class="details-grid">
                            <div class="detail-item">
                                <i class="fas fa-calendar-alt"></i>
                                <div>
                                    <h3>Начало</h3>
                                    <p><?php echo formatDateTime($tournament['start_date']); ?></p>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-flag-checkered"></i>
                                <div>
                                    <h3>Окончание</h3>
                                    <p><?php echo formatDateTime($tournament['end_date']); ?></p>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-users"></i>
                                <div>
                                    <h3>Участники</h3>
                                    <p><?php echo (int)$tournament['participants_count']; ?> / <?php echo (int)$tournament['max_participants']; ?></p>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-chess-king"></i>
                                <div>
                                    <h3>Статус</h3>
                                    <p><?php echo getTournamentStatus($tournament['status']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="tournament-actions">
                            <?php if ($tournament['is_participant']): ?>
                                <button class="btn btn-success" disabled>
                                    <i class="fas fa-check"></i> Вы участвуете
                                </button>
                            <?php elseif ($tournament['status'] === 'upcoming' && $tournament['participants_count'] < $tournament['max_participants']): ?>
                                <a href="join_tournament.php?id=<?php echo (int)$tournament['id']; ?>" class="btn ">
                                    <i class="fas fa-plus"></i> Участвовать
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="participants-section">
                    <h2>Участники турнира</h2>
                    <?php if (empty($participants)): ?>
                        <div class="no-participants">
                            <i class="fas fa-users"></i>
                            <p>Пока нет участников</p>
                        </div>
                    <?php else: ?>
                        <div class="participants-list">
                            <?php foreach ($participants as $participant): ?>
                                <div class="participant-card">
                                    <img src="<?php echo getProfileImage($participant['avatar'] ?? ''); ?>" alt="<?php echo htmlspecialchars($participant['full_name']); ?>" class="participant-avatar">
                                    <div class="participant-info">
                                        <h3><?php echo htmlspecialchars($participant['full_name']); ?></h3>
                                        <p>Рейтинг: <?php echo $participant['rating'] ?? 'Не установлен'; ?></p>
                                        <p class="registration-date">
                                            <i class="fas fa-clock"></i>
                                            Дата регистрации: <?php echo formatDate($participant['registration_date']); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php include INCLUDES_PATH . '/footer.php'; ?>
</body>
</html>
