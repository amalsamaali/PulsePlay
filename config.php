<?php
define('BASE_PATH', __DIR__);

class Database {
    private static $instance = null;

    public static function connect(): PDO {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO(
                    "mysql:host=localhost;dbname=web_sport;charset=utf8",
                    "root",
                    ""
                );
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Erreur de connexion : " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}

// Créer la variable globale $pdo pour les contrôleurs
try {
    $GLOBALS['pdo'] = Database::connect();
} catch (Exception $e) {
    die("Erreur lors de l'initialisation de la base de données : " . $e->getMessage());
}
