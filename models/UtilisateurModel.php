<?php
class UtilisateurModel {
    private $pdo;

    public function __construct() {
        $this->pdo = new PDO("mysql:host=localhost;dbname=web_sport;charset=utf8", "root", "");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getUserByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function createUser(string $nom, string $prenom, string $email, string $hashedPassword, string $role = 'adherent', int $is_actif = 0): bool {
        $stmt = $this->pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, is_actif) VALUES (?, ?, ?, ?, ?, ?)");
        try {
            return $stmt->execute([$nom, $prenom, $email, $hashedPassword, $role, $is_actif]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
