<?php

class StockfishEngine {
    private $process;
    private $descriptors;
    private $pipes;
    
    public function __construct($pathToStockfish = null) {
        if ($pathToStockfish === null) {
            // Определяем путь относительно текущего файла
            $pathToStockfish = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'engines' . DIRECTORY_SEPARATOR . 'stockfish.exe';
        }
        
        $this->descriptors = array(
            0 => array("pipe", "r"),  // stdin
            1 => array("pipe", "w"),  // stdout
            2 => array("pipe", "w")   // stderr
        );
        
        // Проверяем существование файла
        if (!file_exists($pathToStockfish)) {
            throw new Exception("Stockfish executable not found at: " . $pathToStockfish . 
                              ". Please make sure to place stockfish.exe in the 'engines' directory.");
        }
        
        // Запускаем процесс
        $this->process = proc_open($pathToStockfish, $this->descriptors, $pipes);
        
        if (!is_resource($this->process)) {
            throw new Exception("Failed to start Stockfish engine");
        }
        
        $this->pipes = $pipes;
        
        // Устанавливаем неблокирующий режим для pipe
        stream_set_blocking($this->pipes[1], false);
        
        // Инициализация движка
        $this->sendCommand("uci");
        $response = $this->waitForResponse("uciok");
        if (strpos($response, 'uciok') === false) {
            throw new Exception("Failed to initialize Stockfish: UCI initialization failed");
        }
        
        $this->sendCommand("isready");
        $response = $this->waitForResponse("readyok");
        if (strpos($response, 'readyok') === false) {
            throw new Exception("Failed to initialize Stockfish: Engine not ready");
        }
    }
    
    private function waitForResponse($waitFor, $timeout = 5) {
        $output = "";
        $startTime = time();
        
        while (true) {
            $line = fgets($this->pipes[1]);
            if ($line !== false) {
                $output .= $line;
                if (strpos($line, $waitFor) !== false) {
                    return $output;
                }
            }
            
            if (time() - $startTime > $timeout) {
                throw new Exception("Timeout waiting for Stockfish response");
            }
            
            usleep(10000); // Небольшая пауза чтобы не нагружать процессор
        }
    }
    
    public function sendCommand($command) {
        if (!is_resource($this->pipes[0])) {
            throw new Exception("Stockfish process is not running");
        }
        
        fwrite($this->pipes[0], $command . "\n");
        fflush($this->pipes[0]);
    }
    
    public function getBestMove($position, $level) {
        // Настройки для разных уровней сложности
        $settings = [
            1 => [ // Начинающий (очень легкий)
                'depth' => 1,
                'skill' => 0,
                'time' => 50,
                'multipv' => 1,
                'eval_randomization' => 2000 // Добавляем случайность в оценку позиции
            ],
            2 => [ // Любитель
                'depth' => 5,
                'skill' => 5,
                'time' => 300,
                'multipv' => 1
            ],
            3 => [ // Средний
                'depth' => 8,
                'skill' => 10,
                'time' => 500,
                'multipv' => 2
            ],
            4 => [ // Продвинутый
                'depth' => 12,
                'skill' => 15,
                'time' => 1000,
                'multipv' => 2
            ],
            5 => [ // Эксперт
                'depth' => 15,
                'skill' => 20,
                'time' => 2000,
                'multipv' => 3
            ]
        ];
        
        // Получаем настройки для выбранного уровня
        $level = max(1, min(5, (int)$level));
        $config = $settings[$level];
        
        // Устанавливаем настройки движка
        $this->sendCommand("setoption name Skill Level value " . $config['skill']);
        $this->sendCommand("setoption name MultiPV value " . $config['multipv']);
        
        // Для первого уровня добавляем случайность
        if ($level === 1) {
            $this->sendCommand("setoption name Contempt value 0");
            $this->sendCommand("setoption name UCI_LimitStrength value true");
            $this->sendCommand("setoption name UCI_Elo value 500");
        } else {
            $this->sendCommand("setoption name UCI_LimitStrength value false");
        }
        
        // Устанавливаем позицию
        $this->sendCommand("position fen " . $position);
        
        // Запускаем поиск с ограничением по времени и глубине
        $this->sendCommand("go depth " . $config['depth'] . " movetime " . $config['time']);
        
        // Ждем лучший ход
        $bestMove = "";
        $startTime = microtime(true);
        while (true) {
            $line = fgets($this->pipes[1]);
            if ($line === false) continue;
            
            // Если находим лучший ход
            if (preg_match('/^bestmove\s+([a-h][1-8][a-h][1-8][qrbn]?)/', trim($line), $matches)) {
                $bestMove = $matches[1];
                break;
            }
            
            // Таймаут 30 секунд
            if (microtime(true) - $startTime > 30) {
                throw new Exception("Timeout waiting for engine response");
            }
        }
        
        return $bestMove;
    }
    
    public function __destruct() {
        if (is_resource($this->process)) {
            $this->sendCommand("quit");
            
            foreach ($this->pipes as $pipe) {
                if (is_resource($pipe)) {
                    fclose($pipe);
                }
            }
            
            proc_close($this->process);
        }
    }
}

// Пример использования:
/*
try {
    $engine = new StockfishEngine();
    $fen = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1"; // начальная позиция
    $bestMove = $engine->getBestMove($fen, 3);
    echo "Best move: " . $bestMove;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
*/
