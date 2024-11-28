<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');

// Установка текущей страницы для меню
$current_page = 'admin';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

// Получение статистики пользователей
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) as coaches FROM users WHERE is_coach = 1");
    $totalCoaches = $stmt->fetch()['coaches'];

    $stmt = $pdo->query("SELECT COUNT(*) as students FROM users WHERE is_coach = 0");
    $totalStudents = $stmt->fetch()['students'];

    // Получение последних зарегистрированных пользователей
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
    $recentUsers = $stmt->fetchAll();

} catch(PDOException $e) {
    $error = "Ошибка при получении статистики: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора - Шахматный портал</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include INCLUDES_PATH . '/header.php'; ?>

    <main class="container">
        <div class="page-header">
            <h1>
                <i class="fas fa-user-shield"></i>
                Панель администратора
            </h1>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="admin-stats">
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <h3>Всего пользователей</h3>
                <p><?php echo $totalUsers; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-chalkboard-teacher"></i>
                <h3>Тренеров</h3>
                <p><?php echo $totalCoaches; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-user-graduate"></i>
                <h3>Учеников</h3>
                <p><?php echo $totalStudents; ?></p>
            </div>
        </div>

        <div class="admin-actions">
            <a href="manage-users.php" class="btn">
                    <i class="fas fa-plus"></i> Управление пользователями
            </a>
            <a href="create-tournament.php" class="btn">
                    <i class="fas fa-plus"></i> Создать турнир
            </a>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
