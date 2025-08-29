<?php
// controller/EntraineurController.php

require_once __DIR__ . '/../models/EntraineurDAO.php';
require_once __DIR__ . '/../models/Entraineur.php';

class EntraineurController {
    private $dao;

    public function __construct() {
        try {
            $this->dao = new EntraineurDAO();
        } catch (Exception $e) {
            error_log("Erreur initialisation EntraineurController: " . $e->getMessage());
            throw $e;
        }
    }

    // ================== RÉCUPÉRER TOUS LES ENTRAÎNEURS ==================
    public function getAllEntraineurs() {
        try {
            return $this->dao->getAllEntraineurs();
        } catch (Exception $e) {
            error_log("Erreur getAllEntraineurs: " . $e->getMessage());
            return [];
        }
    }

    // ================== RÉCUPÉRER STATISTIQUES ==================
    public function getEntraineurStats() {
        try {
            return $this->dao->getStats();
        } catch (Exception $e) {
            error_log("Erreur getEntraineurStats: " . $e->getMessage());
            return ['total' => 0, 'actifs' => 0, 'inactifs' => 0];
        }
    }

    // ================== RÉCUPÉRER PAR ID ==================
    public function getEntraineurById($id) {
        try {
            if (empty($id) || !is_numeric($id)) {
                return null;
            }
            return $this->dao->getById((int)$id);
        } catch (Exception $e) {
            error_log("Erreur getEntraineurById: " . $e->getMessage());
            return null;
        }
    }

