<?php
// controller/AdherentController.php

require_once __DIR__ . '/../models/AdherentDAO.php';
require_once __DIR__ . '/../models/Adherent.php';


class AdherentController {
    private $dao;

    public function __construct() {
        try {
            $this->dao = new AdherentDAO();
        } catch (Exception $e) {
            error_log("Erreur initialisation AdherentController: " . $e->getMessage());
            throw $e;
        }
    }

    // ================== RÉCUPÉRER TOUS LES ADHÉRENTS ==================
    public function getAllAdherents() {
        try {
            return $this->dao->getAllAdherents();
        } catch (Exception $e) {
            error_log("Erreur getAllAdherents: " . $e->getMessage());
            return [];
        }
    }

    // ================== RÉCUPÉRER STATISTIQUES ==================
    public function getAdherentStats() {
        try {
            return $this->dao->getStats();
        } catch (Exception $e) {
            error_log("Erreur getAdherentStats: " . $e->getMessage());
            return ['total' => 0, 'actifs' => 0, 'inactifs' => 0];
        }
    }

    // ================== RÉCUPÉRER PAR ID ==================
    public function getAdherentById($id) {
        try {
            if (empty($id) || !is_numeric($id)) {
                return null;
            }
            return $this->dao->getById((int)$id);
        } catch (Exception $e) {
            error_log("Erreur getAdherentById: " . $e->getMessage());
            return null;
        }
    }

    // ================== CRÉER ADHÉRENT ==================
    public function createAdherent($data) {
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

            $adherent = new Adherent($cleanData);

            if ($this->dao->create($adherent)) {
                return ['success' => true, 'message' => 'Adhérent créé avec succès'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la création de l\'adhérent'];
            }

        } catch (Exception $e) {
            error_log("Erreur createAdherent: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur serveur lors de la création'];
        }
    }

    // ================== MODIFIER ADHÉRENT ==================
    public function updateAdherent($data) {
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

            $adherent = new Adherent($cleanData);

            if ($this->dao->update($adherent)) {
                return ['success' => true, 'message' => 'Adhérent mis à jour avec succès'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la mise à jour de l\'adhérent'];
            }

        } catch (Exception $e) {
            error_log("Erreur updateAdherent: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur serveur lors de la mise à jour'];
        }
    }

    // ================== SUPPRIMER ADHÉRENT ==================
    public function deleteAdherent($id) {
        try {
            if (empty($id) || !is_numeric($id) || $id <= 0) {
                return ['success' => false, 'message' => 'ID invalide'];
            }

            $id = (int)$id;

            // Vérifier si l'adhérent existe
            $adherent = $this->dao->getById($id);
            if (!$adherent) {
                return ['success' => false, 'message' => 'Adhérent non trouvé'];
            }

            if ($this->dao->delete($id)) {
                return ['success' => true, 'message' => 'Adhérent supprimé avec succès'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la suppression de l\'adhérent'];
            }

        } catch (Exception $e) {
            error_log("Erreur deleteAdherent: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur serveur lors de la suppression'];
        }
    }

    // ================== RECHERCHER ADHÉRENTS ==================
    public function searchAdherents($keyword) {
        try {
            if (empty($keyword) || trim($keyword) === '') {
                return $this->getAllAdherents();
            }
            return $this->dao->search(trim($keyword));
        } catch (Exception $e) {
            error_log("Erreur searchAdherents: " . $e->getMessage());
            return [];
        }
    }

    // ================== EXPORTER CSV ==================
    public function exportCSV() {
        try {
            $adherents = $this->getAllAdherents();
            
            // Headers pour le téléchargement
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="adherents_' . date('Y-m-d_H-i-s') . '.csv"');
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
            foreach ($adherents as $adherent) {
                fputcsv($output, [
                    $adherent->getId(),
                    $adherent->getNom(),
                    $adherent->getPrenom(),
                    $adherent->getEmail(),
                    $adherent->getDateInscription() ? 
                        date('d/m/Y H:i', strtotime($adherent->getDateInscription())) : 
                        'Non définie',
                    $adherent->getIsActif() ? 'Actif' : 'Inactif'
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
                    
                    $adherent = $this->getAdherentById($id);
                    if ($adherent) {
                        $response = ['success' => true, 'data' => $adherent];
                    } else {
                        $response = ['success' => false, 'message' => 'Adhérent non trouvé'];
                    }
                    break;

                case 'create':
                    $response = $this->createAdherent($_POST);
                    break;

                case 'update':
                    $response = $this->updateAdherent($_POST);
                    break;

                case 'delete':
                    $id = (int)($_POST['id'] ?? 0);
                    $response = $this->deleteAdherent($id);
                    break;

                case 'stats':
                    $stats = $this->getAdherentStats();
                    $response = ['success' => true, 'data' => $stats];
                    break;

                case 'search':
                    $keyword = $_POST['keyword'] ?? '';
                    $adherents = $this->searchAdherents($keyword);
                    
                    // Convertir en array pour JSON
                    $data = [];
                    foreach ($adherents as $adherent) {
                        $data[] = $adherent->toArray();
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
            error_log("Erreur AJAX AdherentController [{$action}]: " . $e->getMessage());
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
}