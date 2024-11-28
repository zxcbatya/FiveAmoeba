<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Обработка формы создания турнира
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $format = trim($_POST['format']);
    $start_date = $_POST['start_date'];
    $start_time = $_POST['start_time'];
    $prize_fund = (int)$_POST['prize_fund'];
    $max_participants = (int)$_POST['max_participants'];
    $status = 'open'; // По умолчанию турнир открыт для регистрации

    // Валидация данных
    if (empty($name) || empty($format) || empty($start_date) || empty($start_time)) {
        $error_message = 'Пожалуйста, заполните все обязательные поля';
    } else {
        // Объединяем дату и время
        $start_datetime = date('Y-m-d H:i:s', strtotime("$start_date $start_time"));

        try {
            // Добавление турнира в базу данных
            $query = "INSERT INTO tournaments (name, description, format, start_date, prize_fund, max_participants, status, created_by) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$name, $description, $format, $start_datetime, $prize_fund, $max_participants, $status, $_SESSION['user_id']]);

            $success_message = 'Турнир успешно создан';
            
            // Перенаправление на страницу турниров после успешного создания
            header("Location: tournaments.php?success=created");
            exit();
        } catch (PDOException $e) {
            $error_message = 'Произошла ошибка при создании турнира';
        }
    }
}

$current_page = 'tournaments';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание турнира - Шахматный портал</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tournaments.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="page-header">
            <h1>
                <i class="fas fa-trophy"></i>
                Создание нового турнира
            </h1>
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

            <form method="POST" class="tournament-form">
                <div class="form-group">
                    <label for="name">Название турнира *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="description">Описание турнира</label>
                    <textarea id="description" name="description" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="format">Формат турнира *</label>
                    <select id="format" name="format" required>
                        <option value="">Выберите формат</option>
                        <option value="Классические шахматы">Классические шахматы</option>
                        <option value="Быстрые шахматы">Быстрые шахматы</option>
                        <option value="Блиц">Блиц</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date">Дата начала *</label>
                        <input type="date" id="start_date" name="start_date" required 
                               min="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="start_time">Время начала *</label>
                        <input type="time" id="start_time" name="start_time" required
                               value="<?php echo isset($_POST['start_time']) ? htmlspecialchars($_POST['start_time']) : ''; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="prize_fund">Призовой фонд (₽)</label>
                        <input type="number" id="prize_fund" name="prize_fund" min="0" step="100"
                               value="<?php echo isset($_POST['prize_fund']) ? htmlspecialchars($_POST['prize_fund']) : '0'; ?>">
                    </div>

                    <div class="form-group">
                        <label for="max_participants">Максимум участников</label>
                        <input type="number" id="max_participants" name="max_participants" min="2" max="100"
                               value="<?php echo isset($_POST['max_participants']) ? htmlspecialchars($_POST['max_participants']) : '32'; ?>">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Создать турнир
                    </button>
                    <a href="tournaments.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Отмена
                    </a>
                </div>
            </form>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Устанавливаем минимальную дату
        const startDateInput = document.getElementById('start_date');
        const today = new Date().toISOString().split('T')[0];
        startDateInput.min = today;

        // Восстанавливаем выбранный формат после отправки формы
        const formatSelect = document.getElementById('format');
        const savedFormat = '<?php echo isset($_POST['format']) ? htmlspecialchars($_POST['format']) : ''; ?>';
        if (savedFormat) {
            formatSelect.value = savedFormat;
        }
    </script>
</body>
</html>
