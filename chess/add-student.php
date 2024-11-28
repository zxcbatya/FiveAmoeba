<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');

// Проверка авторизации и роли тренера
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_coach']) || !$_SESSION['is_coach']) {
    header("Location: login.php");
    exit();
}

$coach_id = $_SESSION['user_id'];
$success = $error = '';

// Обработка добавления ученика
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_email = $_POST['student_email'] ?? '';
    
    try {
        // Поиск ученика по email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND is_coach = 0");
        $stmt->execute([$student_email]);
        $student = $stmt->fetch();

        if ($student) {
            // Проверка, не добавлен ли уже этот ученик к тренеру
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM coach_students WHERE coach_id = ? AND student_id = ?");
            $stmt->execute([$coach_id, $student['id']]);
            $exists = $stmt->fetchColumn();

            if ($exists) {
                $error = "Этот ученик уже добавлен к вам";
            } else {
                // Добавление связи тренер-ученик
                $stmt = $pdo->prepare("INSERT INTO coach_students (coach_id, student_id) VALUES (?, ?)");
                $stmt->execute([$coach_id, $student['id']]);
                $success = "Ученик успешно добавлен";
            }
        } else {
            $error = "Ученик с таким email не найден";
        }
    } catch(PDOException $e) {
        $error = "Ошибка при добавлении ученика: " . $e->getMessage();
    }
}

// Получение списка всех учеников (не тренеров)
$stmt = $pdo->prepare("
    SELECT u.* 
    FROM users u 
    LEFT JOIN coach_students cs ON u.id = cs.student_id AND cs.coach_id = ?
    WHERE u.is_coach = 0 AND cs.id IS NULL
    ORDER BY u.full_name
");
$stmt->execute([$coach_id]);
$available_students = $stmt->fetchAll();

$current_page = 'dashboard';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавление ученика - Шахматный портал</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tournaments.css">
    <style>
        .add-student-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: var(--background-light);
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: var(--background-light);
            color: var(--text-light);
            font-size: 1rem;
        }
        .available-students {
            margin-top: 2rem;
        }
        .available-students h2{
            color: #2c3e50;
        }
        .student-list {
            list-style: none;
            padding: 0;
            margin-top: 1rem;
        }
        .student-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s ease;
        }
        .student-item:hover {
            background: var(--background-light);
        }
        .student-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 500;
        }
        .student-details h3 {
            color: var(--text-dark);
            margin: 0;
        }
        .student-details p {
            color: var(--text-muted);
            margin: 0;
            font-size: 0.9rem;
        }
        .btn-add {
            padding: 0.5rem 1rem;
            background: var(--success-color);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-add:hover {
            background: var(--success-dark);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <?php include INCLUDES_PATH . '/header.php'; ?>

    <div class="add-student-container">
        <h1 class="page-title">
            <i class="fas fa-user-plus"></i>
            Добавление ученика
        </h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="student_email">Email ученика</label>
                <input type="email" class="form-control" id="student_email" name="student_email" required>
            </div>
            <button type="submit" class="btn">Добавить ученика</button>
        </form>
        <div class="available-students">
            <h2>Доступные ученики</h2>
            <ul class="student-list">
                <?php foreach ($available_students as $student): ?>
                    <li class="student-item">
                        <div class="student-info">
                            <div class="student-avatar">
                                <?php echo strtoupper(substr($student['username'], 0, 1)); ?>
                            </div>
                            <div class="student-details">
                                <h3><?php echo htmlspecialchars($student['full_name']); ?></h3>
                                <p><?php echo htmlspecialchars($student['email']); ?></p>
                            </div>
                        </div>
                        <form method="POST" action="" style="margin: 0;">
                            <input type="hidden" name="student_email" value="<?php echo htmlspecialchars($student['email']); ?>">
                            <button type="submit" class="btn-add">
                                <i class="fas fa-plus"></i>
                                Добавить
                            </button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php include INCLUDES_PATH . '/footer.php'; ?>
</body>
</html>
