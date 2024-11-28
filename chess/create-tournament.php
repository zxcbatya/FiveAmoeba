<?php
session_start();

// Проверка авторизации и прав администратора
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: tournaments.php");
    exit();
}

require_once 'config/database.php';
require_once 'includes/functions.php';
define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $start_date = $_POST['start_date'];
    $prize_pool = (float)$_POST['prize_pool'];
    $max_participants = (int)$_POST['max_participants'];
    $status = 'open';

    if (empty($name) || empty($start_date)) {
        $error_message = 'Пожалуйста, заполните все обязательные поля';
    } else {
        try {
            // First, let's verify the table structure
            $stmt = $pdo->query("DESCRIBE tournaments");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Build the query based on existing columns
            $fields = ['name', 'description', 'start_date', 'status', 'created_by'];
            $values = [$name, $description, $start_date, $status, $_SESSION['user_id']];
            $placeholders = ['?', '?', '?', '?', '?'];
            
            if (in_array('prize_pool', $columns)) {
                $fields[] = 'prize_pool';
                $values[] = $prize_pool;
                $placeholders[] = '?';
            }
            
            if (in_array('max_participants', $columns)) {
                $fields[] = 'max_participants';
                $values[] = $max_participants;
                $placeholders[] = '?';
            }
            
            $query = "INSERT INTO tournaments (" . implode(', ', $fields) . ") 
                     VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $pdo->prepare($query);
            $result = $stmt->execute($values);

            if ($result) {
                header("Location: tournaments.php?success=created");
                exit();
            } else {
                $error_message = 'Произошла ошибка при создании турнира';
            }
        } catch (PDOException $e) {
            $error_message = 'Произошла ошибка при создании турнира: ' . $e->getMessage();
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
    <style>
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-header h1 {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0 ;
            margin-right: 15px;
            font-size: 1.75rem;
            color: #1a202c;
        }

        .page-header h1 i {
            color: #3182ce;
        }

        .tournament-form {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .form-section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 1.5rem;
        }

        .required-field::after {
            content: '*';
            color: #e53e3e;
            margin-left: 4px;
        }

        .form-hint {
            font-size: 0.875rem;
            color: #718096;
            margin-top: 0.5rem;
        }

        @media (max-width: 768px) {
            .tournament-form {
                margin: 1rem;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include INCLUDES_PATH . '/header.php'; ?>

    <main class="container">
        <div class="page-header">
            <h1>
                <i class="fas fa-trophy"></i>
                Создание нового турнира
            </h1>
            <a href="tournaments.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Назад к турнирам
            </a>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="tournament-form">
            <div class="form-section">
                <h2 class="form-section-title">Основная информация</h2>
                
                <div class="form-group">
                    <label for="name">Название турнира *</label>
                    <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="description">Описание</label>
                    <textarea id="description" name="description"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="start_date">Дата начала *</label>
                    <input type="date" id="start_date" name="start_date" required value="<?php echo isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : ''; ?>">
                </div>
            </div>

            <div class="form-section">
                <h2 class="form-section-title">Настройки турнира</h2>
                
                <div class="form-group">
                    <label for="max_participants">Максимальное количество участников</label>
                    <input type="number" id="max_participants" name="max_participants" min="2" value="<?php echo isset($_POST['max_participants']) ? htmlspecialchars($_POST['max_participants']) : '16'; ?>">
                </div>

                <div class="form-group">
                    <label for="prize_pool">Призовой фонд (₽)</label>
                    <input type="number" id="prize_pool" name="prize_pool" min="0" step="100" value="<?php echo isset($_POST['prize_pool']) ? htmlspecialchars($_POST['prize_pool']) : '0'; ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">
                    <i class="fas fa-plus"></i>
                    Создать турнир
                </button>
                <a href="tournaments.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Отмена
                </a>
            </div>
        </form>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Устанавливаем минимальную дату
        const startDateInput = document.getElementById('start_date');
        const today = new Date().toISOString().split('T')[0];
        startDateInput.min = today;

        // Валидация формы
        document.querySelector('.tournament-form').addEventListener('submit', function(e) {
            const startDate = new Date(startDateInput.value);
            const now = new Date();

            if (startDate < now) {
                e.preventDefault();
                alert('Дата начала турнира не может быть в прошлом');
            }
        });
    </script>
</body>
</html>
