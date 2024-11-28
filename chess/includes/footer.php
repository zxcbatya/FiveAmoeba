<footer class="main-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>О портале</h3>
                <p>Вселенная шахмат - место, где собираются любители шахмат для участия в турнирах, общения и развития своего мастерства.</p>
            </div>
            <div class="footer-section">
                <h3>Быстрые ссылки</h3>
                <ul>
                    <li><a href="quest.php"><i class="fas fa-tasks"></i> Квест</a></li>
                    <li><a href="news.php"><i class="fas fa-newspaper"></i> Ресурсы</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Контакты</h3>
                <ul>
                    <li><i class="fas fa-envelope"></i> chess_centr@mail.ru</li>
                    <li><i class="fas fa-phone"></i> +7 929 249 41 50</li>
                    <li><i class="fas fa-map-marker-alt"></i> Ханты-Мансийск, Россия</li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Социальные сети</h3>
                <div class="social-links">
                    <a href="https://vk.com/chessugra" class="social-link"><i class="fab fa-vk"></i></a>
                    <a href="https://t.me/chesshmao" class="social-link"><i class="fab fa-telegram"></i></a>
                    
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Вселенная шахмат. Все права защищены.</p>
        </div>
    </div>
</footer>

<?php if (isset($additional_js)): ?>
    <?php foreach ($additional_js as $js): ?>
        <script src="<?php echo $js; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