    // ================== CRÉER ENTRAÎNEUR ==================
    public function createEntraineur($data) {
        try {
           // Validation des données requises
            $requiredFields = ['nom', 'prenom', 'email', 'mot_de_passe'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field]) || trim($data[$field]) === '') {
                    return ['success' => false, 'message' => "Le champ {$field} est obligatoire"];
                }
            }

            // Validation de l'email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Format d\'email invalide'];
            }

            // Validation de la longueur du mot de passe
            if (strlen($data['mot_de_passe']) < 6) {
                return ['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères'];
            }

            // Vérifier l'unicité de l'email
            if ($this->dao->emailExists($data['email'])) {
                return ['success' => false, 'message' => 'Cet email est déjà utilisé'];
            }
             // Nettoyer et préparer les données
            $cleanData = [
                'nom' => trim(ucwords(strtolower($data['nom']))),
                'prenom' => trim(ucwords(strtolower($data['prenom']))),
                'email' => trim(strtolower($data['email'])),
                'mot_de_passe' => $data['mot_de_passe'],
                'is_actif' => isset($data['is_actif']) ? (int)$data['is_actif'] : 1
            ];

            $entraineur = new Entraineur($cleanData);

            if ($this->dao->create($entraineur)) {
                return ['success' => true, 'message' => 'Adhérent créé avec succès'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la création de l\'adhérent'];
            }

        } catch (Exception $e) {
            error_log("Erreur createAdherent: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur serveur lors de la création'];
        }
    }

    // ================== MODIFIER ENTRAÎNEUR ==================
    public function updateEntraineur($data) {
        try {
            // Validation des données requises
            $requiredFields = ['id', 'nom', 'prenom', 'email'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field]) || trim($data[$field]) === '') {
                    return ['success' => false, 'message' => "Le champ {$field} est obligatoire"];
                }
            }

            $id = (int)$data['id'];
            if ($id <= 0) {
                return ['success' => false, 'message' => 'ID invalide'];
            }

            // Validation de l'email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Format d\'email invalide'];
            }

            // Validation du mot de passe si fourni
            if (!empty($data['mot_de_passe']) && strlen($data['mot_de_passe']) < 6) {
                return ['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères'];
            }

            // Vérifier l'unicité de l'email (sauf pour lui-même)
            if ($this->dao->emailExists($data['email'], $id)) {
                return ['success' => false, 'message' => 'Cet email est déjà utilisé par un autre utilisateur'];
            }

            // Nettoyer et préparer les données
            $cleanData = [
                'id' => $id,
                'nom' => trim(ucwords(strtolower($data['nom']))),
                'prenom' => trim(ucwords(strtolower($data['prenom']))),
                'email' => trim(strtolower($data['email'])),
                'is_actif' => isset($data['is_actif']) ? (int)$data['is_actif'] : 1
            ];

            // Ajouter le mot de passe seulement s'il est fourni
            if (!empty($data['mot_de_passe'])) {
                $cleanData['mot_de_passe'] = $data['mot_de_passe'];
            }

            $entraineur = new Entraineur($cleanData);

            if ($this->dao->update($entraineur)) {
                return ['success' => true, 'message' => 'Adhérent mis à jour avec succès'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la mise à jour de l\'adhérent'];
            }

        } catch (Exception $e) {
            error_log("Erreur updateAdherent: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur serveur lors de la mise à jour'];
        }
    }


    // ================== SUPPRIMER ENTRAÎNEUR ==================
    public function deleteEntraineur($id) {
        try {
            if (empty($id) || !is_numeric($id) || $id <= 0) {
                return ['success' => false, 'message' => 'ID invalide'];
            }

            $id = (int)$id;

            // Vérifier si l'entraîneur existe
            $entraineur = $this->dao->getById($id);
            if (!$entraineur) {
                return ['success' => false, 'message' => 'Entraîneur non trouvé'];
            }

            if ($this->dao->delete($id)) {
                return ['success' => true, 'message' => 'Entraîneur supprimé avec succès'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la suppression de l\'entraîneur'];
            }

        } catch (Exception $e) {
            error_log("Erreur deleteEntraineur: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur serveur lors de la suppression'];
        }
    }

    // ================== RECHERCHER ENTRAÎNEURS ==================
    public function searchEntraineurs($keyword) {
        try {
            if (empty($keyword) || trim($keyword) === '') {
                return $this->getAllEntraineurs();
            }
            return $this->dao->search(trim($keyword));
        } catch (Exception $e) {
            error_log("Erreur searchEntraineurs: " . $e->getMessage());
            return [];
        }
    }

    // ================== RECHERCHER PAR SPÉCIALITÉ ==================
    public function getEntraineursBySpecialite($specialite) {
        try {
            if (empty($specialite) || trim($specialite) === '') {
                return [];
            }
            return $this->dao->getBySpecialite(trim($specialite));
        } catch (Exception $e) {
            error_log("Erreur getEntraineursBySpecialite: " . $e->getMessage());
            return [];
        }
    }

    // ================== EXPORTER CSV ==================
    public function exportCSV() {
        try {
            $entraineurs = $this->getAllEntraineurs();
            
          
            // Headers pour le téléchargement
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="entraineur_' . date('Y-m-d_H-i-s') . '.csv"');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            
            $output = fopen('php://output', 'w');
            
            // BOM UTF-8 pour Excel
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // En-têtes CSV
            fputcsv($output, [
                'ID',
                'Nom', 
                'Prénom', 
                'Email', 
                'Date d\'inscription', 
                'Statut'
            ], ';');
            
            // Données
            foreach ($entraineurs as $entraineur) {
                fputcsv($output, [
                    $entraineur->getId(),
                    $entraineur->getNom(),
                    $entraineur->getPrenom(),
                    $entraineur->getEmail(),
                    $entraineur->getDateInscription()? 
                        date('d/m/Y H:i', strtotime($entraineur->getDateInscription())) : 
                        'Non définie',
                    $entraineur->getIsActif() ? 'Actif' : 'Inactif'
                ], ';');
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            error_log("Erreur exportCSV: " . $e->getMessage());
            http_response_code(500);
            echo "Erreur lors de l'export";
            exit;
        }
    }

    // ================== GESTION AJAX ==================
    public function handleAjaxRequest() {
        // Vérification de la requête
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['ajax'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Requête invalide']);
            exit;
        }

        $action = $_POST['action'] ?? '';
        $response = ['success' => false, 'message' => 'Action inconnue'];

        try {
            switch ($action) {
                case 'get':
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id <= 0) {
                        $response = ['success' => false, 'message' => 'ID invalide'];
                        break;
                    }
                    
                    $entraineur = $this->getEntraineurById($id);
                    if ($entraineur) {
                        $response = ['success' => true, 'data' => $entraineur];
                    } else {
                        $response = ['success' => false, 'message' => 'Entraîneur non trouvé'];
                    }
                    break;

                case 'create':
                    $response = $this->createEntraineur($_POST);
                    break;

                case 'update':
                    $response = $this->updateEntraineur($_POST);
                    break;

                case 'delete':
                    $id = (int)($_POST['id'] ?? 0);
                    $response = $this->deleteEntraineur($id);
                    break;

                case 'stats':
                    $stats = $this->getEntraineurStats();
                    $response = ['success' => true, 'data' => $stats];
                    break;

                case 'search':
                    $keyword = $_POST['keyword'] ?? '';
                    $entraineurs = $this->searchEntraineurs($keyword);
                    
                    // Convertir en array pour JSON
                    $data = [];
                    foreach ($entraineurs as $entraineur) {
                        $data[] = $entraineur->toArray();
                    }
                    
                    $response = ['success' => true, 'data' => $data];
                    break;

                case 'search_by_specialite':
                    $specialite = $_POST['specialite'] ?? '';
                    $entraineurs = $this->getEntraineursBySpecialite($specialite);
                    
                    // Convertir en array pour JSON
                    $data = [];
                    foreach ($entraineurs as $entraineur) {
                        $data[] = $entraineur->toArray();
                    }
                    
                    $response = ['success' => true, 'data' => $data];
                    break;

                case 'export':
                    $this->exportCSV();
                    // Ne pas retourner de JSON pour l'export
                    break;

                case 'check_email':
                    $email = $_POST['email'] ?? '';
                    $excludeId = (int)($_POST['exclude_id'] ?? 0);
        
                    $exists = $this->dao->emailExists($email, $excludeId ?: null);
                    $response = ['exists' => $exists];
                    break;    
           
                default:
                    $response = ['success' => false, 'message' => 'Action non supportée: ' . $action];
            }

        } catch (Exception $e) {
            error_log("Erreur AJAX EntraineurController [{$action}]: " . $e->getMessage());
            $response = [
                'success' => false, 
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ];
        }

        // Retourner la réponse JSON
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ================== UTILITAIRES ==================
    public function validateData($data, $requiredFields) {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                return false;
            }
        }
        return true;
    }

    public function sanitizeData($data) {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    // ================== MÉTHODES SPÉCIFIQUES AUX ENTRAÎNEURS ==================
    
    // Obtenir la liste des spécialités disponibles
    public function getSpecialites() {
        try {
            return $this->dao->getAllSpecialites();
        } catch (Exception $e) {
            error_log("Erreur getSpecialites: " . $e->getMessage());
            return [];
        }
    }

    // Obtenir les entraîneurs les plus expérimentés
    public function getTopEntraineurs($limit = 5) {
        try {
            return $this->dao->getTopByExperience($limit);
        } catch (Exception $e) {
            error_log("Erreur getTopEntraineurs: " . $e->getMessage());
            return [];
        }
    }
}