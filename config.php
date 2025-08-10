<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=web_sport", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion OK<br>";

    // Tester une requête simple
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables dans la base : " . implode(', ', $tables);
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
}
?>
