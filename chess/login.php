<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
// Define base path constants
define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $is_admin = isset($_POST['is_admin']) ? true : false;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_coach'] = $user['is_coach'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            // Перенаправляем на соответствующую страницу в зависимости от роли
            if ($is_admin && $user['is_admin'] == 1) {
                header("Location: dashboard-admin.php");
            } elseif ($user['is_coach']) {
                header("Location: dashboard-coach.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $error = "Неверный email или пароль";
        }
    } catch(PDOException $e) {
        $error = "Ошибка при входе: " . $e->getMessage();
    }
}

$current_page = 'login';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - Шахматный портал</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tournaments.css">
</head>
<body>

    <?php include INCLUDES_PATH . '/header.php'; ?>

    <main class="container">
        <div class="page-header">
            <h1>
                <i class="fas fa-sign-in-alt"></i>
                Вход в систему
            </h1>
        </div>

        <div class="content-card">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="form">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email
                    </label>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           placeholder="Введите ваш email">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Пароль
                    </label>
                    <div class="password-input">
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               required 
                               placeholder="Введите ваш пароль">
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" id="is_admin" name="is_admin">
                    <label for="is_admin">
                        <i class="fas fa-user-shield"></i>
                        Войти как администратор
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">
                        <i class="fas fa-sign-in-alt"></i>
                        Войти
                    </button>
                    <a href="register-choice.php" class="btn btn-secondary">
                        <i class="fas fa-user-plus"></i>
                        Регистрация
                    </a>
                </div>
            </form>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const icon = document.querySelector('.toggle-password i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    </script>
</body>
</html>
