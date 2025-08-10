<?php
class AdminModel {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function getUtilisateursEnAttente() {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateurs WHERE is_actif = 0");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Ajoute ici d'autres méthodes pour gérer les activités, inscriptions, etc.
}
