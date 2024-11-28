<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$current_page = 'play';
$tournament_id = isset($_GET['tournament_id']) ? (int)$_GET['tournament_id'] : 0;

try {
    // Проверяем, участвует ли пользователь в турнире
    $query = "SELECT t.*, tp.registration_date 
              FROM tournaments t 
              JOIN tournament_participants tp ON t.id = tp.tournament_id 
              WHERE t.id = :tournament_id AND tp.user_id = :user_id";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'tournament_id' => $tournament_id,
        'user_id' => $_SESSION['user_id']
    ]);
    $tournament = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tournament) {
        throw new Exception('Турнир не найден или вы не являетесь его участником');
    }

    if ($tournament['status'] !== 'ongoing') {
        throw new Exception('Турнир еще не начался или уже завершен');
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игра - Шахматный портал</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/chessboard.css">
    <!-- Подключаем chess.js для логики игры -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.10.3/chess.min.js"></script>
    <!-- Подключаем библиотеку для отрисовки доски -->
    <script src="https://chessboardjs.com/js/chessboard-1.0.0.min.js"></script>
    <link rel="stylesheet" href="https://chessboardjs.com/css/chessboard-1.0.0.min.css">
    <!-- jQuery (необходим для chessboard.js) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="container">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php else: ?>
            <div class="game-container">
                <div class="game-info">
                    <h1><?php echo htmlspecialchars($tournament['name']); ?></h1>
                    <div class="player-info">
                        <div class="player white">
                            <i class="fas fa-user"></i>
                            <span>Белые</span>
                            <div class="timer" id="whiteTimer">10:00</div>
                        </div>
                        <div class="player black">
                            <i class="fas fa-user"></i>
                            <span>Черные</span>
                            <div class="timer" id="blackTimer">10:00</div>
                        </div>
                    </div>
                </div>

                <div class="board-container">
                    <div id="board"></div>
                    <div class="game-controls">
                        <button id="startBtn" class="btn btn-primary">
                            <i class="fas fa-play"></i> Начать игру
                        </button>
                        <button id="resignBtn" class="btn btn-danger" disabled>
                            <i class="fas fa-flag"></i> Сдаться
                        </button>
                        <button id="drawBtn" class="btn btn-secondary" disabled>
                            <i class="fas fa-handshake"></i> Предложить ничью
                        </button>
                    </div>
                </div>

                <div class="game-sidebar">
                    <div class="move-list">
                        <h3>История ходов</h3>
                        <div id="pgn"></div>
                    </div>
                    <div class="chat-box">
                        <h3>Чат</h3>
                        <div class="chat-messages" id="chatMessages"></div>
                        <div class="chat-input">
                            <input type="text" id="chatInput" placeholder="Введите сообщение...">
                            <button class="btn btn-primary" id="sendMessage">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script>
        // Инициализация игры
        var board = null;
        var game = new Chess();
        var $status = $('#status');
        var $pgn = $('#pgn');
        
        // Функция для случайного хода (для демонстрации)
        function makeRandomMove() {
            var possibleMoves = game.moves();
            
            // Игра окончена
            if (possibleMoves.length === 0) return;
            
            var randomIdx = Math.floor(Math.random() * possibleMoves.length);
            game.move(possibleMoves[randomIdx]);
            board.position(game.fen());
            updateStatus();
        }
        
        // Обработчик хода
        function onDrop(source, target) {
            // Проверяем возможность хода
            var move = game.move({
                from: source,
                to: target,
                promotion: 'q' // Всегда превращаем в ферзя
            });
            
            // Недопустимый ход
            if (move === null) return 'snapback';
            
            // Обновляем статус
            updateStatus();
            
            // Делаем ответный ход компьютера
            window.setTimeout(makeRandomMove, 250);
        }
        
        // Обновление статуса игры
        function updateStatus() {
            var status = '';
            
            var moveColor = 'Белые';
            if (game.turn() === 'b') {
                moveColor = 'Черные';
            }
            
            // Проверяем мат
            if (game.in_checkmate()) {
                status = 'Игра окончена, ' + moveColor + ' получили мат.';
            }
            // Проверяем пат
            else if (game.in_draw()) {
                status = 'Игра окончена, ничья';
            }
            // Игра продолжается
            else {
                status = moveColor + ' ходят';
                
                // Проверяем шах
                if (game.in_check()) {
                    status += ', ' + moveColor + ' под шахом';
                }
            }
            
            $status.html(status);
            $pgn.html(game.pgn());
        }
        
        // Конфигурация доски
        var config = {
            draggable: true,
            position: 'start',
            onDrop: onDrop,
            pieceTheme: 'https://chessboardjs.com/img/chesspieces/wikipedia/{piece}.png'
        };
        
        // Инициализация доски
        board = Chessboard('board', config);
        
        // Обработчики кнопок
        $('#startBtn').on('click', function() {
            board.start();
            game.reset();
            updateStatus();
            
            // Активируем кнопки
            $('#resignBtn, #drawBtn').prop('disabled', false);
            $(this).prop('disabled', true);
        });
        
        $('#resignBtn').on('click', function() {
            if (confirm('Вы уверены, что хотите сдаться?')) {
                game.reset();
                board.start();
                $('#startBtn').prop('disabled', false);
                $('#resignBtn, #drawBtn').prop('disabled', true);
            }
        });
        
        $('#drawBtn').on('click', function() {
            if (confirm('Предложить ничью?')) {
                // Здесь будет логика для предложения ничьей
                alert('Противник отклонил предложение ничьей');
            }
        });
        
        // Обработчик чата
        $('#sendMessage').on('click', function() {
            var message = $('#chatInput').val().trim();
            if (message) {
                var chatMessages = $('#chatMessages');
                chatMessages.append('<div class="message"><strong>Вы:</strong> ' + message + '</div>');
                chatMessages.scrollTop(chatMessages[0].scrollHeight);
                $('#chatInput').val('');
            }
        });
        
        // Обработка Enter в чате
        $('#chatInput').on('keypress', function(e) {
            if (e.which == 13) {
                $('#sendMessage').click();
            }
        });
        
        $(window).resize(function() {
            board.resize();
        });
    </script>
</body>
</html>
