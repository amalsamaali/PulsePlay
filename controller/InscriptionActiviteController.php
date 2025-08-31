<?php
// controller/InscriptionActiviteController.php

require_once __DIR__ . '/../models/InscriptionActiviteDAO.php';
require_once __DIR__ . '/../models/InscriptionActivite.php';

class InscriptionActiviteController {
    private $dao;

    public function __construct() {
        try {
            $this->dao = new InscriptionActiviteDAO();
        } catch (Exception $e) {
            error_log("Erreur initialisation InscriptionActiviteController: " . $e->getMessage());
            throw $e;
        }
    }

    // ================== S'INSCRIRE À UNE ACTIVITÉ ==================
    public function registerToActivity($adherentId, $activiteId) {
        try {
            // Validation des paramètres
            if (empty($adherentId) || empty($activiteId) || !is_numeric($adherentId) || !is_numeric($activiteId)) {
                return ['success' => false, 'message' => 'Paramètres invalides'];
            }

            // Vérifier si l'inscription existe déjà
            if ($this->dao->inscriptionExists($adherentId, $activiteId)) {
                return ['success' => false, 'message' => 'Vous êtes déjà inscrit à cette activité'];
            }

            // Créer l'inscription
            $inscription = new InscriptionActivite([
                'adherent_id' => $adherentId,
                'activite_id' => $activiteId,
                'date_inscription' => date('Y-m-d H:i:s')
            ]);

            if ($this->dao->create($inscription)) {
                return ['success' => true, 'message' => 'Inscription réussie !'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de l\'inscription'];
            }

        } catch (Exception $e) {
            error_log("Erreur registerToActivity: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur serveur lors de l\'inscription'];
        }
    }

    // ================== SE DÉSINSCRIRE D'UNE ACTIVITÉ ==================
    public function unregisterFromActivity($adherentId, $activiteId) {
        try {
            // Validation des paramètres
            if (empty($adherentId) || empty($activiteId) || !is_numeric($adherentId) || !is_numeric($activiteId)) {
                return ['success' => false, 'message' => 'Paramètres invalides'];
            }

            // Vérifier si l'inscription existe
            if (!$this->dao->inscriptionExists($adherentId, $activiteId)) {
                return ['success' => false, 'message' => 'Vous n\'êtes pas inscrit à cette activité'];
            }

            // Annuler l'inscription
            if ($this->dao->cancelInscription($adherentId, $activiteId)) {
                return ['success' => true, 'message' => 'Désinscription réussie !'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la désinscription'];
            }

        } catch (Exception $e) {
            error_log("Erreur unregisterFromActivity: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur serveur lors de la désinscription'];
        }
    }

    // ================== RÉCUPÉRER LES ACTIVITÉS D'UN ADHÉRENT ==================
    public function getAdherentActivities($adherentId) {
        try {
            if (empty($adherentId) || !is_numeric($adherentId)) {
                return [];
            }
            return $this->dao->getActivitesByAdherent($adherentId);
        } catch (Exception $e) {
            error_log("Erreur getAdherentActivities: " . $e->getMessage());
            return [];
        }
    }

    // ================== RÉCUPÉRER LES IDS DES ACTIVITÉS D'UN ADHÉRENT ==================
    public function getAdherentActivityIds($adherentId) {
        try {
            if (empty($adherentId) || !is_numeric($adherentId)) {
                return [];
            }
            return $this->dao->getActiviteIdsByAdherent($adherentId);
        } catch (Exception $e) {
            error_log("Erreur getAdherentActivityIds: " . $e->getMessage());
            return [];
        }
    }

    // ================== RÉCUPÉRER TOUTES LES INSCRIPTIONS ==================
    public function getAllInscriptions() {
        try {
            return $this->dao->getAllInscriptions();
        } catch (Exception $e) {
            error_log("Erreur getAllInscriptions: " . $e->getMessage());
            return [];
        }
    }

    // ================== RÉCUPÉRER LES INSCRIPTIONS D'UN ADHÉRENT ==================
    public function getInscriptionsByAdherent($adherentId) {
        try {
            if (empty($adherentId) || !is_numeric($adherentId)) {
                return [];
            }
            return $this->dao->getInscriptionsByAdherent($adherentId);
        } catch (Exception $e) {
            error_log("Erreur getInscriptionsByAdherent: " . $e->getMessage());
            return [];
        }
    }

    // ================== RÉCUPÉRER LES INSCRIPTIONS D'UNE ACTIVITÉ ==================
    public function getInscriptionsByActivite($activiteId) {
        try {
            if (empty($activiteId) || !is_numeric($activiteId)) {
                return [];
            }
            return $this->dao->getInscriptionsByActivite($activiteId);
        } catch (Exception $e) {
            error_log("Erreur getInscriptionsByActivite: " . $e->getMessage());
            return [];
        }
    }

    // ================== RÉCUPÉRER LES STATISTIQUES ==================
    public function getInscriptionStats() {
        try {
            return $this->dao->getStats();
        } catch (Exception $e) {
            error_log("Erreur getInscriptionStats: " . $e->getMessage());
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
    public function searchInscriptions($keyword) {
        try {
            if (empty($keyword) || trim($keyword) === '') {
                return $this->getAllInscriptions();
            }
            return $this->dao->search(trim($keyword));
        } catch (Exception $e) {
            error_log("Erreur searchInscriptions: " . $e->getMessage());
            return [];
        }
    }

    // ================== VÉRIFIER SI UNE INSCRIPTION EXISTE ==================
    public function inscriptionExists($adherentId, $activiteId) {
        try {
            if (empty($adherentId) || empty($activiteId) || !is_numeric($adherentId) || !is_numeric($activiteId)) {
                return false;
            }
            return $this->dao->inscriptionExists($adherentId, $activiteId);
        } catch (Exception $e) {
            error_log("Erreur inscriptionExists: " . $e->getMessage());
            return false;
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
                case 'register':
                    $adherentId = (int)($_POST['adherent_id'] ?? 0);
                    $activiteId = (int)($_POST['activite_id'] ?? 0);
                    $response = $this->registerToActivity($adherentId, $activiteId);
                    break;

                case 'unregister':
                    $adherentId = (int)($_POST['adherent_id'] ?? 0);
                    $activiteId = (int)($_POST['activite_id'] ?? 0);
                    $response = $this->unregisterFromActivity($adherentId, $activiteId);
                    break;

                case 'get_adherent_activities':
                    $adherentId = (int)($_POST['adherent_id'] ?? 0);
                    $activities = $this->getAdherentActivities($adherentId);
                    $response = ['success' => true, 'data' => $activities];
                    break;

                case 'get_adherent_activity_ids':
                    $adherentId = (int)($_POST['adherent_id'] ?? 0);
                    $activityIds = $this->getAdherentActivityIds($adherentId);
                    $response = ['success' => true, 'data' => $activityIds];
                    break;

                case 'get_all':
                    $inscriptions = $this->getAllInscriptions();
                    $data = [];
                    foreach ($inscriptions as $inscription) {
                        $data[] = $inscription->toArray();
                    }
                    $response = ['success' => true, 'data' => $data];
                    break;

                case 'get_by_adherent':
                    $adherentId = (int)($_POST['adherent_id'] ?? 0);
                    $inscriptions = $this->getInscriptionsByAdherent($adherentId);
                    $data = [];
                    foreach ($inscriptions as $inscription) {
                        $data[] = $inscription->toArray();
                    }
                    $response = ['success' => true, 'data' => $data];
                    break;

                case 'get_by_activite':
                    $activiteId = (int)($_POST['activite_id'] ?? 0);
                    $inscriptions = $this->getInscriptionsByActivite($activiteId);
                    $data = [];
                    foreach ($inscriptions as $inscription) {
                        $data[] = $inscription->toArray();
                    }
                    $response = ['success' => true, 'data' => $data];
                    break;

                case 'stats':
                    $stats = $this->getInscriptionStats();
                    $response = ['success' => true, 'data' => $stats];
                    break;

                case 'search':
                    $keyword = $_POST['keyword'] ?? '';
                    $inscriptions = $this->searchInscriptions($keyword);
                    $data = [];
                    foreach ($inscriptions as $inscription) {
                        $data[] = $inscription->toArray();
                    }
                    $response = ['success' => true, 'data' => $data];
                    break;

                case 'check_exists':
                    $adherentId = (int)($_POST['adherent_id'] ?? 0);
                    $activiteId = (int)($_POST['activite_id'] ?? 0);
                    $exists = $this->inscriptionExists($adherentId, $activiteId);
                    $response = ['success' => true, 'exists' => $exists];
                    break;

                default:
                    $response = ['success' => false, 'message' => 'Action non supportée: ' . $action];
            }

        } catch (Exception $e) {
            error_log("Erreur AJAX InscriptionActiviteController [{$action}]: " . $e->getMessage());
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
}
