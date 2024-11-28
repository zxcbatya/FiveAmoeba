<?php
// Подключение к базе данных
require_once __DIR__ . '/config/database.php';

try {
    // Чтение SQL-файла
    $sql = file_get_contents(__DIR__ . '/sql/create_tournaments.sql');
    
    // Выполнение SQL-запросов
    $pdo->exec($sql);
    
    echo "База данных успешно инициализирована!\n";
    echo "Таблицы созданы и заполнены тестовыми данными.\n";
    
} catch (PDOException $e) {
    die("Ошибка при инициализации базы данных: " . $e->getMessage() . "\n");
}
