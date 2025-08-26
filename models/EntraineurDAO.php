<?php
// models/EntraineurDAO.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Entraineur.php';

class EntraineurDAO {
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

    // ================== RÉCUPÉRER TOUS LES ENTRAÎNEURS ==================
    public function getAllEntraineurs() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, nom, prenom, email, telephone, specialite, experience, 
                       diplomes, date_embauche, is_actif 
                FROM entraineurs 
                ORDER BY nom ASC, prenom ASC
            ");
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            $entraineurs = [];
            foreach ($results as $row) {
                $entraineurs[] = new Entraineur($row);
            }
            
            return $entraineurs;
        } catch (PDOException $e) {
            error_log("Erreur getAllEntraineurs: " . $e->getMessage());
            return [];
        }
    }

    // ================== RÉCUPÉRER PAR ID ==================
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, nom, prenom, email, telephone, specialite, experience, 
                       diplomes, date_embauche, is_actif 
                FROM entraineurs 
                WHERE id = ?
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

    // ================== CRÉER ENTRAÎNEUR ==================
    public function create(Entraineur $entraineur) {
       try {
            $stmt = $this->pdo->prepare("
                INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, is_actif, date_inscription) 
                VALUES (?, ?, ?, ?, 'adherent', ?, NOW())
            ");
            
            $hashedPassword = password_hash($entraineur->getMotDePasse(), PASSWORD_DEFAULT);
            
            $result = $stmt->execute([
                $entraineur->getNom(),
                $entraineur->getPrenom(),
                $entraineur->getEmail(),
                $hashedPassword,
                $entraineur->getIsActif() ? 1 : 0
            ]);
            
            return $result;
        } catch (PDOException $e) {
            error_log("Erreur create: " . $e->getMessage());
            return false;
        }
    }

    // ================== MODIFIER ENTRAÎNEUR ==================
    public function update(Entraineur $entraineur) {
         try {
            // Si mot de passe fourni, l'inclure dans la mise à jour
            if ($entraineur->getMotDePasse()) {
                $stmt = $this->pdo->prepare("
                    UPDATE utilisateurs 
                    SET nom = ?, prenom = ?, email = ?, mot_de_passe = ?, is_actif = ?
                    WHERE id = ? AND role = 'adherent'
                ");
                
                $hashedPassword = password_hash($entraineur->getMotDePasse(), PASSWORD_DEFAULT);
                
                $result = $stmt->execute([
                    $entraineur->getNom(),
                    $entraineur->getPrenom(),
                    $entraineur->getEmail(),
                    $hashedPassword,
                    $entraineur->getIsActif() ? 1 : 0,
                    $entraineur->getId()
                ]);
            } else {
                // Mise à jour sans mot de passe
                $stmt = $this->pdo->prepare("
                    UPDATE utilisateurs 
                    SET nom = ?, prenom = ?, email = ?, is_actif = ?
                    WHERE id = ? AND role = 'adherent'
                ");
                
                $result = $stmt->execute([
                    $entraineur->getNom(),
                    $entraineur->getPrenom(),
                    $entraineur->getEmail(),
                    $entraineur->getIsActif() ? 1 : 0,
                    $entraineur->getId()
                ]);
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Erreur update: " . $e->getMessage());
            return false;
        }
    }

    // ================== SUPPRIMER ENTRAÎNEUR ==================
    public function delete($id) {
        try {
            // Vérifier d'abord si l'entraîneur existe
            $stmt = $this->pdo->prepare("
                SELECT id FROM entraineurs 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            
            if (!$stmt->fetch()) {
                return false;
            }
            
            // Supprimer l'entraîneur
            $stmt = $this->pdo->prepare("
                DELETE FROM entraineurs 
                WHERE id = ?
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
                    SUM(CASE WHEN is_actif = 0 THEN 1 ELSE 0 END) as inactifs,
                    AVG(experience) as experience_moyenne,
                    COUNT(DISTINCT specialite) as nb_specialites
                FROM entraineurs
            ");
            $stmt->execute();
            $result = $stmt->fetch();
            
            return [
                'total' => (int)$result['total'],
                'actifs' => (int)$result['actifs'],
                'inactifs' => (int)$result['inactifs'],
                'experience_moyenne' => round((float)$result['experience_moyenne'], 1),
                'nb_specialites' => (int)$result['nb_specialites']
            ];
        } catch (PDOException $e) {
            error_log("Erreur getStats: " . $e->getMessage());
            return [
                'total' => 0, 
                'actifs' => 0, 
                'inactifs' => 0, 
                'experience_moyenne' => 0, 
                'nb_specialites' => 0
            ];
        }
    }

    // ================== VÉRIFIER EMAIL UNIQUE ==================
    public function emailExists($email, $excludeId = null) {
        try {
            if ($excludeId) {
                $stmt = $this->pdo->prepare("
                    SELECT id FROM entraineurs 
                    WHERE email = ? AND id != ?
                ");
                $stmt->execute([$email, $excludeId]);
            } else {
                $stmt = $this->pdo->prepare("
                    SELECT id FROM entraineurs 
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

    // ================== RECHERCHER ENTRAÎNEURS ==================
    public function search($keyword) {
        try {
            $searchTerm = '%' . $keyword . '%';
            $stmt = $this->pdo->prepare("
                SELECT id, nom, prenom, email, telephone, specialite, experience, 
                       diplomes, date_embauche, is_actif 
                FROM entraineurs 
                WHERE (nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR specialite LIKE ?)
                ORDER BY nom ASC, prenom ASC
            ");
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            $results = $stmt->fetchAll();
            
            $entraineurs = [];
            foreach ($results as $row) {
                $entraineurs[] = new Entraineur($row);
            }
            
            return $entraineurs;
        } catch (PDOException $e) {
            error_log("Erreur search: " . $e->getMessage());
            return [];
        }
    }

    // ================== RECHERCHER PAR SPÉCIALITÉ ==================
    public function getBySpecialite($specialite) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, nom, prenom, email, telephone, specialite, experience, 
                       diplomes, date_embauche, is_actif 
                FROM entraineurs 
                WHERE specialite = ? AND is_actif = 1
                ORDER BY experience DESC, nom ASC
            ");
            $stmt->execute([$specialite]);
            $results = $stmt->fetchAll();
            
            $entraineurs = [];
            foreach ($results as $row) {
                $entraineurs[] = new Entraineur($row);
            }
            
            return $entraineurs;
        } catch (PDOException $e) {
            error_log("Erreur getBySpecialite: " . $e->getMessage());
            return [];
        }
    }

    // ================== OBTENIR TOUTES LES SPÉCIALITÉS ==================
    public function getAllSpecialites() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT DISTINCT specialite, COUNT(*) as nb_entraineurs 
                FROM entraineurs 
                WHERE is_actif = 1 
                GROUP BY specialite 
                ORDER BY specialite ASC
            ");
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            $specialites = [];
            foreach ($results as $row) {
                $specialites[] = [
                    'nom' => $row['specialite'],
                    'nb_entraineurs' => (int)$row['nb_entraineurs']
                ];
            }
            
            return $specialites;
        } catch (PDOException $e) {
            error_log("Erreur getAllSpecialites: " . $e->getMessage());
            return [];
        }
    }

    // ================== TOP ENTRAÎNEURS PAR EXPÉRIENCE ==================
    public function getTopByExperience($limit = 5) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, nom, prenom, email, telephone, specialite, experience, 
                       diplomes, date_embauche, is_actif 
                FROM entraineurs 
                WHERE is_actif = 1 
                ORDER BY experience DESC, date_embauche ASC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $results = $stmt->fetchAll();
            
            $entraineurs = [];
            foreach ($results as $row) {
                $entraineurs[] = new Entraineur($row);
            }
            
            return $entraineurs;
        } catch (PDOException $e) {
            error_log("Erreur getTopByExperience: " . $e->getMessage());
            return [];
        }
    }

    // ================== STATISTIQUES PAR SPÉCIALITÉ ==================
    public function getStatsBySpecialite() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    specialite,
                    COUNT(*) as nb_entraineurs,
                    AVG(experience) as experience_moyenne,
                    SUM(CASE WHEN is_actif = 1 THEN 1 ELSE 0 END) as actifs
                FROM entraineurs 
                GROUP BY specialite 
                ORDER BY nb_entraineurs DESC
            ");
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            $stats = [];
            foreach ($results as $row) {
                $stats[] = [
                    'specialite' => $row['specialite'],
                    'nb_entraineurs' => (int)$row['nb_entraineurs'],
                    'experience_moyenne' => round((float)$row['experience_moyenne'], 1),
                    'actifs' => (int)$row['actifs']
                ];
            }
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Erreur getStatsBySpecialite: " . $e->getMessage());
            return [];
        }
    }

    // ================== ENTRAÎNEURS RÉCEMMENT EMBAUCHÉS ==================
    public function getRecentlyHired($limit = 10) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, nom, prenom, email, telephone, specialite, experience, 
                       diplomes, date_embauche, is_actif 
                FROM entraineurs 
                WHERE date_embauche >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                ORDER BY date_embauche DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $results = $stmt->fetchAll();
            
            $entraineurs = [];
            foreach ($results as $row) {
                $entraineurs[] = new Entraineur($row);
            }
            
            return $entraineurs;
        } catch (PDOException $e) {
            error_log("Erreur getRecentlyHired: " . $e->getMessage());
            return [];
        }
    }

    // ================== VÉRIFIER SI UN TÉLÉPHONE EXISTE ==================
    public function telephoneExists($telephone, $excludeId = null) {
        try {
            if ($excludeId) {
                $stmt = $this->pdo->prepare("
                    SELECT id FROM entraineurs 
                    WHERE telephone = ? AND id != ?
                ");
                $stmt->execute([$telephone, $excludeId]);
            } else {
                $stmt = $this->pdo->prepare("
                    SELECT id FROM entraineurs 
                    WHERE telephone = ?
                ");
                $stmt->execute([$telephone]);
            }
            
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Erreur telephoneExists: " . $e->getMessage());
            return true; // Retourner true par sécurité
        }
    }

    // ================== ENTRAÎNEURS PAR NIVEAU D'EXPÉRIENCE ==================
    public function getByExperienceLevel($minExperience = 0, $maxExperience = null) {
        try {
            if ($maxExperience !== null) {
                $stmt = $this->pdo->prepare("
                    SELECT id, nom, prenom, email, telephone, specialite, experience, 
                           diplomes, date_embauche, is_actif 
                    FROM entraineurs 
                    WHERE experience >= ? AND experience <= ? AND is_actif = 1
                    ORDER BY experience DESC, nom ASC
                ");
                $stmt->execute([$minExperience, $maxExperience]);
            } else {
                $stmt = $this->pdo->prepare("
                    SELECT id, nom, prenom, email, telephone, specialite, experience, 
                           diplomes, date_embauche, is_actif 
                    FROM entraineurs 
                    WHERE experience >= ? AND is_actif = 1
                    ORDER BY experience DESC, nom ASC
                ");
                $stmt->execute([$minExperience]);
            }
            
            $results = $stmt->fetchAll();
            
            $entraineurs = [];
            foreach ($results as $row) {
                $entraineurs[] = new Entraineur($row);
            }
            
            return $entraineurs;
        } catch (PDOException $e) {
            error_log("Erreur getByExperienceLevel: " . $e->getMessage());
            return [];
        }
    }
}