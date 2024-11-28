<?php
// Включаем отображение всех ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once 'stockfish_engine.php';

try {
    if (!isset($_POST['position']) || !isset($_POST['depth'])) {
        throw new Exception('Missing required parameters');
    }

    $position = $_POST['position'];
    $depth = (int)$_POST['depth'];
    
    // Логируем входные данные для отладки
    error_log("Received position: " . $position);
    error_log("Received depth: " . $depth);
    
    // Создаем экземпляр движка с автоматическим определением пути
    $engine = new StockfishEngine();
    
    // Получаем лучший ход
    $bestMove = $engine->getBestMove($position, $depth);
    error_log("Engine returned move: " . $bestMove);
    
    if (empty($bestMove)) {
        throw new Exception('Engine did not return a move');
    }
    
    echo json_encode([
        'success' => true,
        'move' => $bestMove
    ]);
    
} catch (Exception $e) {
    error_log("Stockfish error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
