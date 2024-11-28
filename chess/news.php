<?php
session_start();


define('BASE_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');
$page_title = 'Ресурсы';
$current_page = '';

?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Шахматная литература</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tournaments.css">
    <link rel="stylesheet" href="css/notifications.css">
    
</head>
<body>
    <?php include INCLUDES_PATH . './header.php'?>
    <h3 class="page-title">Рекомендуемые книги по шахматам</h3>
        <section class="books">
            <div class="book-card">
                <img class="book-image" src="https://cdn-icons-png.flaticon.com/512/5837/5837150.png" alt="Основы шахматной игры">
                <div class="book-info">
                    <h3 class="book-info-text">Основы шахматной игры</h3>
                    <p>Автор: Хосе Рауль Капабланка</p>
                    <p>Описание: Классическая книга по основам шахмат, написанная одним из величайших игроков всех времен.</p>
                </div>
            </div>
            <div class="read-button">
                <a href="https://djvu.online/file/mUvPF8LQjLll1?ysclid=m3yi4833e8771122842" class="btn">Читать</a>
            </div>
            <div class="book-card">
                <img class="book-image" src="https://cdn-icons-png.flaticon.com/512/5837/5837150.png" alt="Моя система">
                <div class="book-info">
                    <h3 class="book-info-text">Моя система</h3>
                    <p>Автор: Арон Нимцович</p>
                    <p>Описание: Книга, ставшая классикой шахматной стратегии, написана одним из ведущих теоретиков начала XX века.</p>
                </div>
            </div>
            <div class="read-button">
                <a href="https://djvu.online/file/mUvPF8LQjLll1?ysclid=m3yi4833e8771122842" class="btn">Читать</a>
            </div>
            <div class="book-card">
                <img class="book-image" src="https://cdn-icons-png.flaticon.com/512/5837/5837150.png" alt="Дебютный репертуар атакующего шахматиста">
                <div class="book-info">
                    <h3 class="book-info-text">Дебютный репертуар атакующего шахматиста</h3>
                    <p>Автор: Николай Калиниченко</p>
                    <p>Описание: Современный подход к изучению дебютов для амбициозных шахматистов.</p>
                </div>
            </div>
            <div class="read-button">
                <a href="https://djvu.online/file/mUvPF8LQjLll1?ysclid=m3yi4833e8771122842" class="btn">Читать</a>
            </div>
        </section>
    <h2 class="videos-text">Полезные видеоуроки по шахматам</h2>
    <section class="videos">
        <ul class="video-list">
            <a class="video-item">         
                <iframe width="560" height="315" src="https://rutube.ru/play/embed/e1b5ee46de1e9708deb82b15142d2fdd" frameBorder="0" allow="clipboard-write; autoplay" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
                <p>Видеоурок: Основы дебютов</p>
            </a>
            <a class="video-item">
                <iframe width="560" height="315" src="https://rutube.ru/play/embed/7d96442d4a4bb6ddf76a45ebf0016eb1" frameBorder="0" allow="clipboard-write; autoplay" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>   
                <p>Видеоурок: Стратегия миттельшпиля</p>
            </a>
            <a class="video-item"> 
                <iframe width="560" height="315" src="https://rutube.ru/play/embed/bc090db07301ce993be980c84d38b862" frameBorder="0" allow="clipboard-write; autoplay" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
                <p>Видеоурок: Тактика эндшпиля</p>
            </a>
            <a class="video-item">   
                <iframe width="560" height="315" src="https://rutube.ru/play/embed/c5ac82154ab732fb853b1a734de2b1ca" frameBorder="0" allow="clipboard-write; autoplay" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframу>
                <p>Видеоурок: Тактические приемы</p>
            </a>
            <a class="video-item">  
                <iframe width="560" height="315" src="https://rutube.ru/play/embed/4390191957c1a934f9d7bbfdc92ac98d" frameBorder="0" allow="clipboard-write; autoplay" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
                <p>Видеоурок: 7 ловушек в дебьюте</p>
            </a>
            <a class="video-item">
                <iframe width="560" height="315" src="https://rutube.ru/play/embed/af7ce04bbda9ea08660e0e1376d27d02" frameBorder="0" allow="clipboard-write; autoplay" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
                <p>Видеоурок: Французская защита</p>
            </a>
            <a class="video-item">
                <iframe width="560" height="315" src="https://rutube.ru/play/embed/5e71af70a4212e8bb13cb888e9df97d0" frameBorder="0" allow="clipboard-write; autoplay" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
                <p>Видеоурок: Защита Филидора</p>
            </a>
            
        </ul>
    </section>
    <?php include INCLUDES_PATH . '/footer.php'; ?>
</body>
</html>