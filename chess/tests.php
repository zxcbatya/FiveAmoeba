<?php
session_start();

define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');

// Include required files
require_once CONFIG_PATH . '/database.php';
require_once INCLUDES_PATH . '/functions.php';


// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Получение списка тестов
try {
    $stmt = $pdo->query("
        SELECT 
            t.*,
            COUNT(DISTINCT tq.id) as questions_count,
            COALESCE(tr.best_score, 0) as best_score,
            COALESCE(tr.attempts, 0) as attempts
        FROM chess_tests t
        LEFT JOIN test_questions tq ON t.id = tq.test_id
        LEFT JOIN (
            SELECT 
                test_id,
                user_id,
                MAX(score) as best_score,
                COUNT(*) as attempts
            FROM test_results
            WHERE user_id = {$_SESSION['user_id']}
            GROUP BY test_id, user_id
        ) tr ON t.id = tr.test_id
        GROUP BY t.id
        ORDER BY t.difficulty_level, t.title
    ");
    $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Ошибка при получении списка тестов: " . $e->getMessage();
}

$current_page = 'tests';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тестирование - Шахматный портал</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="css/tournaments.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tests.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .test-card h3{
            color: #2c3e50;
        }
        .tests-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            width: auto;
            margin: auto;
            gap: 10px;
        }
        .btn-filter {
            padding: 0.6rem 1.2rem;
            /* border-radius: 20px; */
            border: 1px solid var(--primary-color);
            background: transparent;
            color: var(--primary-color);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-filter:hover {
            background: var(--primary-color);
            color: white;
        }

        .btn-filter.active {
            background: var(--primary-color);
            color: white;
        }
        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn i {
            font-size: 1rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--background-white);
            color: var(--text-primary);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .btn-secondary:hover {
            background: var(--background-light);
            transform: translateY(-2px);
        }

        .btn-success {
            background: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        .test-actions{
            display: flex;
            justify-content: center;
            margin: auto;
            width: fit-content;
            gap: 10px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="page-header">
            <h1><i class="fas fa-tasks"></i> Тестирование</h1>
        </div>

        <div class="training-section">
            <button class="btn btn-filter active" data-filter="all">Все тесты</button>
            <button class="btn btn-filter" data-filter="beginner">Начинающий</button>
            <button class="btn btn-filter" data-filter="intermediate">Средний</button>
            <button class="btn btn-filter" data-filter="advanced">Продвинутый</button>
        </div>

        <div class="tests-grid">
            <?php foreach ($tests as $test): ?>
                <div class="test-card" data-difficulty="<?php echo htmlspecialchars($test['difficulty_level']); ?>">
                    <div class="test-header">
                        <h3><?php echo htmlspecialchars($test['title']); ?></h3>
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
                    </div>
                    
                    <p class="test-description"><?php echo htmlspecialchars($test['description']); ?></p>
                    
                    <div class="test-info">
                        <div class="info-item">
                            <i class="fas fa-question-circle"></i>
                            <span><?php echo $test['questions_count']; ?> вопросов</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <span><?php echo $test['time_limit']; ?> минут</span>
                        </div>
                        <?php if ($test['attempts'] > 0): ?>
                            <div class="info-item">
                                <i class="fas fa-trophy"></i>
                                <span>Лучший результат: <?php echo $test['best_score']; ?>%</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-redo"></i>
                                <span>Попыток: <?php echo $test['attempts']; ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="test-actions">
                        <a href="take-test.php?id=<?php echo $test['id']; ?>" class="btn ">
                            <?php echo $test['attempts'] > 0 ? 'Пройти снова' : 'Начать тест'; ?>
                        </a>
                        <?php if ($test['attempts'] > 0): ?>
                            <a href="test-results.php?id=<?php echo $test['id']; ?>" class="btn btn-secondary">
                                История
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('.btn-filter');
        const testCards = document.querySelectorAll('.test-card');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Убираем активный класс у всех кнопок
                filterButtons.forEach(btn => btn.classList.remove('active'));
                // Добавляем активный класс нажатой кнопке
                button.classList.add('active');

                const filter = button.dataset.filter;
                
                testCards.forEach(card => {
                    if (filter === 'all' || card.dataset.difficulty === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    });
    </script>
    <?php include INCLUDES_PATH . '/footer.php'; ?>
</body>
</html>
