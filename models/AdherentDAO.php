<?php
// models/AdherentDAO.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Adherent.php';

class AdherentDAO {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host=localhost;dbname=web_sport;charset=utf8mb4",
                "root",     // utilisateur MySQL
                ""          // mot de passe MySQL (vide par défaut sous XAMPP)
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }


    // ================== RÉCUPÉRER TOUS LES ADHÉRENTS ==================
    public function getAllAdherents() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, nom, prenom, email, date_inscription, is_actif 
                FROM utilisateurs 
                WHERE role = 'adherent' 
                ORDER BY nom ASC, prenom ASC
            ");
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            $adherents = [];
            foreach ($results as $row) {
                $adherents[] = new Adherent($row);
            }
            
            return $adherents;
        } catch (PDOException $e) {
            error_log("Erreur getAllAdherents: " . $e->getMessage());
            return [];
        }
    }

    // ================== RÉCUPÉRER PAR ID ==================
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, nom, prenom, email, date_inscription, is_actif 
                FROM utilisateurs 
                WHERE id = ? AND role = 'adherent'
            ");
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            
            if ($result) {
                return $result; // Retourner array pour AJAX
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Erreur getById: " . $e->getMessage());
            return null;
        }
    }

    // ================== CRÉER ADHÉRENT ==================
    public function create(Adherent $adherent) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, is_actif, date_inscription) 
                VALUES (?, ?, ?, ?, 'adherent', ?, NOW())
            ");
            
            $hashedPassword = password_hash($adherent->getMotDePasse(), PASSWORD_DEFAULT);
            
            $result = $stmt->execute([
                $adherent->getNom(),
                $adherent->getPrenom(),
                $adherent->getEmail(),
                $hashedPassword,
                $adherent->getIsActif() ? 1 : 0
            ]);
            
            return $result;
        } catch (PDOException $e) {
            error_log("Erreur create: " . $e->getMessage());
            return false;
        }
    }

    // ================== MODIFIER ADHÉRENT ==================
    public function update(Adherent $adherent) {
        try {
            // Si mot de passe fourni, l'inclure dans la mise à jour
            if ($adherent->getMotDePasse()) {
                $stmt = $this->pdo->prepare("
                    UPDATE utilisateurs 
                    SET nom = ?, prenom = ?, email = ?, mot_de_passe = ?, is_actif = ?
                    WHERE id = ? AND role = 'adherent'
                ");
                
                $hashedPassword = password_hash($adherent->getMotDePasse(), PASSWORD_DEFAULT);
                
                $result = $stmt->execute([
                    $adherent->getNom(),
                    $adherent->getPrenom(),
                    $adherent->getEmail(),
                    $hashedPassword,
                    $adherent->getIsActif() ? 1 : 0,
                    $adherent->getId()
                ]);
            } else {
                // Mise à jour sans mot de passe
                $stmt = $this->pdo->prepare("
                    UPDATE utilisateurs 
                    SET nom = ?, prenom = ?, email = ?, is_actif = ?
                    WHERE id = ? AND role = 'adherent'
                ");
                
                $result = $stmt->execute([
                    $adherent->getNom(),
                    $adherent->getPrenom(),
                    $adherent->getEmail(),
                    $adherent->getIsActif() ? 1 : 0,
                    $adherent->getId()
                ]);
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Erreur update: " . $e->getMessage());
            return false;
        }
    }

    // ================== SUPPRIMER ADHÉRENT ==================
    public function delete($id) {
        try {
            // Vérifier d'abord si l'adhérent existe et est bien un adhérent
            $stmt = $this->pdo->prepare("
                SELECT id FROM utilisateurs 
                WHERE id = ? AND role = 'adherent'
            ");
            $stmt->execute([$id]);
            
            if (!$stmt->fetch()) {
                return false;
            }
            
            // Supprimer l'adhérent
            $stmt = $this->pdo->prepare("
                DELETE FROM utilisateurs 
                WHERE id = ? AND role = 'adherent'
            ");
            
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erreur delete: " . $e->getMessage());
            return false;
        }
    }

    // ================== STATISTIQUES ==================
    public function getStats() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_actif = 1 THEN 1 ELSE 0 END) as actifs,
                    SUM(CASE WHEN is_actif = 0 THEN 1 ELSE 0 END) as inactifs
                FROM utilisateurs 
                WHERE role = 'adherent'
            ");
            $stmt->execute();
            $result = $stmt->fetch();
            
            return [
                'total' => (int)$result['total'],
                'actifs' => (int)$result['actifs'],
                'inactifs' => (int)$result['inactifs']
            ];
        } catch (PDOException $e) {
            error_log("Erreur getStats: " . $e->getMessage());
            return ['total' => 0, 'actifs' => 0, 'inactifs' => 0];
        }
    }

    // ================== VÉRIFIER EMAIL UNIQUE ==================
    public function emailExists($email, $excludeId = null) {
        try {
            if ($excludeId) {
                $stmt = $this->pdo->prepare("
                    SELECT id FROM utilisateurs 
                    WHERE email = ? AND id != ?
                ");
                $stmt->execute([$email, $excludeId]);
            } else {
                $stmt = $this->pdo->prepare("
                    SELECT id FROM utilisateurs 
                    WHERE email = ?
                ");
                $stmt->execute([$email]);
            }
            
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Erreur emailExists: " . $e->getMessage());
            return true; // Retourner true par sécurité
        }
    }

    // ================== RECHERCHER ADHÉRENTS ==================
    public function search($keyword) {
        try {
            $searchTerm = '%' . $keyword . '%';
            $stmt = $this->pdo->prepare("
                SELECT id, nom, prenom, email, date_inscription, is_actif 
                FROM utilisateurs 
                WHERE role = 'adherent' 
                AND (nom LIKE ? OR prenom LIKE ? OR email LIKE ?)
                ORDER BY nom ASC, prenom ASC
            ");
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
            $results = $stmt->fetchAll();
            
            $adherents = [];
            foreach ($results as $row) {
                $adherents[] = new Adherent($row);
            }
            
            return $adherents;
        } catch (PDOException $e) {
            error_log("Erreur search: " . $e->getMessage());
            return [];
        }
    }
}