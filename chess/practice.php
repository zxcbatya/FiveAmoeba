<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$current_page = 'practice';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Практика - Шахматный портал</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- jQuery (необходим для chessboard.js) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Подключаем chess.js для логики игры -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.10.3/chess.min.js"></script>
    <!-- Подключаем библиотеку для отрисовки доски -->
    <link rel="stylesheet" href="https://unpkg.com/@chrisoakman/chessboardjs@1.0.0/dist/chessboard-1.0.0.min.css">
    <script src="https://unpkg.com/@chrisoakman/chessboardjs@1.0.0/dist/chessboard-1.0.0.min.js"></script>
    <!-- Наши стили -->
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="container">
        <div class="practice-section">
            <h1>Практика</h1>
            
            <div class="practice-modes">
                <!-- Режим игры с компьютером -->
                <div class="practice-mode">
                    <h2>Игра с компьютером</h2>
                    <div class="chessboard-container">
                        <div id="board1"></div>
                        <div class="game-controls">
                            <div class="difficulty-controls">
                                <label for="difficulty">Сложность:</label>
                                <select id="difficulty">
                                    <option value="1">Легкий</option>
                                    <option value="2">Средний</option>
                                    <option value="3">Сложный</option>
                                </select>
                            </div>
                            <button id="startBtn1" class="btn btn-primary">
                                <i class="fas fa-play"></i> Начать игру
                            </button>
                            <button id="undoBtn1" class="btn btn-secondary" disabled>
                                <i class="fas fa-undo"></i> Отменить ход
                            </button>
                        </div>
                        <div id="status1" class="game-status"></div>
                    </div>
                </div>

                <!-- Режим анализа позиции -->
                <div class="practice-mode">
                    <h2>Анализ позиции</h2>
                    <div class="chessboard-container">
                        <div id="board2"></div>
                        <div class="game-controls">
                            <button id="clearBtn" class="btn btn-secondary">
                                <i class="fas fa-trash"></i> Очистить доску
                            </button>
                            <button id="analyzeBtn" class="btn btn-primary">
                                <i class="fas fa-search"></i> Анализировать
                            </button>
                            <button id="exportBtn" class="btn btn-secondary">
                                <i class="fas fa-download"></i> Экспорт FEN
                            </button>
                        </div>
                        <div id="analysis" class="analysis-results"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script>
        // Инициализация первой доски (игра с компьютером)
        var board1 = null;
        var game1 = new Chess();
        var $status1 = $('#status1');

        function onDragStart1(source, piece, position, orientation) {
            if (game1.game_over()) return false;
            if ((game1.turn() === 'w' && piece.search(/^b/) !== -1) ||
                (game1.turn() === 'b' && piece.search(/^w/) !== -1)) {
                return false;
            }
        }

        function onDrop1(source, target) {
            var move = game1.move({
                from: source,
                to: target,
                promotion: 'q'
            });

            if (move === null) return 'snapback';
            updateStatus1();
            
            // Ход компьютера
            window.setTimeout(makeRandomMove1, 250);
        }

        function onSnapEnd1() {
            board1.position(game1.fen());
        }

        function updateStatus1() {
            var status = '';
            var moveColor = game1.turn() === 'b' ? 'Черные' : 'Белые';

            if (game1.in_checkmate()) {
                status = 'Игра окончена, ' + moveColor + ' получили мат.';
            } else if (game1.in_draw()) {
                status = 'Игра окончена, ничья';
            } else {
                status = moveColor + ' ходят';
                if (game1.in_check()) {
                    status += ', ' + moveColor + ' под шахом';
                }
            }

            $status1.html(status);
        }

        function makeRandomMove1() {
            var possibleMoves = game1.moves();
            if (possibleMoves.length === 0) return;

            var randomIdx = Math.floor(Math.random() * possibleMoves.length);
            game1.move(possibleMoves[randomIdx]);
            board1.position(game1.fen());
            updateStatus1();
        }

        // Инициализация второй доски (анализ позиции)
        var board2 = null;
        var game2 = new Chess();

        function onDragStart2(source, piece, position, orientation) {
            return true; // Разрешаем все ходы в режиме анализа
        }

        function onDrop2(source, target) {
            var move = game2.move({
                from: source,
                to: target,
                promotion: 'q'
            });

            if (move === null) return 'snapback';
            updateAnalysis();
        }

        function onSnapEnd2() {
            board2.position(game2.fen());
        }

        function updateAnalysis() {
            var analysis = '';
            if (game2.in_check()) {
                analysis += 'Шах! ';
            }
            if (game2.in_checkmate()) {
                analysis += 'Мат! ';
            }
            if (game2.in_stalemate()) {
                analysis += 'Пат! ';
            }
            if (game2.in_threefold_repetition()) {
                analysis += 'Троекратное повторение позиции. ';
            }
            if (game2.insufficient_material()) {
                analysis += 'Недостаточно материала для мата. ';
            }

            $('#analysis').html(analysis || 'Нормальная позиция');
        }

        // Конфигурация досок
        var config1 = {
            draggable: true,
            position: 'start',
            onDragStart: onDragStart1,
            onDrop: onDrop1,
            onSnapEnd: onSnapEnd1,
            pieceTheme: 'https://chessboardjs.com/img/chesspieces/wikipedia/{piece}.png'
        };

        var config2 = {
            draggable: true,
            position: 'start',
            onDragStart: onDragStart2,
            onDrop: onDrop2,
            onSnapEnd: onSnapEnd2,
            pieceTheme: 'https://chessboardjs.com/img/chesspieces/wikipedia/{piece}.png'
        };

        // Инициализация досок
        board1 = Chessboard('board1', config1);
        board2 = Chessboard('board2', config2);

        // Обработчики кнопок
        $('#startBtn1').on('click', function() {
            game1.reset();
            board1.start();
            updateStatus1();
        });

        $('#undoBtn1').on('click', function() {
            game1.undo();
            game1.undo(); // Отменяем ход компьютера тоже
            board1.position(game1.fen());
            updateStatus1();
        });

        $('#clearBtn').on('click', function() {
            game2.reset();
            board2.start();
            updateAnalysis();
        });

        $('#analyzeBtn').on('click', function() {
            updateAnalysis();
        });

        $('#exportBtn').on('click', function() {
            alert(game2.fen());
        });

        // Начальное обновление статусов
        updateStatus1();
        updateAnalysis();

        // Адаптивность при изменении размера окна
        $(window).resize(function() {
            board1.resize();
            board2.resize();
        });
    </script>
</body>
</html>
