<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Проверка ID результата
if (!isset($_GET['id'])) {
    header("Location: tests.php");
    exit();
}

$result_id = $_GET['id'];

try {
    // Получение результатов теста
    $stmt = $pdo->prepare("
        SELECT r.*, t.title, t.difficulty_level
        FROM test_results r
        JOIN chess_tests t ON r.test_id = t.id
        WHERE r.id = ? AND r.user_id = ?
    ");
    $stmt->execute([$result_id, $_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        header("Location: tests.php");
        exit();
    }

    // Получение статистики пользователя по этому тесту
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_attempts,
            MAX(score) as best_score,
            AVG(score) as avg_score
        FROM test_results
        WHERE test_id = ? AND user_id = ?
    ");
    $stmt->execute([$result['test_id'], $_SESSION['user_id']]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Ошибка при получении результатов теста: " . $e->getMessage());
}

$current_page = 'tests';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты теста - Шахматный портал</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tests.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="page-header">
            <h1>Результаты теста</h1>
            <a href="tests.php" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> К списку тестов
            </a>
        </div>

        <div class="test-result-container">
            <div class="test-info-card">
                <h2><?php echo htmlspecialchars($result['title']); ?></h2>
                <span class="difficulty-badge <?php echo htmlspecialchars($result['difficulty_level']); ?>">
                    <?php
                    $difficulty_names = [
                        'beginner' => 'Начинающий',
                        'intermediate' => 'Средний',
                        'advanced' => 'Продвинутый'
                    ];
                    echo $difficulty_names[$result['difficulty_level']];
                    ?>
                </span>
            </div>

            <div class="result-score-card">
                <div class="score-circle <?php echo getScoreClass($result['score']); ?>">
                    <div class="score-number"><?php echo $result['score']; ?>%</div>
                </div>
                <div class="score-details">
                    <div class="detail-item">
                        <i class="fas fa-check"></i>
                        <span>Набрано баллов: <?php echo $result['score']; ?> из <?php echo $result['max_score']; ?></span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-clock"></i>
                        <span>Затраченное время: <?php echo formatTime($result['time_spent']); ?></span>
                    </div>
                </div>
            </div>

            <div class="statistics-cards">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Лучший результат</div>
                        <div class="stat-value"><?php echo round($stats['best_score']); ?>%</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Средний балл</div>
                        <div class="stat-value"><?php echo round($stats['avg_score']); ?>%</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-redo"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Всего попыток</div>
                        <div class="stat-value"><?php echo $stats['total_attempts']; ?></div>
                    </div>
                </div>
            </div>

            <div class="result-actions">
                <a href="take-test.php?id=<?php echo $result['test_id']; ?>" class="btn btn-primary">
                    <i class="fas fa-redo"></i> Пройти тест снова
                </a>
                <a href="test-results.php?id=<?php echo $result['test_id']; ?>" class="btn btn-secondary">
                    <i class="fas fa-history"></i> История результатов
                </a>
            </div>
        </div>
    </main>
</body>
</html>

<?php
function getScoreClass($score) {
    if ($score >= 90) return 'excellent';
    if ($score >= 70) return 'good';
    if ($score >= 50) return 'average';
    return 'poor';
}

function formatTime($seconds) {
    $minutes = floor($seconds / 60);
    $seconds = $seconds % 60;
    return sprintf("%d мин %d сек", $minutes, $seconds);
}
?>
