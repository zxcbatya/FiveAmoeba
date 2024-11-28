<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Проверяем, есть ли ID урока
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: training.php");
    exit();
}

$lesson_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Получаем информацию об уроке
$query = "SELECT * FROM training_materials WHERE id = ?";
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute([$lesson_id]);
    $lesson = $stmt->fetch();
    
    if (!$lesson) {
        header("Location: training.php");
        exit();
    }
} catch(PDOException $e) {
    header("Location: training.php");
    exit();
}

// Получаем задачи для урока и прогресс пользователя
$query = "SELECT e.*, COALESCE(p.completed, 0) as completed 
          FROM lesson_exercises e 
          LEFT JOIN user_progress p ON e.id = p.exercise_id AND p.user_id = ? 
          WHERE e.lesson_id = ? 
          ORDER BY e.order_number ASC";
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $lesson_id]);
    $exercises = $stmt->fetchAll();
} catch(PDOException $e) {
    $exercises = [];
}

// Обработка AJAX запроса на сохранение прогресса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_progress') {
    header('Content-Type: application/json');
    
    if (!isset($_POST['exercise_id']) || !isset($_POST['completed'])) {
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        exit;
    }
    
    try {
        // Проверяем существует ли запись
        $query = "SELECT id FROM user_progress WHERE user_id = ? AND exercise_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id, $_POST['exercise_id']]);
        $exists = $stmt->fetch();
        
        if ($exists) {
            // Обновляем существующую запись
            $query = "UPDATE user_progress SET completed = ?, completed_at = NOW() 
                     WHERE user_id = ? AND exercise_id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$_POST['completed'], $user_id, $_POST['exercise_id']]);
        } else {
            // Создаем новую запись
            $query = "INSERT INTO user_progress (user_id, exercise_id, completed, completed_at) 
                     VALUES (?, ?, ?, NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$user_id, $_POST['exercise_id'], $_POST['completed']]);
        }
        
        echo json_encode(['success' => true]);
        exit;
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }
}

