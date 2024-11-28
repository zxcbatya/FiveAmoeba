<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Получение данных пользователя
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $school = trim($_POST['school']);
    $sports_rank = trim($_POST['sports_rank']);
    $username = trim($_POST['username']);

    // Проверка уникальности email и username
    $check_query = "SELECT COUNT(*) FROM users WHERE (email = ? OR username = ?) AND id != ?";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->execute([$email, $username, $user_id]);
    $exists = $check_stmt->fetchColumn();

    if ($exists) {
        $error_message = 'Пользователь с таким email или никнеймом уже существует';
    } else {
        // Обновление профиля
        $update_query = "UPDATE users SET 
                        email = ?, 
                        full_name = ?, 
                        school = ?, 
                        sports_rank = ?,
                        username = ?
                        WHERE id = ?";
        
        try {
            $update_stmt = $pdo->prepare($update_query);
            $update_stmt->execute([$email, $full_name, $school, $sports_rank, $username, $user_id]);
            $success_message = 'Профиль успешно обновлен';
            
            // Обновляем данные пользователя для отображения
            $stmt = $pdo->prepare($query);
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        } catch (PDOException $e) {
            $error_message = 'Ошибка при обновлении профиля';
        }
    }
}

$current_page = 'dashboard';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование профиля - Шахматный портал</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tournaments.css">
</head>
<body>
    <?php include INCLUDES_PATH . './header.php'?>

    <main class="container">
        <div class="dashboard-header">
            <h1>Редактирование профиля</h1>
        </div>
        <div class="form-container">
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="profile-form">
                <div class="form-group">
                    <label for="username">Никнейм</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="full_name">ФИО</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="school">Школа</label>
                    <input type="text" id="school" name="school" value="<?php echo htmlspecialchars($user['school'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="sports_rank">Спортивный разряд</label>
                    <select id="sports_rank" name="sports_rank">
                        <option value="">Нет разряда</option>
                        <option value="3" <?php echo ($user['sports_rank'] ?? '') === '3' ? 'selected' : ''; ?>>3 разряд</option>
                        <option value="2" <?php echo ($user['sports_rank'] ?? '') === '2' ? 'selected' : ''; ?>>2 разряд</option>
                        <option value="1" <?php echo ($user['sports_rank'] ?? '') === '1' ? 'selected' : ''; ?>>1 разряд</option>
                        <option value="KMS" <?php echo ($user['sports_rank'] ?? '') === 'KMS' ? 'selected' : ''; ?>>КМС</option>
                        <option value="MS" <?php echo ($user['sports_rank'] ?? '') === 'MS' ? 'selected' : ''; ?>>МС</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">Сохранить изменения</button>
                    <a href="dashboard.php" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
