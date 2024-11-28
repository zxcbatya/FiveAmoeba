<?php session_start(); 
require_once 'config/database.php';
require_once 'includes/functions.php';

define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');

// Получение данных пользователя
if (isset($_SESSION['user_id'])):
    
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

endif;
// Получение статистики игр
$stats = [
    'total_games' => 0,
    'wins' => 0,
    'losses' => 0
];

// Получение топ-3 игроков
$top_players_query = "SELECT u.id, u.username, u.rating, u.games_played, u.sports_rank,
                     (SELECT COUNT(*) 
                      FROM tournament_participants tp 
                      JOIN tournaments t ON tp.tournament_id = t.id 
                      WHERE tp.user_id = u.id AND t.status = 'completed'
                     ) as tournaments_played
                FROM users u
                ORDER BY u.rating DESC
                LIMIT 3";
$stmt = $pdo->prepare($top_players_query);
$stmt->execute();
$top_players = $stmt->fetchAll();

// Получение параметров сортировки и фильтрации
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'rating';
$location_filter = isset($_GET['location']) ? $_GET['location'] : '';
$gender_filter = isset($_GET['gender']) ? $_GET['gender'] : '';
$age_min = isset($_GET['age_min']) ? (int)$_GET['age_min'] : '';
$age_max = isset($_GET['age_max']) ? (int)$_GET['age_max'] : '';
$training_status = isset($_GET['training_status']) ? $_GET['training_status'] : ''; // Используем training_status

// Проверяем, была ли нажата кнопка поиска
$search_clicked = isset($_GET['search_players']);

// Базовый SQL запрос
$sql = "SELECT u.*, 
               (SELECT COUNT(*) 
                FROM tournament_participants tp 
                JOIN tournaments t ON tp.tournament_id = t.id 
                WHERE tp.user_id = u.id AND t.status = 'completed'
               ) as tournaments_played
        FROM users u
        WHERE 1=1";

