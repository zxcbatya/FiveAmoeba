<?php
require_once 'config/database.php';

try {
    // Get table structure
    $stmt = $pdo->query("DESCRIBE tournaments");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
