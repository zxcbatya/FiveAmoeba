<?php
// Здесь будет код для отображения топ игроков
$query = "SELECT username, rating FROM users ORDER BY rating DESC LIMIT 5";
try {
    $stmt = $pdo->query($query);
    
    if ($stmt) {
        echo '<ul class="leaderboard-list">';
        while ($row = $stmt->fetch()) {
            echo '<li>' . htmlspecialchars($row['username']) . ' - ' . $row['rating'] . '</li>';
        }
        echo '</ul>';
    }
} catch(PDOException $e) {
    echo '<p>Ошибка при получении данных</p>';
}
?>