// Добавляем условия фильтрации только если была нажата кнопка поиска
if ($search_clicked) {
    if (!empty($location_filter)) {
        $sql .= " AND u.location = :location";
    }
    if (!empty($gender_filter)) {
        $sql .= " AND u.gender = :gender";
    }
    if ($age_min !== '') {
        $sql .= " AND u.age >= :age_min";
    }
    if ($age_max !== '') {
        $sql .= " AND u.age <= :age_max";
    }
    if (!empty($training_status)) {
        $sql .= " AND u.training_status = :training_status";
    }

    // Определение сортировки
    $sql .= " ORDER BY " . match($sort) {
        'games' => 'u.games_played DESC',
        'tournaments' => '(SELECT COUNT(*) FROM tournament_participants tp WHERE tp.user_id = u.id) DESC',
        'achievements' => 'u.sports_rank DESC',
        'location' => 'u.location ASC',
        default => 'u.rating DESC'
    };

    // Подготавливаем и выполняем запрос
    $stmt = $pdo->prepare($sql);

    // Привязываем параметры
    if (!empty($location_filter)) {
        $stmt->bindValue(':location', $location_filter);
    }
    if (!empty($gender_filter)) {
        $stmt->bindValue(':gender', $gender_filter);
    }
    if ($age_min !== '') {
        $stmt->bindValue(':age_min', $age_min, PDO::PARAM_INT);
    }
    if ($age_max !== '') {
        $stmt->bindValue(':age_max', $age_max, PDO::PARAM_INT);
    }
    if (!empty($training_status)) {
        $stmt->bindValue(':training_status', $training_status);
    }

    $stmt->execute();
    $filtered_players = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Получение уникальных значений для фильтров
$locations_query = "SELECT DISTINCT location FROM users WHERE location IS NOT NULL ORDER BY location";
$locations = $pdo->query($locations_query)->fetchAll(PDO::FETCH_COLUMN);

$current_page = 'players';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игроки - Шахматный портал</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tournaments.css">
    <link rel="stylesheet" href="css/notifications.css">
    <style>
        .filters-section {
            margin-bottom: 2rem;
            background: var(--background-light);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .filter-group label {
            font-weight: 500;
            color: var(--text-primary);
        }
        .filter-group select {
            padding: 10px 15px;
            border: 1px solid var(--secondary-dark);
            border-radius: 8px;
            background: var(--background-light);
            color: var(--text-secondary);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        .filter-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }
        .sort-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }
        .sort-button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            background: var(--background-light);
            color: var(--text-secondary);
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid var(--border-color);
        }
        .sort-button:hover {
            background: var(--primary-color);
            color: white;
        }
        .sort-button.active {
            background: var(--primary-color);
            color: white;
        }
        .sort-button i {
            font-size: 1rem;
        }
        @media (max-width: 768px) {
            .filters-grid {
                grid-template-columns: 1fr;
            }
            .sort-buttons {
                flex-wrap: wrap;
            }
            .sort-button {
                flex: 1;
                min-width: 140px;
                justify-content: center;
            }
        }
        .top-players-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .rows {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin: 0 auto;
            max-width: 1200px;
            padding: 0 20px;
        }
        .col-md-4 {
            flex: 1;
            min-width: 0;
        }
        .top-player {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            height: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .top-player:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .top-player .player-name {
            text-align: center;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        .top-player .player-name a {
            color: #2c3e50;
            text-decoration: none;
            font-weight: 600;
        }
        .top-player .player-name a:hover {
            color: #3498db;
        }
        .top-player .player-stats {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .top-player .player-stats p {
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .top-player .player-stats p i {
            color: #3498db;
            width: 20px;
            text-align: center;
        }
        .filters-section {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 10px 20px;
        }
        .player-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .player-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .player-avatar {
            text-align: center;
            margin-bottom: 15px;
        }
        .player-avatar i {
            font-size: 3rem;
            color: #3498db;
        }
        .player-info {
            text-align: center;
        }
        .player-stats .stat {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 8px;
        }
        .player-stats .stat i {
            color: #3498db;
        }
        .player-card .btn {
            display: block;
            width: 100%;
            padding: 10px;
            margin-top: 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
            transition: background 0.2s ease;
        }
        .player-card .btn:hover {
            background: #2980b9;
            color: white;
            text-decoration: none;
        }
        .age-filter {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .age-inputs {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .age-input {
            padding: 10px 15px;
            border: 1px solid var(--secondary-dark);
            border-radius: 8px;
            background: var(--background-light);
            color: var(--text-secondary);
            font-size: 0.95rem;
            transition: all 0.3s ease;
            width: 100px;
        }
        .age-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }
        /* Стили для кнопки поиска */
        .search-button {
            display: block;
            width: 100%;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .search-button:hover {
            background: #2980b9;
        }
        .search-button i {
            margin-right: 8px;
        }
        .players-section {
            margin-top: 2rem;
        }
        .players-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }
        .button-setting-search-how-to-take-controle-middle-watch-demo-watch-demo{}
    </style>
</head>
<body>
    <?php include INCLUDES_PATH . '/header.php'; ?>

    <main class="container">
 
        
        <!-- Секция топ-3 игроков -->
        <div class="top-players-section">
            <h2 class="page-title">Топ 3 игрока</h2>
            <div class="rows">
                <?php foreach ($top_players as $player): ?>
                    <div class="col-md-4">
                        <div class="top-player">
                            <div class="player-info">
                                <h3 class="player-name">
                                    <a href="profile.php?id=<?= htmlspecialchars($player['id']) ?>">
                                        <?= htmlspecialchars($player['username']) ?>
                                    </a>
                                </h3>
                                <div class="player-stats">
                                    <p>
                                        <i class="fas fa-star"></i>
                                        <span>Рейтинг: <?= htmlspecialchars($player['rating']) ?></span>
                                    </p>
                                    <p>
                                        <i class="fas fa-chess"></i>
                                        <span>Игр: <?= htmlspecialchars($player['games_played']) ?></span>
                                    </p>
                                    <p>
                                        <i class="fas fa-trophy"></i>
                                        <span>Турниров: <?= htmlspecialchars($player['tournaments_played']) ?></span>
                                    </p>
                                    <?php if ($player['sports_rank']): ?>
                                        <p>
                                            <i class="fas fa-medal"></i>
                                            <span>Разряд: <?= htmlspecialchars($player['sports_rank']) ?></span>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Секция фильтров -->
        <div class="filters-section">
            <form action="" method="GET">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="location">Город</label>
                        <select name="location" id="location">
                            <option value="">Все города</option>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?php echo htmlspecialchars($loc); ?>" 
                                        <?php echo $location_filter === $loc ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($loc); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="training_status">Тип подготовки</label>
                        <select name="training_status" id="training_status">
                            <option value="">Все</option>
                            <option value="self" <?php echo $training_status === 'self' ? 'selected' : ''; ?>>Самоподготовка</option>
                            <option value="club" <?php echo $training_status === 'club' ? 'selected' : ''; ?>>Спортивная организация</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="gender">Пол</label>
                        <select name="gender" id="gender">
                            <option value="">Все</option>
                            <option value="M" <?php echo $gender_filter === 'M' ? 'selected' : ''; ?>>Мужской</option>
                            <option value="F" <?php echo $gender_filter === 'F' ? 'selected' : ''; ?>>Женский</option>
                        </select>
                    </div>

                    <div class="filter-group age-filter">
                        <label>Возраст</label>
                        <div class="age-inputs">
                            <input type="number" name="age_min" id="age_min" placeholder="От" min="0" max="100" 
                                   value="<?php echo $age_min !== '' ? $age_min : ''; ?>" class="age-input">
                            <span>-</span>
                            <input type="number" name="age_max" id="age_max" placeholder="До" min="0" max="100" 
                                   value="<?php echo $age_max !== '' ? $age_max : ''; ?>" class="age-input">
                        </div>
                    </div>
                </div>

                <div class="sort-buttons">
                    <button type="submit" name="sort" value="rating" class="sort-button <?php echo $sort === 'rating' ? 'active' : ''; ?>">
                        <i class="fas fa-star"></i> Рейтинг
                    </button>
                    <button type="submit" name="sort" value="games" class="sort-button <?php echo $sort === 'games' ? 'active' : ''; ?>">
                        <i class="fas fa-chess"></i> Игры
                    </button>
                    <button type="submit" name="sort" value="tournaments" class="sort-button <?php echo $sort === 'tournaments' ? 'active' : ''; ?>">
                        <i class="fas fa-trophy"></i> Турниры
                    </button>
                    <button type="submit" name="sort" value="achievements" class="sort-button <?php echo $sort === 'achievements' ? 'active' : ''; ?>">
                        <i class="fas fa-medal"></i> Достижения
                    </button>
                </div>


                <div class="button-setting-search-how-to-take-controle-middle-watch-demo-watch-demo" style="margin-top: 20px; text-align: center;">
                    <button type="submit" name="search_players" value="1" class="search-button">
                        <i class="fas fa-search"></i> Искать
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Вывод отфильтрованных игроков -->
        <?php if ($search_clicked): ?>
            <div class="players-section">
                <div class="players-grid">
                    <?php if (!empty($filtered_players)): ?>
                        <?php foreach ($filtered_players as $player): ?>
                            <div class="player-card">
                                <div class="player-avatar">
                                    <i class="fas fa-chess-pawn"></i>
                                </div>
                                <div class="player-info">
                                    <h3 class="player-name">
                                        <a href="profile.php?id=<?= htmlspecialchars($player['id']) ?>">
                                            <?= htmlspecialchars($player['username']) ?>
                                        </a>
                                    </h3>
                                    <div class="player-stats">
                                        <div class="stat">
                                            <i class="fas fa-star"></i>
                                            <span>Рейтинг: <?= htmlspecialchars($player['rating']) ?></span>
                                        </div>
                                        <div class="stat">
                                            <i class="fas fa-chess"></i>
                                            <span>Игр: <?= htmlspecialchars($player['games_played']) ?></span>
                                        </div>
                                        <div class="stat">
                                            <i class="fas fa-trophy"></i>
                                            <span>Турниров: <?= htmlspecialchars($player['tournaments_played']) ?></span>
                                        </div>
                                        <?php if (!empty($player['sports_rank'])): ?>
                                            <div class="stat">
                                                <i class="fas fa-medal"></i>
                                                <span>Разряд: <?= htmlspecialchars($player['sports_rank']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($player['location'])): ?>
                                            <div class="stat">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span>Город: <?= htmlspecialchars($player['location']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $player['id']): ?>
                                    <a href="#" class="btn">
                                        <i class="fas fa-gamepad"></i> Вызвать на игру
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center">По вашему запросу игроков не найдено.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

<?php include 'includes/footer.php'; ?>
</body>
</html>