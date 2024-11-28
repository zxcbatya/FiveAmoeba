<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Проверка ID теста
if (!isset($_GET['id'])) {
    header("Location: tests.php");
    exit();
}

$test_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    // Получение информации о тесте
    $stmt = $pdo->prepare("
        SELECT title, difficulty_level, description
        FROM chess_tests
        WHERE id = ?
    ");
    $stmt->execute([$test_id]);
    $test = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$test) {
        header("Location: tests.php");
        exit();
    }

    // Получение истории результатов
    $stmt = $pdo->prepare("
        SELECT id, score, max_score, time_spent, completed_at
        FROM test_results
        WHERE test_id = ? AND user_id = ?
        ORDER BY completed_at DESC
    ");
    $stmt->execute([$test_id, $user_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Получение статистики
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_attempts,
            MAX(score) as best_score,
            MIN(score) as worst_score,
            AVG(score) as avg_score,
            AVG(time_spent) as avg_time
        FROM test_results
        WHERE test_id = ? AND user_id = ?
    ");
    $stmt->execute([$test_id, $user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Ошибка при получении результатов: " . $e->getMessage());
}

$current_page = 'tests';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>История результатов - Шахматный портал</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tests.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>
    <?php include 'includes/header.php'; ?>
    <script>
        h2{
            color: #2c3e50;
        }
    </script>
    <main class="container">
        <div class="page-header">
            <h1>История результатов</h1>
            <a href="tests.php" class="btn ">
                <i class="fas fa-arrow-left"></i> К списку тестов
            </a>
        </div>

        <div class="test-info-card">
            <h2><?php echo htmlspecialchars($test['title']); ?></h2>
            <span class="difficulty-badge <?php echo htmlspecialchars($test['difficulty_level']); ?>">
                <?php
                $difficulty_names = [
                    'beginner' => 'Начинающий',
                    'intermediate' => 'Средний',
                    'advanced' => 'Продвинутый'
                ];
                echo $difficulty_names[$test['difficulty_level']];
                ?>
            </span>
            <p><?php echo htmlspecialchars($test['description']); ?></p>
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
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Среднее время</div>
                    <div class="stat-value"><?php echo formatTime($stats['avg_time']); ?></div>
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

        <div class="chart-container">
            <canvas id="progressChart"></canvas>
        </div>

        <div class="results-table-container">
            <table class="results-table">
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Результат</th>
                        <th>Время</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $result): ?>
                        <tr>
                            <td><?php echo date('d.m.Y H:i', strtotime($result['completed_at'])); ?></td>
                            <td>
                                <div class="score-badge <?php echo getScoreClass($result['score']); ?>">
                                    <?php echo $result['score']; ?>%
                                </div>
                            </td>
                            <td><?php echo formatTime($result['time_spent']); ?></td>
                            <td>
                                <a href="test-result.php?id=<?php echo $result['id']; ?>" class="btn btn-small">
                                    <i class="fas fa-eye"></i> Детали
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="result-actions">
            <a href="take-test.php?id=<?php echo $test_id; ?>" class="btn ">
                <i class="fas fa-redo"></i> Пройти тест снова
            </a>
        </div>
    </main>

    <script>
        // Подготовка данных для графика
        const results = <?php echo json_encode(array_reverse($results)); ?>;
        const scores = results.map(r => r.score);
        const dates = results.map(r => new Date(r.completed_at).toLocaleDateString());

        // Создание графика прогресса
        const ctx = document.getElementById('progressChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Результаты тестов',
                    data: scores,
                    borderColor: '#1976d2',
                    backgroundColor: 'rgba(25, 118, 210, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'График прогресса'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Результат (%)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Дата'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>

<?php
function formatTime($seconds) {
    $totalSeconds = (int)round(floatval($seconds));
    $minutes = (int)($totalSeconds / 60);
    $seconds = (int)($totalSeconds % 60);
    return sprintf("%d мин %d сек", $minutes, $seconds);
}

function getScoreClass($score) {
    if ($score >= 90) return 'excellent';
    if ($score >= 70) return 'good';
    if ($score >= 50) return 'average';
    return 'poor';
}
?>
