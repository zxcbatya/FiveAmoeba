<?php
session_start();


define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');

// Include required files
require_once CONFIG_PATH . '/database.php';
require_once INCLUDES_PATH . '/functions.php';
// $page_title = 'Главная';
$current_page = 'register';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'username' => $_POST['username'] ?? '',
        'full_name' => $_POST['full_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
        'school' => $_POST['school'] ?? '',
        'grade' => $_POST['grade'] ?? '',
        'location' => $_POST['location'] ?? '',
        'age' => $_POST['age'] ?? '',
        'gender' => $_POST['gender'] ?? '',
        'training_status' => $_POST['training_status'] ?? '',
        'coach_name' => $_POST['coach_name'] ?? '',
        'rating' => $_POST['rating'] ?? '',
        'sports_rank' => $_POST['sports_rank'] ?? '',
        'competition_history' => $_POST['competition_history'] ?? '',
        'training_time' => $_POST['training_time'] ?? '',
        'other_sports' => $_POST['other_sports'] ?? ''
    ];

    try {
        $sql = "INSERT INTO users (username, full_name, email, password, school, grade, location, age, gender, 
                training_status, coach_name, rating, sports_rank, competition_history, training_time, other_sports) 
                VALUES (:username, :full_name, :email, :password, :school, :grade, :location, :age, :gender,
                :training_status, :coach_name, :rating, :sports_rank, :competition_history, :training_time, :other_sports)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        
        $_SESSION['success'] = "Регистрация успешно завершена!";
        header("Location: login.php");
        exit();
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
    <title>Регистрация - Шахматный портал</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tournaments.css">
    <link rel="stylesheet" href="css/notifications.css">
</head>
<body>
    <?php include INCLUDES_PATH . '/header.php'; ?>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="register-container">
                        <h1 class="page-title">Регистрация ученика</h1>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Никнейм</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="full_name" class="form-label">ФИО</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Пароль</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="mb-3">
                                <label for="school" class="form-label">Школа</label>
                                <input type="text" class="form-control" id="school" name="school">
                            </div>

                            <div class="mb-3">
                                <label for="grade" class="form-label">Класс</label>
                                <input type="text" class="form-control" id="grade" name="grade">
                            </div>

                            <div class="mb-3">
                                <label for="location" class="form-label">Место проживания</label>
                                <input type="text" class="form-control" id="location" name="location">
                            </div>

                            <div class="mb-3">
                                <label for="age" class="form-label">Возраст</label>
                                <input type="number" class="form-control" id="age" name="age">
                            </div>

                            <div class="mb-3">
                                <label for="gender" class="form-label">Пол</label>
                                <select class="form-control" id="gender" name="gender">
                                    <option value="male">Мужской</option>
                                    <option value="female">Женский</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="training_status" class="form-label">Статус подготовки</label>
                                <select class="form-control" id="training_status" name="training_status">
                                    <option value="self">Самоподготовка</option>
                                    <option value="club">Спортивная организация</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="coach_name" class="form-label">ФИО тренера</label>
                                <input type="text" class="form-control" id="coach_name" name="coach_name">
                            </div>

                            <div class="mb-3">
                                <label for="rating" class="form-label">Действующий рейтинг</label>
                                <input type="text" class="form-control" id="rating" name="rating">
                            </div>

                            <div class="mb-3">
                                <label for="sports_rank" class="form-label">Разряд</label>
                                <input type="text" class="form-control" id="sports_rank" name="sports_rank">
                            </div>

                            <div class="mb-3">
                                <label for="competition_history" class="form-label">Участие в соревнованиях</label>
                                <textarea class="form-control" id="competition_history" name="competition_history"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="training_time" class="form-label">Время занятий</label>
                                <input type="text" class="form-control" id="training_time" name="training_time">
                            </div>

                            <div class="mb-3">
                                <label for="other_sports" class="form-label">Другие виды спорта</label>
                                <input type="text" class="form-control" id="other_sports" name="other_sports">
                            </div>

                            <button type="submit" class="btn ">Зарегистрироваться</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include INCLUDES_PATH . '/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