$current_page = 'training';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lesson['title']); ?> - Шахматный портал</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chessboard-js/1.0.0/chessboard-1.0.0.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.10.3/chess.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chessboard-js/1.0.0/chessboard-1.0.0.min.js"></script>
    <style>
        .game-container {
            display: flex;
            gap: 30px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .board-container {
            flex: 0 0 600px;
        }
        .game-controls {
            flex: 1;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .control-group {
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .control-group h3 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 1.2em;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .control-group h3 i {
            color: #3498db;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn i {
            font-size: 1.1em;
        }
        .btn-primary {
            background: #2196f3;
            color: white;
        }
        .btn-secondary {
            background: #78909c;
            color: white;
        }
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        .move-history {
            max-height: 200px;
            overflow-y: auto;
            padding: 10px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .move-history p {
            margin: 5px 0;
            padding: 8px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }
        .move-history p:last-child {
            border-bottom: none;
            background: #f8f9fa;
            font-weight: 500;
        }
        #engineThinking {
            display: none;
            color: #1976d2;
            font-style: italic;
            padding: 10px;
            background: #e3f2fd;
            border-radius: 4px;
            margin-top: 10px;
            text-align: center;
        }
        .exercise-status {
            padding: 12px;
            border-radius: 4px;
            margin: 15px 0;
            font-weight: 500;
        }
        .exercise-status.success {
            background: #e8f5e9;
            border: 1px solid #c8e6c9;
            color: #2e7d32;
        }
        .exercise-status.error {
            background: #ffebee;
            border: 1px solid #ffcdd2;
            color: #c62828;
        }
        .exercise-hint {
            padding: 12px;
            background: #fff3e0;
            border: 1px solid #ffe0b2;
            border-radius: 4px;
            margin: 15px 0;
            display: none;
        }
        .progress-container {
            margin: 20px 0;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-bar {
            height: 20px;
            background: #2196f3;
            width: 0;
            transition: width 0.3s ease;
        }
        .progress-text {
            text-align: center;
            margin-top: 5px;
            color: #6c757d;
        }
    </style>
    <script>
    $(document).ready(function() {
        let board = null;
        let game = new Chess();
        let currentExercise = 0;
        let moveHistory = [];

        // Получаем упражнения из PHP
        const exercises = <?php echo json_encode($exercises); ?>;

        function onDragStart(source, piece, position, orientation) {
            if (game.game_over()) return false;
            if ((game.turn() === 'w' && piece.search(/^b/) !== -1) ||
                (game.turn() === 'b' && piece.search(/^w/) !== -1)) {
                return false;
            }
            return true;
        }

        function addMoveToHistory(player, move) {
            moveHistory.push({ player, move });
            const $history = $('.move-history');
            $history.append(`<p><span>${player}:</span> <span>${move}</span></p>`);
            $history.scrollTop($history[0].scrollHeight);
        }

        function onDrop(source, target) {
            try {
                const move = game.move({
                    from: source,
                    to: target,
                    promotion: 'q'
                });

                if (move === null) return 'snapback';

                addMoveToHistory('Вы', move.san);

                const exercise = exercises[currentExercise];
                if (exercise.solution === move.san) {
                    saveProgress(exercise.id, 1).then(response => {
                        if (response.success) {
                            $('.exercise-status')
                                .removeClass('error')
                                .addClass('success')
                                .html('<i class="fas fa-check-circle"></i> Правильно! Задание выполнено.');
                            
                            exercises[currentExercise].completed = 1;
                            $('.next-exercise').show();
                            updateProgress();
                        }
                    });
                } else {
                    $('.exercise-status')
                        .removeClass('success')
                        .addClass('error')
                        .html('<i class="fas fa-times-circle"></i> Попробуйте другой ход.');
                    
                    // Запрашиваем подсказку от Stockfish
                    requestStockfishHint();
                    
                    game.undo();
                    return 'snapback';
                }
            } catch (e) {
                console.error('Error making move:', e);
                return 'snapback';
            }
        }

        function requestStockfishHint() {
            $('#engineThinking').show();
            $.ajax({
                url: 'stockfish_move.php',
                method: 'POST',
                data: {
                    fen: game.fen(),
                    depth: 15
                },
                success: function(response) {
                    $('#engineThinking').hide();
                    if (response.success) {
                        $('.exercise-hint')
                            .html(`<i class="fas fa-lightbulb"></i> Подсказка: Stockfish рекомендует ход ${response.move}`)
                            .slideDown();
                    }
                },
                error: function() {
                    $('#engineThinking').hide();
                    $('.exercise-hint')
                        .html('<i class="fas fa-exclamation-circle"></i> Не удалось получить подсказку')
                        .slideDown();
                }
            });
        }

        function saveProgress(exerciseId, completed) {
            return $.ajax({
                url: 'lesson.php?id=<?php echo $lesson_id; ?>',
                method: 'POST',
                data: {
                    action: 'save_progress',
                    exercise_id: exerciseId,
                    completed: completed
                }
            });
        }

        function updateProgress() {
            const completedExercises = exercises.filter(ex => ex.completed).length;
            const totalExercises = exercises.length;
            const progressPercent = Math.round((completedExercises / totalExercises) * 100);
            
            $('.progress-bar').css('width', progressPercent + '%');
            $('.progress-text').text(`Выполнено ${completedExercises} из ${totalExercises} заданий`);
            
            if (completedExercises === totalExercises) {
                $('.completion-message').show();
            }
        }

        function loadExercise(index) {
            if (index >= exercises.length) {
                $('.exercise-container').hide();
                $('.completion-message').show();
                return;
            }

            const exercise = exercises[index];
            currentExercise = index;
            
            game = new Chess();
            if (exercise.position) {
                game.load(exercise.position);
            }

            moveHistory = [];
            $('.move-history').empty();

            $('.exercise-title').text(exercise.title);
            $('.exercise-description').text(exercise.description);
            $('.hint-text').text(exercise.hint);
            $('.exercise-hint').hide();
            $('.exercise-status').removeClass('success error').empty();
            $('.next-exercise').toggle(exercise.completed === 1);
            $('.current-exercise').text(index + 1);
            $('.total-exercises').text(exercises.length);

            if (board) {
                board.position(exercise.position || 'start');
            }
            
            if (exercise.completed) {
                $('.exercise-status')
                    .removeClass('error')
                    .addClass('success')
                    .html('<i class="fas fa-check-circle"></i> Задание уже выполнено');
            }
        }

        const config = {
            draggable: true,
            position: 'start',
            pieceTheme: 'https://chessboardjs.com/img/chesspieces/wikipedia/{piece}.png',
            onDragStart: onDragStart,
            onDrop: onDrop,
            onSnapEnd: () => board.position(game.fen())
        };

        board = Chessboard('board', config);
        $(window).resize(() => board.resize());

        $('.show-hint').click(() => $('.exercise-hint').slideToggle());
        $('.reset-exercise').click(() => loadExercise(currentExercise));
        $('.next-exercise').click(() => loadExercise(currentExercise + 1));
        $('.flip-board').click(() => board.flip());
        $('.request-hint').click(() => requestStockfishHint());
        
        $('.tab-button').click(function() {
            const tab = $(this).data('tab');
            $('.tab-button').removeClass('active');
            $(this).addClass('active');
            $('.tab-content').hide();
            $(`#${tab}-content`).show();
            if (tab === 'practice' && board) {
                board.resize();
            }
        });

        // Добавляем обработчик для кнопки "Перейти к практике"
        $('.start-practice').click(function() {
            $('.tab-button[data-tab="practice"]').addClass('active').siblings().removeClass('active');
            $('.tab-content').hide();
            $('#practice-content').show();
            if (board) {
                board.resize();
            }
        });

        loadExercise(0);
        updateProgress();
    });
    </script>
</head>
<body>
    <?php include INCLUDES_PATH . '/header.php'; ?>
    
    <main class="container">
        <div class="container-training">
            <a class="page-title-training" href="training.php">
                <i class="fas fa-graduation-cap"></i>
                Тренировки
            </a>
        </div>

        <div class="lesson-content">
            <div class="lesson-tabs">
                <button class="tab-button active" data-tab="theory">Теория</button>
                <button class="tab-button" data-tab="practice">Практика</button>
            </div>

            <div class="tab-content" id="theory-content">
                <h1 class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></h1>
                <div class="lesson-text">
                    <?php echo $lesson['content']; ?>
                </div>
                <button class="btn  start-practice">
                    <i class="fas fa-chess"></i>
                    Практика
                </button>
            </div>

            <div class="tab-content" id="practice-content" style="display: none;">
                <div class="game-container">
                    <div class="board-container">
                        <div id="board"></div>
                    </div>
                    
                    <div class="game-controls">
                        <div class="control-group">
                            <h3>
                                <i class="fas fa-tasks"></i>
                                Задание <span class="current-exercise">1</span> из <span class="total-exercises">0</span>
                            </h3>
                            <div class="exercise-title"></div>
                            <div class="exercise-description"></div>
                            <div class="exercise-status"></div>
                            <div class="exercise-hint"></div>
                            <div id="engineThinking">
                                <i class="fas fa-cog fa-spin"></i> Анализ позиции...
                            </div>
                        </div>

                        <div class="control-group">
                            <h3>
                                <i class="fas fa-chess-board"></i>
                                Управление доской
                            </h3>
                            <div class="btn-group">
                                <button class="btn btn-secondary flip-board">
                                    <i class="fas fa-retweet"></i>
                                    Перевернуть доску
                                </button>
                                <button class="btn btn-secondary reset-exercise">
                                    <i class="fas fa-undo"></i>
                                    Сбросить
                                </button>
                            </div>
                            <button class="btn  request-hint">
                                <i class="fas fa-lightbulb"></i>
                                Получить подсказку
                            </button>
                            <button class="btn btn-primary next-exercise" style="display: none;">
                                <i class="fas fa-forward"></i>
                                Следующее задание
                            </button>
                        </div>

                        <div class="control-group">
                            <h3>
                                <i class="fas fa-history"></i>
                                История ходов
                            </h3>
                            <div class="move-history"></div>
                        </div>

                        <div class="progress-container">
                            <div class="progress-bar"></div>
                            <div class="progress-text"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include INCLUDES_PATH . '/footer.php'; ?>
</body>
</html>
