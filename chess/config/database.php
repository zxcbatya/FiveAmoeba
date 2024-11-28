<?php
$host = 'localhost';
$dbname = 'chess_portal';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Создаем базу данных, если она не существует
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    $pdo->exec($sql);
    
    // Подключаемся к созданной базе данных
    $pdo->exec("use $dbname");

    // Создаем таблицу users, если она не существует
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        location VARCHAR(100),
        municipality VARCHAR(100),
        district VARCHAR(100),
        sports_rank VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_coach BOOLEAN DEFAULT 0,
        education TEXT,
        experience_years INT,
        achievements TEXT,
        teaching_approach TEXT,
        specialization VARCHAR(255),
        certificates TEXT,
        contact_phone VARCHAR(20),
        available_hours TEXT,
        hourly_rate DECIMAL(10,2),
        rating INT DEFAULT 1200
    )";
    
    $pdo->exec($sql);

    // Создаем таблицу для связи тренеров и учеников
    $sql = "CREATE TABLE IF NOT EXISTS coach_students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        coach_id INT NOT NULL,
        student_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (coach_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_coach_student (coach_id, student_id)
    )";
    
    $pdo->exec($sql);

    // Создаем таблицу для игр и результатов
    $sql = "CREATE TABLE IF NOT EXISTS games (
        id INT AUTO_INCREMENT PRIMARY KEY,
        player_id INT NOT NULL,
        opponent_name VARCHAR(100),
        result ENUM('win', 'loss', 'draw'),
        rating_change INT NOT NULL,
        date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (player_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);

    // Проверка подключения
    echo "<!-- Database connection successful -->\n";
} catch(PDOException $e) {
    // Выводим ошибку в HTML-комментарий для отладки
    echo "<!-- Database connection failed: " . $e->getMessage() . " -->\n";
    die("Connection failed: " . $e->getMessage());
}
?>
