<?php
session_start();

define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');

// Include required files
require_once CONFIG_PATH . '/database.php';
require_once INCLUDES_PATH . '/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Игра';
$current_page = 'stockfish_test';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://unpkg.com/@chrisoakman/chessboardjs@1.0.0/dist/chessboard-1.0.0.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .page-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 30px 0;
            margin-bottom: 30px;
            color: white;
            text-align: center;
        }
        .page-header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 600;
        }
        .page-header p {
            margin: 10px 0 0;
            font-size: 1.1em;
            opacity: 0.9;
        }
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
        .radio-group {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        .radio-group label {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            padding: 8px 15px;
            background: #f8f9fa;
            border-radius: 4px;
            transition: all 0.2s;
        }
        .radio-group label:hover {
            background: #e9ecef;
        }
        .radio-group input[type="radio"]:checked + span {
            color: #2196f3;
            font-weight: 500;
        }
        .difficulty-select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 1em;
            cursor: pointer;
        }
        .difficulty-select:focus {
            border-color: #2196f3;
            outline: none;
            box-shadow: 0 0 0 2px rgba(33,150,243,0.2);
        }
        .status-message {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
            background: #e8f5e9;
            border: 1px solid #c8e6c9;
            font-weight: 500;
        }
        .error-message {
            background: #ffebee;
            border: 1px solid #ffcdd2;
            color: #c62828;
            display: none;
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
        .main-content {
            background: #f5f5f5;
            min-height: calc(100vh - 60px);
            padding: 20px 0;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="game-container">
                <div class="board-container">
                    <div id="board" style="width: 600px"></div>
                </div>
                
                <div class="game-controls">
                    <div class="control-group">
                        <h3><i class="fas fa-cog"></i> Настройки игры</h3>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="playerColor" value="white" checked>
                                <span><i class="fas fa-chess-pawn"></i> Белые</span>
                            </label>
                            <label>
                                <input type="radio" name="playerColor" value="black">
                                <span><i class="fas fa-chess-pawn" style="color: #333;"></i> Черные</span>
                            </label>
                        </div>
                        
                        <select id="depth" class="difficulty-select">
                            <option value="1">Уровень 1 (Начинающий)</option>
                            <option value="2">Уровень 2 (Любитель)</option>
                            <option value="3" selected>Уровень 3 (Средний)</option>
                            <option value="4">Уровень 4 (Продвинутый)</option>
                            <option value="5">Уровень 5 (Эксперт)</option>
                        </select>
                        
                        <div class="btn-group">
                            <button id="startBtn" class="btn ">
                                <i class="fas fa-play"></i> Начать новую игру
                            </button>
                            <button id="undoBtn" class="btn btn-secondary">
                                <i class="fas fa-undo"></i> Отменить ход
                            </button>
                        </div>
                    </div>
                    
                    <div class="control-group">
                        <h3><i class="fas fa-info-circle"></i> Статус игры</h3>
                        <div id="status" class="status-message">
                            Выберите цвет фигур и уровень сложности
                        </div>
                        <div id="error-message" class="status-message error-message"></div>
                        <div id="engineThinking">
                            <i class="fas fa-cog fa-spin"></i> Stockfish думает...
                        </div>
                    </div>
                    
                    <div class="control-group">
                        <h3><i class="fas fa-history"></i> История ходов</h3>
                        <div id="move-history" class="move-history"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.10.3/chess.min.js"></script>
    <script src="https://unpkg.com/@chrisoakman/chessboardjs@1.0.0/dist/chessboard-1.0.0.min.js"></script>
    <script>
        var board = null;
        var game = new Chess();
        var $status = $('#status');
        var $errorMessage = $('#error-message');
        var $engineThinking = $('#engineThinking');
        var $moveHistory = $('#move-history');
        var playerColor = 'white';
        
        function onDragStart(source, piece, position, orientation) {
            if (game.game_over()) return false;
            
            // разрешаем перемещение только своих фигур в свой ход
            if ((game.turn() === 'w' && piece.search(/^b/) !== -1) ||
                (game.turn() === 'b' && piece.search(/^w/) !== -1) ||
                (game.turn() !== playerColor.charAt(0))) {
                return false;
            }
        }
        
        function makeEngineMove() {
            if (game.game_over()) return;
            
            $engineThinking.show();
            $errorMessage.hide();
            
            var fen = game.fen();
            var depth = $('#depth').val();
            
            $.ajax({
                url: 'stockfish_move.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    position: fen,
                    depth: depth
                }
            })
            .done(function(response) {
                try {
                    if (response.success && response.move) {
                        game.move({
                            from: response.move.substring(0, 2),
                            to: response.move.substring(2, 4),
                            promotion: response.move.length === 5 ? response.move.substring(4, 5) : undefined
                        });
                        
                        board.position(game.fen());
                        updateStatus();
                        addMoveToHistory('Stockfish', response.move);
                    } else {
                        $errorMessage.html('Ошибка: ' + (response.error || 'Некорректный ответ от сервера')).show();
                    }
                } catch (e) {
                    $errorMessage.html('Ошибка: ' + e.message).show();
                }
            })
            .fail(function(xhr, status, error) {
                $errorMessage.html('Ошибка связи с сервером: ' + error).show();
            })
            .always(function() {
                $engineThinking.hide();
            });
        }
        
        function onDrop(source, target) {
            var move = game.move({
                from: source,
                to: target,
                promotion: 'q'
            });
            
            if (move === null) return 'snapback';
            
            updateStatus();
            addMoveToHistory('Игрок', source + target);
            
            // После хода игрока делаем ход компьютера
            window.setTimeout(makeEngineMove, 250);
        }
        
        function updateStatus() {
            var status = '';
            
            var moveColor = game.turn() === 'b' ? 'Черные' : 'Белые';
            
            if (game.in_checkmate()) {
                status = 'Игра окончена, ' + moveColor + ' получили мат.';
            } else if (game.in_draw()) {
                status = 'Игра окончена, ничья';
            } else {
                status = moveColor + ' ходят';
                if (game.in_check()) {
                    status += ', ' + moveColor + ' под шахом';
                }
            }
            
            $status.html(status);
        }
        
        function addMoveToHistory(player, move) {
            var moveNumber = Math.floor((game.history().length + 1) / 2);
            $moveHistory.prepend('<p>' + moveNumber + '. ' + player + ': ' + move + '</p>');
        }
        
        function startNewGame() {
            game.reset();
            board.start();
            $moveHistory.empty();
            $errorMessage.hide();
            updateStatus();
            
            playerColor = $('input[name="playerColor"]:checked').val();
            board.orientation(playerColor);
            
            if (playerColor === 'black') {
                window.setTimeout(makeEngineMove, 250);
            }
        }
        
        $(document).ready(function() {
            var config = {
                draggable: true,
                position: 'start',
                onDragStart: onDragStart,
                onDrop: onDrop,
                pieceTheme: 'https://chessboardjs.com/img/chesspieces/wikipedia/{piece}.png'
            };
            board = Chessboard('board', config);
            
            $('#startBtn').on('click', startNewGame);
            
            $('#undoBtn').on('click', function() {
                if (game.history().length >= 2) {
                    game.undo(); // отменяем ход компьютера
                    game.undo(); // отменяем ход игрока
                    board.position(game.fen());
                    updateStatus();
                }
            });
            
            updateStatus();
        });
    </script>
    <?php include INCLUDES_PATH . '/footer.php'; ?>
</body>
</html>
