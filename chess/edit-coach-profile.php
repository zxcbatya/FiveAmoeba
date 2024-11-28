<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Проверка авторизации и роли тренера
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_coach']) || !$_SESSION['is_coach']) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Получение текущих данных тренера
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_coach = 1");
    $stmt->execute([$user_id]);
    $coach = $stmt->fetch();
    
    if (!$coach) {
        header("Location: login.php");
        exit();
    }
} catch(PDOException $e) {
    $error_message = "Ошибка при получении данных: " . $e->getMessage();
}

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $data = [
            'full_name' => $_POST['full_name'] ?? $coach['full_name'],
            'education' => $_POST['education'] ?? $coach['education'],
            'experience_years' => $_POST['experience_years'] ?? $coach['experience_years'],
            'achievements' => $_POST['achievements'] ?? $coach['achievements'],
            'teaching_approach' => $_POST['teaching_approach'] ?? $coach['teaching_approach'],
            'specialization' => $_POST['specialization'] ?? $coach['specialization'],
            'certificates' => $_POST['certificates'] ?? $coach['certificates'],
            'location' => $_POST['location'] ?? $coach['location'],
            'contact_phone' => $_POST['contact_phone'] ?? $coach['contact_phone'],
            'available_hours' => $_POST['available_hours'] ?? $coach['available_hours'],
            'hourly_rate' => $_POST['hourly_rate'] ?? $coach['hourly_rate'],
            'id' => $user_id
        ];

        $sql = "UPDATE users SET 
                full_name = :full_name,
                education = :education,
                experience_years = :experience_years,
                achievements = :achievements,
                teaching_approach = :teaching_approach,
                specialization = :specialization,
                certificates = :certificates,
                location = :location,
                contact_phone = :contact_phone,
                available_hours = :available_hours,
                hourly_rate = :hourly_rate
                WHERE id = :id AND is_coach = 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        
        $success_message = "Профиль успешно обновлен!";
        
        // Обновляем данные для отображения в форме
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_coach = 1");
        $stmt->execute([$user_id]);
        $coach = $stmt->fetch();
    } catch(PDOException $e) {
        $error_message = "Ошибка при обновлении профиля: " . $e->getMessage();
    }
}

$current_page = 'edit-profile';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование профиля тренера - Шахматный портал</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tournaments.css">
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <style>
        textarea{
            width: 560px;
            resize: none;
        }
        input{
            width: 560px;
        }
        .dashboard-header{
            text-align: center;
            color: #2c3e50;
            margin: 0px 0px 20px 0px;
            font-size: 2.5em;
            font-weight: 700;
            position: relative;
        }
        .dashboard-header::after{
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, #3498db, #2980b9);
            margin: 15px auto;
            border-radius: 2px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="content-wrapper">
                <div class="dashboard-header">
                    <h1>Редактирование профиля</h1>
                </div>
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" class="profile-form">
                                <div class="form-group">
                                    <label for="full_name"><i class="fas fa-user"></i> ФИО:</label>
                                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($coach['full_name']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="education"><i class="fas fa-graduation-cap"></i> Образование:</label>
                                    <textarea id="education" name="education"><?php echo htmlspecialchars($coach['education']); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="experience_years"><i class="fas fa-briefcase"></i> Опыт работы (лет):</label>
                                    <input type="number" id="experience_years" name="experience_years" value="<?php echo htmlspecialchars($coach['experience_years']); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="achievements"><i class="fas fa-trophy"></i> Достижения:</label>
                                    <textarea id="achievements" name="achievements"><?php echo htmlspecialchars($coach['achievements']); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="teaching_approach"><i class="fas fa-chalkboard-teacher"></i> Подход к обучению:</label>
                                    <textarea id="teaching_approach" name="teaching_approach"><?php echo htmlspecialchars($coach['teaching_approach']); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="specialization"><i class="fas fa-chess"></i> Специализация:</label>
                                    <input type="text" id="specialization" name="specialization" value="<?php echo htmlspecialchars($coach['specialization']); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="certificates"><i class="fas fa-certificate"></i> Сертификаты:</label>
                                    <textarea id="certificates" name="certificates"><?php echo htmlspecialchars($coach['certificates']); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="location"><i class="fas fa-map-marker-alt"></i> Местоположение:</label>
                                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($coach['location']); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="contact_phone"><i class="fas fa-phone"></i> Контактный телефон:</label>
                                    <input type="text" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($coach['contact_phone']); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="available_hours"><i class="fas fa-clock"></i> Доступные часы для занятий:</label>
                                    <textarea id="available_hours" name="available_hours"><?php echo htmlspecialchars($coach['available_hours']); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="hourly_rate"><i class="fas fa-ruble-sign"></i> Стоимость занятия (в час):</label>
                                    <input type="number" step="0.01" id="hourly_rate" name="hourly_rate" value="<?php echo htmlspecialchars($coach['hourly_rate']); ?>">
                                </div>
                            

                            <div class="form-actions">
                                <button type="submit" class="btn ">
                                    <i class="fas fa-save"></i> Сохранить изменения
                                </button>
                                <a href="dashboard-coach.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Вернуться в личный кабинет
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
