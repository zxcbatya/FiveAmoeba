<?php
try {
    // Подключение к MySQL без выбора базы данных
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Создание базы данных
    $pdo->exec("CREATE DATABASE IF NOT EXISTS chess_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "База данных chess_portal успешно создана или уже существует\n";
    
    // Выбор базы данных
    $pdo->exec("USE chess_portal");
    
    // Чтение и выполнение SQL-скрипта
    $sql = file_get_contents(__DIR__ . '/sql/create_tournaments.sql');
    $pdo->exec($sql);
    
    echo "Таблицы успешно созданы и заполнены тестовыми данными\n";
    
} catch(PDOException $e) {
    die("Ошибка: " . $e->getMessage() . "\n");
}
