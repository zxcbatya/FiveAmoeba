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

// Получение информации о тесте
try {
    $stmt = $pdo->prepare("
        SELECT t.*, COUNT(DISTINCT q.id) as total_questions
        FROM chess_tests t
        LEFT JOIN test_questions q ON t.id = q.test_id
        WHERE t.id = ?
        GROUP BY t.id
    ");
    $stmt->execute([$test_id]);
    $test = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$test) {
        header("Location: tests.php");
        exit();
    }

    // Получение вопросов теста
    $stmt = $pdo->prepare("
        SELECT q.*, GROUP_CONCAT(
            CONCAT(a.id, ':::', a.answer_text)
            ORDER BY RAND()
            SEPARATOR '|||'
        ) as answers
        FROM test_questions q
        LEFT JOIN question_answers a ON q.id = a.question_id
        WHERE q.test_id = ?
        GROUP BY q.id
        ORDER BY RAND()
    ");
    $stmt->execute([$test_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Ошибка при получении данных теста: " . $e->getMessage());
}

$current_page = 'tests';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($test['title']); ?> - Тестирование</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tests.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .question-card h3{
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="page-header">
            <h1><?php echo htmlspecialchars($test['title']); ?></h1>
            <div class="test-timer" id="timer">
                <i class="fas fa-clock"></i>
                <span id="time-remaining"></span>
            </div>
        </div>

        <form id="test-form" action="process-test.php" method="POST">
            <input type="hidden" name="test_id" value="<?php echo $test_id; ?>">
            <input type="hidden" name="start_time" value="<?php echo time(); ?>">
            
            <div class="test-progress">
                <div class="progress-bar">
                    <div class="progress" id="progress-bar" style="width: 0%"></div>
                </div>
                <span id="question-counter">Вопрос 1 из <?php echo count($questions); ?></span>
            </div>

            <div class="questions-container">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="question-card" id="question-<?php echo $index + 1; ?>" style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>">
                        <h3>Вопрос <?php echo $index + 1; ?></h3>
                        
                        <div class="question-text">
                            <?php echo htmlspecialchars($question['question_text']); ?>
                        </div>

                        <?php if ($question['question_image']): ?>
                            <div class="question-image">
                                <img src="<?php echo htmlspecialchars($question['question_image']); ?>" alt="Изображение к вопросу">
                            </div>
                        <?php endif; ?>

                        <div class="answers-list">
                            <?php
                            if (!empty($question['answers'])) {
                                $answers = explode('|||', $question['answers']);
                                foreach ($answers as $answer) {
                                    $answer_parts = explode(':::', $answer);
                                    $answer_id = isset($answer_parts[0]) ? $answer_parts[0] : '';
                                    $answer_text = isset($answer_parts[1]) ? $answer_parts[1] : '';
                            ?>
                                <label class="answer-option">
                                    <input type="radio" name="question[<?php echo $question['id']; ?>]" value="<?php echo htmlspecialchars($answer_id); ?>" required>
                                    <span class="answer-text"><?php echo htmlspecialchars($answer_text); ?></span>
                                </label>
                            <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="test-navigation">
                <button type="button" id="prev-btn" class="btn btn-secondary" style="display: none;">
                    <i class="fas fa-arrow-left"></i> Предыдущий
                </button>
                <button type="button" id="next-btn" class="btn ">
                    Следующий <i class="fas fa-arrow-right"></i>
                </button>
                <button type="submit" id="finish-btn" class="btn" style="display: none;">
                    Завершить тест <i class="fas fa-check"></i>
                </button>
            </div>
        </form>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const totalQuestions = <?php echo count($questions); ?>;
        let currentQuestion = 1;
        const timeLimit = <?php echo $test['time_limit']; ?> * 60; // в секундах
        let timeRemaining = timeLimit;

        const questionCards = document.querySelectorAll('.question-card');
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const finishBtn = document.getElementById('finish-btn');
        const progressBar = document.getElementById('progress-bar');
        const questionCounter = document.getElementById('question-counter');
        const timerDisplay = document.getElementById('time-remaining');
        const testForm = document.getElementById('test-form');

        function updateQuestion() {
            questionCards.forEach(card => card.style.display = 'none');
            document.getElementById(`question-${currentQuestion}`).style.display = 'block';
            
            prevBtn.style.display = currentQuestion > 1 ? 'block' : 'none';
            nextBtn.style.display = currentQuestion < totalQuestions ? 'block' : 'none';
            finishBtn.style.display = currentQuestion === totalQuestions ? 'block' : 'none';
            
            progressBar.style.width = `${(currentQuestion / totalQuestions) * 100}%`;
            questionCounter.textContent = `Вопрос ${currentQuestion} из ${totalQuestions}`;
        }

        prevBtn.addEventListener('click', () => {
            if (currentQuestion > 1) {
                currentQuestion--;
                updateQuestion();
            }
        });

        nextBtn.addEventListener('click', () => {
            if (currentQuestion < totalQuestions) {
                currentQuestion++;
                updateQuestion();
            }
        });

        // Таймер
        function updateTimer() {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            timerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

            if (timeRemaining <= 0) {
                testForm.submit();
            } else {
                timeRemaining--;
            }
        }

        // Обновляем таймер каждую секунду
        updateTimer();
        const timerInterval = setInterval(updateTimer, 1000);

        // Подтверждение при попытке покинуть страницу
        window.addEventListener('beforeunload', function(e) {
            e.preventDefault();
            e.returnValue = '';
        });

        // Отправка формы
        testForm.addEventListener('submit', function() {
            clearInterval(timerInterval);
            window.removeEventListener('beforeunload');
        });
    });
    </script>
</body>
</html>
