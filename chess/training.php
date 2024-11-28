<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');

// Получение списка тренировочных материалов
$query = "SELECT * FROM training_materials ORDER BY difficulty_level ASC";
try {
    $stmt = $pdo->query($query);
    $materials = $stmt->fetchAll();
} catch(PDOException $e) {
    $materials = [];
}

$current_page = 'training';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тренировки - Шахматный портал</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tournaments.css">
    <link rel="stylesheet" href="css/notifications.css">
</head>
<body>
    <?php include INCLUDES_PATH . './header.php'?>

    <main class="container">
        <h1 class="page-title">
            <i class="fas fa-graduation-cap"></i>
            Тренировки
        </h1>
        <div class="training-section">
            <div class="difficulty-filters">
                <button class="btn btn-filter active" data-level="all">Все уровни</button>
                <button class="btn btn-filter" data-level="beginner">Начинающий</button>
                <button class="btn btn-filter" data-level="intermediate">Средний</button>
                <button class="btn btn-filter" data-level="advanced">Продвинутый</button>
            </div>

            <div class="training-grid">
                <?php foreach ($materials as $material): ?>
                    <div class="training-card" data-level="<?php echo strtolower($material['difficulty_level']); ?>">
                        <div class="training-icon">
                            <?php
                            $icon = 'chess-pawn';
                            if ($material['id'] == 2) $icon = 'chess-knight';
                            elseif ($material['id'] == 3) $icon = 'chess-queen';
                            ?>
                            <i class="fas fa-<?php echo $icon; ?>"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($material['title']); ?></h3>
                        <p><?php echo htmlspecialchars($material['short_description']); ?></p>
                        <div class="training-meta">
                            <span class="difficulty <?php echo strtolower($material['difficulty_level']); ?>">
                                <i class="fas fa-signal"></i>
                                <?php
                                switch(strtolower($material['difficulty_level'])) {
                                    case 'beginner':
                                        echo 'Начинающий';
                                        break;
                                    case 'intermediate':
                                        echo 'Средний';
                                        break;
                                    case 'advanced':
                                        echo 'Продвинутый';
                                        break;
                                }
                                ?>
                            </span>
                            <span class="duration">
                                <i class="fas fa-clock"></i>
                                <?php echo $material['duration']; ?> мин
                            </span>
                        </div>
                        <a href="lesson.php?id=<?php echo $material['id']; ?>" class="btn start-lesson" data-lesson-id="<?php echo $material['id']; ?>">
                            Начать урок
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Фильтрация уроков по уровню сложности
        document.querySelectorAll('.btn-filter').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.btn-filter').forEach(btn => {
                    btn.classList.remove('active');
                });
                button.classList.add('active');
                
                const level = button.dataset.level;
                const cards = document.querySelectorAll('.training-card');
                
                cards.forEach(card => {
                    if (level === 'all') {
                        card.style.display = 'flex';
                    } else {
                        const cardLevel = card.dataset.level;
                        card.style.display = cardLevel === level ? 'flex' : 'none';
                    }
                });
            });
        });

        // Обработчик кнопок "Начать урок"
        document.querySelectorAll('.start-lesson').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const lessonId = this.dataset.lessonId;
                
                // Проверяем, авторизован ли пользователь
                <?php if (!isset($_SESSION['user_id'])): ?>
                    // Если не авторизован, показываем сообщение
                    alert('Для доступа к урокам необходимо войти в систему');
                    window.location.href = 'login.php';
                    return;
                <?php endif; ?>
                
                // Если авторизован, переходим к уроку
                window.location.href = `lesson.php?id=${lessonId}`;
            });
        });
    </script>
</body>
</html>
