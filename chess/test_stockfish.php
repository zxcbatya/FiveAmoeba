<?php
require_once 'stockfish_engine.php';

try {
    // Создаем экземпляр движка с правильным путем
    $engine = new StockfishEngine();
    
    // Тестируем начальную позицию
    $startPosition = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1";
    
    // Получаем лучший ход
    echo "Calculating best move...\n";
    $bestMove = $engine->getBestMove($startPosition);
    
    echo "Engine started successfully!\n";
    echo "Best move from starting position: " . $bestMove . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
