<?php
// models/InscriptionActiviteDAO.php

require_once __DIR__ . '/../config.php';
require_once 'InscriptionActivite.php';

class InscriptionActiviteDAO {
    private $pdo;

       public function __construct() {
        try {
            $this->pdo = Database::connect();
        } catch (Exception $e) {
            error_log("Erreur connexion InscriptionActiviteDAO: " . $e->getMessage());
            throw new Exception("Erreur de connexion à la base de données");
        }
    }

    // ================== CRÉER UNE INSCRIPTION ==================
    public function create(InscriptionActivite $inscription) {
        try {
            if (!$inscription->isValid()) {
                return false;
            }

            $sql = "INSERT INTO inscriptions (adherent_id, activite_id, date_inscription) 
                    VALUES (:adherent_id, :activite_id, :date_inscription)";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                'adherent_id' => $inscription->getAdherentId(),
                'activite_id' => $inscription->getActiviteId(),
                'date_inscription' => $inscription->getDateInscription()
            ]);

            if ($result) {
                $inscription->setId($this->pdo->lastInsertId());
                return true;
            }
            return false;

        } catch (PDOException $e) {
            error_log("Erreur create InscriptionActiviteDAO: " . $e->getMessage());
            return false;
        }
    }

    // ================== RÉCUPÉRER PAR ID ==================
    public function getById($id) {
        try {
                        $sql = "SELECT i.*, 
                            CONCAT(u.prenom, ' ', u.nom) as nom_adherent,
                            a.nom as nom_activite
                     FROM inscriptions i
                     LEFT JOIN utilisateurs u ON i.adherent_id = u.id
                     LEFT JOIN activites_sportives a ON i.activite_id = a.id
                     WHERE i.id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch();

            return $data ? new InscriptionActivite($data) : null;

        } catch (PDOException $e) {
            error_log("Erreur getById InscriptionActiviteDAO: " . $e->getMessage());
            return null;
        }
    }

    // ================== RÉCUPÉRER TOUTES LES INSCRIPTIONS ==================
    public function getAllInscriptions() {
        try {
                        $sql = "SELECT i.*, 
                            CONCAT(u.prenom, ' ', u.nom) as nom_adherent,
                            a.nom as nom_activite
                     FROM inscriptions i
                     LEFT JOIN utilisateurs u ON i.adherent_id = u.id
                     LEFT JOIN activites_sportives a ON i.activite_id = a.id
                     ORDER BY i.date_inscription DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll();

            $inscriptions = [];
            foreach ($data as $row) {
                $inscriptions[] = new InscriptionActivite($row);
            }
            return $inscriptions;

        } catch (PDOException $e) {
            error_log("Erreur getAllInscriptions InscriptionActiviteDAO: " . $e->getMessage());
            return [];
        }
    }

    // ================== RÉCUPÉRER LES INSCRIPTIONS D'UN ADHÉRENT ==================
    public function getInscriptionsByAdherent($adherentId) {
        try {
                        $sql = "SELECT i.*, 
                            CONCAT(u.prenom, ' ', u.nom) as nom_adherent,
                            a.nom as nom_activite
                     FROM inscriptions i
                     LEFT JOIN utilisateurs u ON i.adherent_id = u.id
                     LEFT JOIN activites_sportives a ON i.activite_id = a.id
                     WHERE i.adherent_id = :adherent_id
                     ORDER BY i.date_inscription DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['adherent_id' => $adherentId]);
            $data = $stmt->fetchAll();

            $inscriptions = [];
            foreach ($data as $row) {
                $inscriptions[] = new InscriptionActivite($row);
            }
            return $inscriptions;

        } catch (PDOException $e) {
            error_log("Erreur getInscriptionsByAdherent InscriptionActiviteDAO: " . $e->getMessage());
            return [];
        }
    }

    // ================== RÉCUPÉRER LES INSCRIPTIONS D'UNE ACTIVITÉ ==================
    public function getInscriptionsByActivite($activiteId) {
        try {
                        $sql = "SELECT i.*, 
                            CONCAT(u.prenom, ' ', u.nom) as nom_adherent,
                            a.nom as nom_activite
                     FROM inscriptions i
                     LEFT JOIN utilisateurs u ON i.adherent_id = u.id
                     LEFT JOIN activites_sportives a ON i.activite_id = a.id
                     WHERE i.activite_id = :activite_id
                     ORDER BY i.date_inscription DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['activite_id' => $activiteId]);
            $data = $stmt->fetchAll();

            $inscriptions = [];
            foreach ($data as $row) {
                $inscriptions[] = new InscriptionActivite($row);
            }
            return $inscriptions;

        } catch (PDOException $e) {
            error_log("Erreur getInscriptionsByActivite InscriptionActiviteDAO: " . $e->getMessage());
            return [];
        }
    }

    // ================== VÉRIFIER SI UNE INSCRIPTION EXISTE ==================
    public function inscriptionExists($adherentId, $activiteId) {
        try {
            $sql = "SELECT COUNT(*) FROM inscriptions 
                    WHERE adherent_id = :adherent_id AND activite_id = :activite_id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'adherent_id' => $adherentId,
                'activite_id' => $activiteId
            ]);
            
            return $stmt->fetchColumn() > 0;

        } catch (PDOException $e) {
            error_log("Erreur inscriptionExists InscriptionActiviteDAO: " . $e->getMessage());
            return false;
        }
    }

    // ================== RÉCUPÉRER LES ACTIVITÉS D'UN ADHÉRENT ==================
    public function getActivitesByAdherent($adherentId) {
        try {
                        $sql = "SELECT a.id, a.nom, a.description, a.entraineur_id,
                            CONCAT(u.prenom, ' ', u.nom) as nom_entraineur,
                            i.date_inscription
                     FROM activites_sportives a
                     INNER JOIN inscriptions i ON a.id = i.activite_id
                     LEFT JOIN utilisateurs u ON a.entraineur_id = u.id
                     WHERE i.adherent_id = :adherent_id
                     ORDER BY i.date_inscription DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['adherent_id' => $adherentId]);
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Erreur getActivitesByAdherent InscriptionActiviteDAO: " . $e->getMessage());
            return [];
        }
    }

    // ================== RÉCUPÉRER LES IDS DES ACTIVITÉS D'UN ADHÉRENT ==================
    public function getActiviteIdsByAdherent($adherentId) {
        try {
            $sql = "SELECT activite_id FROM inscriptions 
                    WHERE adherent_id = :adherent_id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['adherent_id' => $adherentId]);
            $data = $stmt->fetchAll();

            return array_column($data, 'activite_id');

        } catch (PDOException $e) {
            error_log("Erreur getActiviteIdsByAdherent InscriptionActiviteDAO: " . $e->getMessage());
            return [];
        }
    }

    // ================== METTRE À JOUR UNE INSCRIPTION ==================
    public function update(InscriptionActivite $inscription) {
        try {
            if (!$inscription->isValid() || !$inscription->getId()) {
                return false;
            }

            $sql = "UPDATE inscriptions 
                    SET adherent_id = :adherent_id, 
                        activite_id = :activite_id, 
                        date_inscription = :date_inscription
                    WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'id' => $inscription->getId(),
                'adherent_id' => $inscription->getAdherentId(),
                'activite_id' => $inscription->getActiviteId(),
                'date_inscription' => $inscription->getDateInscription()
            ]);

        } catch (PDOException $e) {
            error_log("Erreur update InscriptionActiviteDAO: " . $e->getMessage());
            return false;
        }
    }

    // ================== ANNULER UNE INSCRIPTION ==================
    public function cancelInscription($adherentId, $activiteId) {
        try {
            $sql = "DELETE FROM inscriptions 
                    WHERE adherent_id = :adherent_id AND activite_id = :activite_id";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'adherent_id' => $adherentId,
                'activite_id' => $activiteId
            ]);

        } catch (PDOException $e) {
            error_log("Erreur cancelInscription InscriptionActiviteDAO: " . $e->getMessage());
            return false;
        }
    }

    // ================== SUPPRIMER UNE INSCRIPTION ==================
    public function delete($id) {
        try {
            $sql = "DELETE FROM inscriptions WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['id' => $id]);

        } catch (PDOException $e) {
            error_log("Erreur delete InscriptionActiviteDAO: " . $e->getMessage());
            return false;
        }
    }

    // ================== RÉCUPÉRER LES STATISTIQUES ==================
    public function getStats() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_inscriptions,
                        COUNT(*) as inscriptions_actives,
                        0 as inscriptions_annulees,
                        COUNT(DISTINCT adherent_id) as adherents_inscrits,
                        COUNT(DISTINCT activite_id) as activites_avec_inscriptions
                    FROM inscriptions";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetch();

        } catch (PDOException $e) {
            error_log("Erreur getStats InscriptionActiviteDAO: " . $e->getMessage());
            return [
                'total_inscriptions' => 0,
                'inscriptions_actives' => 0,
                'inscriptions_annulees' => 0,
                'adherents_inscrits' => 0,
                'activites_avec_inscriptions' => 0
            ];
        }
    }

    // ================== RECHERCHER DES INSCRIPTIONS ==================
    public function search($keyword) {
        try {
                        $sql = "SELECT i.*, 
                            CONCAT(u.prenom, ' ', u.nom) as nom_adherent,
                            a.nom as nom_activite
                     FROM inscriptions i
                     LEFT JOIN utilisateurs u ON i.adherent_id = u.id
                     LEFT JOIN activites_sportives a ON i.activite_id = a.id
                     WHERE CONCAT(u.prenom, ' ', u.nom) LIKE :keyword 
                        OR a.nom LIKE :keyword
                     ORDER BY i.date_inscription DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['keyword' => "%{$keyword}%"]);
            $data = $stmt->fetchAll();

            $inscriptions = [];
            foreach ($data as $row) {
                $inscriptions[] = new InscriptionActivite($row);
            }
            return $inscriptions;

        } catch (PDOException $e) {
            error_log("Erreur search InscriptionActiviteDAO: " . $e->getMessage());
            return [];
        }
    }
}
