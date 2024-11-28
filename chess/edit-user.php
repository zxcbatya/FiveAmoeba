<?php
session_start();
define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');

// Include required files
require_once CONFIG_PATH . '/database.php';
require_once INCLUDES_PATH . '/functions.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: index.php");
    exit();
}

$error_message = '';
$success_message = '';

// Получение данных пользователя
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            header("Location: manage-users.php");
            exit();
        }
    } catch (PDOException $e) {
        $error_message = "Ошибка при получении данных пользователя: " . $e->getMessage();
    }
} else {
    header("Location: manage-users.php");
    exit();
}

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    $is_coach = isset($_POST['is_coach']) ? 1 : 0;
    $new_password = trim($_POST['new_password']);

    try {
        // Начало транзакции
        $pdo->beginTransaction();

        // Обновление основных данных
        if ($new_password) {
            // Если указан новый пароль, обновляем его вместе с остальными данными
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, is_admin = ?, is_coach = ?, password = ? WHERE id = ?");
            $stmt->execute([$username, $email, $is_admin, $is_coach, $password_hash, $user_id]);
        } else {
            // Если пароль не меняется, обновляем только остальные данные
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, is_admin = ?, is_coach = ? WHERE id = ?");
            $stmt->execute([$username, $email, $is_admin, $is_coach, $user_id]);
        }

        // Подтверждение транзакции
        $pdo->commit();

        $_SESSION['success_message'] = "Данные пользователя успешно обновлены";
        header("Location: manage-users.php");
        exit();

    } catch (PDOException $e) {
        // Откат транзакции в случае ошибки
        $pdo->rollBack();
        $error_message = "Ошибка при обновлении данных: " . $e->getMessage();
    }
}

$current_page = 'manage-users';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование пользователя - Шахматный портал</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="page-header">
            <h1><i class="fas fa-user-edit"></i> Редактирование пользователя</h1>
            <a href="manage-users.php" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Назад к списку
            </a>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="edit-user-form">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Имя пользователя:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="new_password">Новый пароль (оставьте пустым, чтобы не менять):</label>
                    <input type="password" id="new_password" name="new_password">
                </div>

                <div class="form-group checkboxes">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_admin" <?php echo $user['is_admin'] ? 'checked' : ''; ?>>
                        Администратор
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_coach" <?php echo $user['is_coach'] ? 'checked' : ''; ?>>
                        Тренер
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Сохранить изменения
                    </button>
                </div>
            </form>
        </div>
    </main>
    <?php include INCLUDES_PATH . '/footer.php'; ?>
</body>
</html>
