<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Проверяем, не пытается ли админ удалить сам себя
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error_message'] = "Вы не можете удалить свой собственный аккаунт";
        header("Location: manage-users.php");
        exit();
    }

    try {
        // Начало транзакции
        $pdo->beginTransaction();

        // Удаляем пользователя
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);

        // Подтверждение транзакции
        $pdo->commit();

        $_SESSION['success_message'] = "Пользователь успешно удален";
    } catch (PDOException $e) {
        // Откат транзакции в случае ошибки
        $pdo->rollBack();
        $_SESSION['error_message'] = "Ошибка при удалении пользователя: " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "Не указан ID пользователя";
}

header("Location: manage-users.php");
exit();
