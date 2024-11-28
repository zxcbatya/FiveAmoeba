<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['test_id'])) {
    header("Location: tests.php");
    exit();
}

$test_id = $_POST['test_id'];
$user_id = $_SESSION['user_id'];
$start_time = $_POST['start_time'];
$time_spent = time() - $start_time;

try {
    // Начало транзакции
    $pdo->beginTransaction();

    // Получаем информацию о тесте и вопросах
    $stmt = $pdo->prepare("
        SELECT q.id, q.points, a.id as correct_answer_id
        FROM test_questions q
        JOIN question_answers a ON q.id = a.question_id
        WHERE q.test_id = ? AND a.is_correct = 1
    ");
    $stmt->execute([$test_id]);
    $correct_answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Подсчет баллов
    $total_points = 0;
    $earned_points = 0;
    $answers_data = [];

    foreach ($correct_answers as $answer) {
        $total_points += $answer['points'];
        $question_id = $answer['id'];
        
        if (isset($_POST['question'][$question_id]) && $_POST['question'][$question_id] == $answer['correct_answer_id']) {
            $earned_points += $answer['points'];
        }
    }

    // Вычисление процента правильных ответов
    $score = round(($earned_points / $total_points) * 100);

    // Сохранение результатов
    $stmt = $pdo->prepare("
        INSERT INTO test_results (user_id, test_id, score, max_score, time_spent)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $test_id, $score, $total_points, $time_spent]);
    $result_id = $pdo->lastInsertId();

    // Подтверждение транзакции
    $pdo->commit();

    // Сохраняем результат в сессии для отображения
    $_SESSION['test_result'] = [
        'score' => $score,
        'time_spent' => $time_spent,
        'total_points' => $total_points,
        'earned_points' => $earned_points
    ];

    header("Location: test-result.php?id=" . $result_id);
    exit();

} catch (PDOException $e) {
    // Откат транзакции в случае ошибки
    $pdo->rollBack();
    die("Ошибка при обработке результатов теста: " . $e->getMessage());
}
