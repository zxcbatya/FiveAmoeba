<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');

$current_page = 'register';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'username' => $_POST['username'] ?? '',
        'full_name' => $_POST['full_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
        'education' => $_POST['education'] ?? '',
        'experience_years' => $_POST['experience_years'] ?? '',
        'achievements' => $_POST['achievements'] ?? '',
        'teaching_approach' => $_POST['teaching_approach'] ?? '',
        'specialization' => $_POST['specialization'] ?? '',
        'certificates' => $_POST['certificates'] ?? '',
        'location' => $_POST['location'] ?? '',
        'contact_phone' => $_POST['contact_phone'] ?? '',
        'available_hours' => $_POST['available_hours'] ?? '',
        'hourly_rate' => $_POST['hourly_rate'] ?? '',
        'is_coach' => 1
    ];

    try {
        
        $check_query = "SELECT COUNT(*) FROM users WHERE email = ? OR username = ?";
        $check_stmt = $pdo->prepare($check_query);
        $check_stmt->execute([$data['email'], $data['username']]);
        $exists = $check_stmt->fetchColumn();

        if ($exists) {
            $error = "Пользователь с таким email или никнеймом уже существует";
        } else {
            $sql = "INSERT INTO users (username, full_name, email, password, education, experience_years, 
                    achievements, teaching_approach, specialization, certificates, location, 
                    contact_phone, available_hours, hourly_rate, is_coach) 
                    VALUES (:username, :full_name, :email, :password, :education, :experience_years,
                    :achievements, :teaching_approach, :specialization, :certificates, :location,
                    :contact_phone, :available_hours, :hourly_rate, :is_coach)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            
            $_SESSION['success'] = "Регистрация успешно завершена!";
            header("Location: login.php");
            exit();
        }
    } catch(PDOException $e) {
        $error = "Ошибка при регистрации: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация тренера - Шахматный портал</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tournaments.css">
    <link rel="stylesheet" href="css/notifications.css">

    <style>
        #contact_phone{
            margin-bottom: 1rem;
        }
        /* textarea{
            resize: none;
        } */
    </style>
</head>
<body>
    <?php include INCLUDES_PATH . '/header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="register-container">
                        <h2 class="page-title">Регистрация тренера</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>


                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username">Никнейм</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>

                            <div class="mb-3">
                                <label for="full_name">ФИО</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>

                            <div class="mb-3">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label for="password">Пароль</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="mb-3">
                                <label for="education">Образование</label>
                                <input type="text" class="form-control" id="education" name="education" required>
                            </div>

                            <div class="mb-3">
                                <label for="experience_years">Опыт работы (лет)</label>
                                <input type="number" class="form-control" id="experience_years" name="experience_years" min="0" required>
                            </div>

                            <div class="mb-3">
                                <label for="achievements">Достижения и награды</label>
                                <textarea class="form-control" id="achievements" name="achievements" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="teaching_approach">Подход к обучению</label>
                                <textarea class="form-control" id="teaching_approach" name="teaching_approach" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="specialization">Специализация</label>
                                <input type="text" class="form-control" id="specialization" name="specialization" required>
                            </div>

                            <div class="mb-3">
                                <label for="certificates">Сертификаты и лицензии</label>
                                <textarea class="form-control" id="certificates" name="certificates"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="location">Город</label>
                                <input type="text" class="form-control" id="location" name="location" required>
                            </div>

                            <div class="mb-3">
                                <label for="contact_phone">Контактный телефон</label>
                                <input type="tel" class="form-control" id="contact_phone" name="contact_phone" required>
                            </div>
                            <button type="submit" class="btn">Зарегистрироваться</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include INCLUDES_PATH . '/footer.php'; ?>
</body>
</html>