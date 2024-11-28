<header class="main-nav">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <img src="images/logo.png" alt="Шахматный портал">
                <span>Вселенная шахмат</span>
            </a>
            <div class="nav-menu">     
                <a href="index.php" <?php echo $current_page === 'home' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-home"></i> Главная
                </a>
                <a href="tournaments.php" <?php echo $current_page === 'tournaments' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-trophy"></i> Турниры
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']): ?>
                        <a href="my_tournaments.php" <?php echo $current_page === 'my_tournaments' ? 'class = "active"' : ''; ?>">
                            <i class="fas fa-chess"></i> Мои турниры
                        </a>
                    <?php endif; ?>
                    <?php if ((!isset($_SESSION['is_coach']) || !$_SESSION['is_coach']) && (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin'])): ?>
                        <a href="training.php" <?php echo $current_page === 'training' ? 'class="active"' : ''; ?>>
                            <i class="fas fa-graduation-cap"></i> Обучение
                        </a>
                        <a href="stockfish_test.php" <?php echo $current_page === 'stockfish_test' ? 'class="active"' : ''; ?>>
                            <i class="fas fa-robot"></i> Игра
                        </a>
                        <a href="tests.php" <?php echo $current_page === 'tests' ? 'class = "active"' : ''; ?>">
                            <i class="fas fa-chess"></i> Тесты
                        </a>
                    <?php endif; ?>
                    <a href="players.php" <?php echo $current_page === 'players' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-users"></i> Игроки
                    </a>
                    <a href="<?php 
                        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
                            echo 'dashboard-admin.php';
                        } elseif (isset($_SESSION['is_coach']) && $_SESSION['is_coach']) {
                            echo 'dashboard-coach.php';
                        } else {
                            echo 'dashboard.php';
                        }
                    ?>" <?php echo $current_page === 'dashboard' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-chess-board"></i> Личный кабинет
                    </a>
                    <a href="logout.php" class="nav-menu-right">
                        <i class="fas fa-sign-out-alt"></i> Выход
                    </a>
                <?php else: ?>
                    <div class="nav-menu-right">
                        <a href="login.php" <?php echo $current_page === 'login' ? 'class="active"' : ''; ?>>
                            <i class="fas fa-sign-in-alt"></i> Вход
                        </a>
                        <a href="register-choice.php" <?php echo $current_page === 'register' ? 'class="active"' : ''; ?>>
                            <i class="fas fa-user-plus"></i> Регистрация
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
</header>
    <?php if (isset($additional_js)): ?>
    <?php foreach ($additional_js as $js): ?>
        <script src="<?php echo $js; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>