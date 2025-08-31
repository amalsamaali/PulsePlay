<?php
require_once __DIR__ . '/../models/Planning.php';
require_once __DIR__ . '/../config.php';

class PlanningController
{
    /** @var PDO */
    private PDO $db;

    public function __construct()
    {
        // Utilise $pdo global si présent, sinon tente Database::connect(), sinon fallback
        if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
            $this->db = $GLOBALS['pdo'];
            return;
        }

        if (class_exists('Database') && method_exists('Database', 'connect')) {
            $this->db = Database::connect();
            return;
        }

        // Fallback (évite la panne si config n'initialise pas $pdo)
        $this->db = new PDO(
            'mysql:host=localhost;dbname=web_sport;charset=utf8',
            'root',
            '',
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    /* =========================
     *        ROUTEUR AJAX
     * ========================= */
    public function handleAjaxRequest(): void
    {
        header('Content-Type: application/json');

        $action = $_POST['action'] ?? null;
        if (!$action) {
            echo json_encode(['success' => false, 'message' => 'Action non spécifiée']);
            return;
        }

        try {
            switch ($action) {
                case 'create':            $this->ajaxCreate(); break;
                case 'get':               $this->ajaxGet(); break;
                case 'get_all':           $this->ajaxGetAll(); break;
                case 'update':            $this->ajaxUpdate(); break;
                case 'delete':            $this->ajaxDelete(); break;
                case 'stats':             $this->ajaxStats(); break;
                case 'export':            $this->exportPlanningsCSV(); break;
                case 'activites':         $this->ajaxActivitesDisponibles(); break;
                case 'check_conflicts':   $this->ajaxCheckConflicts(); break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
            }
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
        }
    }

    /* =========================
     *        CRUD (AJAX)
     * ========================= */
    private function ajaxCreate(): void
    {
        $planning = new Planning($_POST);
        $errors   = $planning->validate();
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => 'Données invalides', 'errors' => $errors]);
            return;
        }

        // Vérifier que l'activité existe
        if (!$this->activiteExists($planning->getActiviteId())) {
            echo json_encode(['success' => false, 'message' => 'Activité inexistante']);
            return;
        }

        // Conflits
        $conflicts = $this->findConflicts(
            $planning->getJourSemaine(),
            $planning->getHeureDebut(),
            $planning->getHeureFin(),
            $planning->getSalle()
        );
        if (!empty($conflicts)) {
            echo json_encode([
                'success' => false,
                'message' => "Conflit d'horaire détecté dans la salle " . $planning->getSalle(),
                'conflicts' => $conflicts
            ]);
            return;
        }

        $sql = "INSERT INTO plannings (activite_id, jour_semaine, heure_debut, heure_fin, salle)
                VALUES (:activite_id, :jour_semaine, :heure_debut, :heure_fin, :salle)";
        $ok = $this->db->prepare($sql)->execute([
            ':activite_id'  => $planning->getActiviteId(),
            ':jour_semaine' => $planning->getJourSemaine(),
            ':heure_debut'  => $planning->getHeureDebut(),
            ':heure_fin'    => $planning->getHeureFin(),
            ':salle'        => $planning->getSalle(),
        ]);

        echo json_encode($ok
            ? ['success' => true, 'message' => 'Planning créé avec succès']
            : ['success' => false, 'message' => 'Erreur lors de la création du planning']
        );
    }

    private function ajaxGet(): void
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID non spécifié']);
            return;
        }

        $planning = $this->getPlanningById($id);
        echo json_encode($planning
            ? ['success' => true, 'data' => $planning->toArray()]
            : ['success' => false, 'message' => 'Planning non trouvé']
        );
    }

    private function ajaxGetAll(): void
    {
        $rows = $this->getAllPlannings();
        $data = array_map(fn($p) => $p->toArray(), $rows);
        echo json_encode(['success' => true, 'data' => $data]);
    }

    private function ajaxUpdate(): void
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID non spécifié']);
            return;
        }

        $planning = new Planning($_POST);
        $planning->setId($id);

        $errors = $planning->validate();
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => 'Données invalides', 'errors' => $errors]);
            return;
        }

        // Vérifier que l'activité existe
        if (!$this->activiteExists($planning->getActiviteId())) {
            echo json_encode(['success' => false, 'message' => 'Activité inexistante']);
            return;
        }

        $conflicts = $this->findConflicts(
            $planning->getJourSemaine(),
            $planning->getHeureDebut(),
            $planning->getHeureFin(),
            $planning->getSalle(),
            $planning->getId() // exclure l'enregistrement actuel
        );
        if (!empty($conflicts)) {
            echo json_encode([
                'success' => false,
                'message' => "Conflit d'horaire détecté dans la salle " . $planning->getSalle(),
                'conflicts' => $conflicts
            ]);
            return;
        }

        $sql = "UPDATE plannings
                SET activite_id = :activite_id,
                    jour_semaine = :jour_semaine,
                    heure_debut = :heure_debut,
                    heure_fin = :heure_fin,
                    salle = :salle
                WHERE id = :id";
        $ok = $this->db->prepare($sql)->execute([
            ':id'           => $planning->getId(),
            ':activite_id'  => $planning->getActiviteId(),
            ':jour_semaine' => $planning->getJourSemaine(),
            ':heure_debut'  => $planning->getHeureDebut(),
            ':heure_fin'    => $planning->getHeureFin(),
            ':salle'        => $planning->getSalle(),
        ]);

        echo json_encode($ok
            ? ['success' => true, 'message' => 'Planning mis à jour avec succès']
            : ['success' => false, 'message' => 'Erreur lors de la mise à jour']
        );
    }

    private function ajaxDelete(): void
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID non spécifié']);
            return;
        }

        $ok = $this->db->prepare("DELETE FROM plannings WHERE id = :id")->execute([':id' => $id]);
        echo json_encode($ok
            ? ['success' => true, 'message' => 'Planning supprimé avec succès']
            : ['success' => false, 'message' => 'Erreur lors de la suppression']
        );
    }

    private function ajaxStats(): void
    {
        $stats = $this->getPlanningStatistics();
        echo json_encode(['success' => true, 'data' => $stats]);
    }

    private function ajaxActivitesDisponibles(): void
    {
        // CORRECTION : Récupérer les vraies activités depuis la base de données
        $sql = "SELECT a.id, a.nom, a.description,
                       CONCAT(u.prenom, ' ', u.nom) as entraineur,
                       u.id as entraineur_id
                FROM activites_sportives a
                LEFT JOIN utilisateurs u ON a.entraineur_id = u.id
                ORDER BY a.nom";
        
        try {
            $stmt = $this->db->query($sql);
            $activites = $stmt->fetchAll();
            
            // Formater les données pour l'affichage
            foreach ($activites as &$activite) {
                if (empty($activite['entraineur'])) {
                    $activite['entraineur'] = 'Non assigné';
                }
            }
            
            echo json_encode(['success' => true, 'data' => $activites]);
        } catch (Exception $e) {
            // Fallback si pas d'activités en base
            echo json_encode([
                'success' => true, 
                'data' => [],
                'message' => 'Aucune activité disponible. Veuillez d\'abord créer des activités sportives.'
            ]);
        }
    }

    private function ajaxCheckConflicts(): void
    {
        $jour      = $_POST['jour_semaine'] ?? '';
        $debut     = $_POST['heure_debut'] ?? '';
        $fin       = $_POST['heure_fin'] ?? '';
        $salle     = $_POST['salle'] ?? '';
        $excludeId = isset($_POST['exclude_id']) ? (int)$_POST['exclude_id'] : null;

        $conflicts = $this->findConflicts($jour, $debut, $fin, $salle, $excludeId);
        echo json_encode([
            'success'        => true,
            'has_conflicts'  => !empty($conflicts),
            'conflicts'      => $conflicts
        ]);
    }

    /* =========================
     *   MÉTHODES MÉTIER/DAO
     * ========================= */
    /** @return Planning[] */
    public function getAllPlannings(): array
    {
        // CORRECTION : Vraies jointures avec activites_sportives et utilisateurs
        $sql = "SELECT p.*,
                       a.nom AS nom_activite,
                       a.description AS description_activite,
                       CONCAT(u.prenom, ' ', u.nom) AS nom_entraineur,
                       u.id AS entraineur_id
                FROM plannings p
                LEFT JOIN activites_sportives a ON p.activite_id = a.id
                LEFT JOIN utilisateurs u ON a.entraineur_id = u.id
                ORDER BY
                    CASE p.jour_semaine
                        WHEN 'Lundi' THEN 1
                        WHEN 'Mardi' THEN 2
                        WHEN 'Mercredi' THEN 3
                        WHEN 'Jeudi' THEN 4
                        WHEN 'Vendredi' THEN 5
                        WHEN 'Samedi' THEN 6
                        WHEN 'Dimanche' THEN 7
                    END,
                    p.heure_debut";
        
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();

        $out = [];
        foreach ($rows as $r) {
            $out[] = new Planning($r);
        }
        return $out;
    }

    public function getPlanningById(int $id): ?Planning
    {
        // CORRECTION : Vraies jointures
        $sql = "SELECT p.*,
                       a.nom AS nom_activite,
                       a.description AS description_activite,
                       CONCAT(u.prenom, ' ', u.nom) AS nom_entraineur,
                       u.id AS entraineur_id
                FROM plannings p
                LEFT JOIN activites_sportives a ON p.activite_id = a.id
                LEFT JOIN utilisateurs u ON a.entraineur_id = u.id
                WHERE p.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $r = $stmt->fetch();
        return $r ? new Planning($r) : null;
    }

    /**
     * NOUVELLE MÉTHODE : Vérifier qu'une activité existe
     */
    private function activiteExists(int $activite_id): bool
    {
        $sql = "SELECT COUNT(*) FROM activites_sportives WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $activite_id]);
        return $stmt->fetchColumn() > 0;
    }

    /** @return array<int, array>  liste brute des enregistrements en conflit */
    private function findConflicts(string $jour, string $heure_debut, string $heure_fin, string $salle, ?int $excludeId = null): array
    {
        // CORRECTION : Inclure le nom de l'activité dans les conflits
        $sql = "SELECT p.*, a.nom AS nom_activite
                FROM plannings p
                LEFT JOIN activites_sportives a ON p.activite_id = a.id
                WHERE p.jour_semaine = :jour
                  AND p.salle = :salle
                  AND (p.heure_debut < :heure_fin AND p.heure_fin > :heure_debut)";
        $params = [
            ':jour'         => $jour,
            ':salle'        => $salle,
            ':heure_debut'  => $heure_debut,
            ':heure_fin'    => $heure_fin,
        ];
        if ($excludeId) {
            $sql .= " AND p.id <> :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Export CSV (réponse directe) */
    private function exportPlanningsCSV(): void
    {
        $plannings = $this->getAllPlannings();

        $filename = 'plannings_' . date('Y-m-d_H-i-s') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // BOM UTF-8
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($out, [
            'ID', 'Activité', 'Entraîneur', 'Jour', 'Heure début', 'Heure fin', 'Durée', 'Salle'
        ], ';');

        foreach ($plannings as $p) {
            fputcsv($out, [
                $p->getId(),
                $p->getNomActivite() ?: 'Activité inconnue',
                $p->getNomEntraineur() ?: 'Entraîneur inconnu',
                $p->getJourSemaine(),
                $p->getHeureDebut(),
                $p->getHeureFin(),
                $p->getDuree(),
                $p->getSalle(),
            ], ';');
        }
        fclose($out);
        exit;
    }

    /* =========================
     *     STATS & RECHERCHE
     * ========================= */
    public function getPlanningStatistics(): array
    {
        $stats = [
            'total'               => 0,
            'activites_planifiees'=> 0,
            'salles_utilisees'    => 0,
            'par_jour'            => [],
            'par_salle'           => [],
            'par_activite'        => [],
            'duree_moyenne'       => 0
        ];

        // Total
        $stats['total'] = (int)$this->db->query("SELECT COUNT(*) FROM plannings")->fetchColumn();

        // Activités
        $stats['activites_planifiees'] = (int)$this->db->query("SELECT COUNT(DISTINCT activite_id) FROM plannings")->fetchColumn();

        // Salles
        $stats['salles_utilisees'] = (int)$this->db->query("SELECT COUNT(DISTINCT salle) FROM plannings WHERE salle IS NOT NULL")->fetchColumn();

        // Par jour
        $stmt = $this->db->query("SELECT jour_semaine, COUNT(*) AS c FROM plannings GROUP BY jour_semaine");
        foreach ($stmt->fetchAll() as $r) {
            $stats['par_jour'][$r['jour_semaine']] = (int)$r['c'];
        }

        // Par salle
        $stmt = $this->db->query("SELECT salle, COUNT(*) AS c FROM plannings WHERE salle IS NOT NULL GROUP BY salle ORDER BY c DESC");
        foreach ($stmt->fetchAll() as $r) {
            $stats['par_salle'][$r['salle']] = (int)$r['c'];
        }

        // NOUVEAU : Par activité
        $stmt = $this->db->query("SELECT a.nom, COUNT(p.id) AS c 
                                 FROM plannings p 
                                 LEFT JOIN activites_sportives a ON p.activite_id = a.id 
                                 GROUP BY a.nom 
                                 ORDER BY c DESC");
        foreach ($stmt->fetchAll() as $r) {
            $stats['par_activite'][$r['nom'] ?: 'Activité inconnue'] = (int)$r['c'];
        }

        // Durée moyenne (minutes)
        $stmt = $this->db->query("SELECT AVG(TIME_TO_SEC(TIMEDIFF(heure_fin, heure_debut))/60) FROM plannings");
        $avg  = $stmt->fetchColumn();
        $stats['duree_moyenne'] = $avg !== null ? (int)round($avg) : 0;

        return $stats;
    }

    /** @return Planning[] */
    public function searchPlannings(?string $terme, ?string $jour = null, ?string $salle = null): array
    {
        // CORRECTION : Inclure la recherche dans les noms d'activités
        $sql = "SELECT p.*,
                       a.nom AS nom_activite,
                       a.description AS description_activite,
                       CONCAT(u.prenom, ' ', u.nom) AS nom_entraineur,
                       u.id AS entraineur_id
                FROM plannings p
                LEFT JOIN activites_sportives a ON p.activite_id = a.id
                LEFT JOIN utilisateurs u ON a.entraineur_id = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($terme)) {
            $sql .= " AND (p.salle LIKE :terme OR a.nom LIKE :terme OR CONCAT(u.prenom, ' ', u.nom) LIKE :terme)";
            $params[':terme'] = "%{$terme}%";
        }
        if (!empty($jour)) {
            $sql .= " AND p.jour_semaine = :jour";
            $params[':jour'] = $jour;
        }
        if (!empty($salle)) {
            $sql .= " AND p.salle LIKE :salle";
            $params[':salle'] = "%{$salle}%";
        }

        $sql .= " ORDER BY
                    CASE p.jour_semaine
                        WHEN 'Lundi' THEN 1
                        WHEN 'Mardi' THEN 2
                        WHEN 'Mercredi' THEN 3
                        WHEN 'Jeudi' THEN 4
                        WHEN 'Vendredi' THEN 5
                        WHEN 'Samedi' THEN 6
                        WHEN 'Dimanche' THEN 7
                    END,
                    p.heure_debut";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $out = [];
        foreach ($rows as $r) {
            $out[] = new Planning($r);
        }
        return $out;
    }

    public function getPlanningsBySalle(string $salle): array
    {
        // CORRECTION : Vraies jointures
        $stmt = $this->db->prepare(
            "SELECT p.*, 
                    a.nom AS nom_activite,
                    a.description AS description_activite,
                    CONCAT(u.prenom, ' ', u.nom) AS nom_entraineur,
                    u.id AS entraineur_id
             FROM plannings p
             LEFT JOIN activites_sportives a ON p.activite_id = a.id
             LEFT JOIN utilisateurs u ON a.entraineur_id = u.id
             WHERE p.salle = :salle
             ORDER BY
                CASE p.jour_semaine
                    WHEN 'Lundi' THEN 1
                    WHEN 'Mardi' THEN 2
                    WHEN 'Mercredi' THEN 3
                    WHEN 'Jeudi' THEN 4
                    WHEN 'Vendredi' THEN 5
                    WHEN 'Samedi' THEN 6
                    WHEN 'Dimanche' THEN 7
                END,
                p.heure_debut"
        );
        $stmt->execute([':salle' => $salle]);
        $rows = $stmt->fetchAll();

        $out = [];
        foreach ($rows as $r) {
            $out[] = new Planning($r);
        }
        return $out;
    }

    /**
     * NOUVELLE MÉTHODE : Récupérer les plannings par activité
     */
    public function getPlanningsByActivite(int $activite_id): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, 
                    a.nom AS nom_activite,
                    a.description AS description_activite,
                    CONCAT(u.prenom, ' ', u.nom) AS nom_entraineur,
                    u.id AS entraineur_id
             FROM plannings p
             LEFT JOIN activites_sportives a ON p.activite_id = a.id
             LEFT JOIN utilisateurs u ON a.entraineur_id = u.id
             WHERE p.activite_id = :activite_id
             ORDER BY
                CASE p.jour_semaine
                    WHEN 'Lundi' THEN 1
                    WHEN 'Mardi' THEN 2
                    WHEN 'Mercredi' THEN 3
                    WHEN 'Jeudi' THEN 4
                    WHEN 'Vendredi' THEN 5
                    WHEN 'Samedi' THEN 6
                    WHEN 'Dimanche' THEN 7
                END,
                p.heure_debut"
        );
        $stmt->execute([':activite_id' => $activite_id]);
        $rows = $stmt->fetchAll();

        $out = [];
        foreach ($rows as $r) {
            $out[] = new Planning($r);
        }
        return $out;
    }

    /**
     * NOUVELLE MÉTHODE : Récupérer les plannings par entraîneur
     */
    public function getPlanningsByEntraineur(int $entraineur_id): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, 
                    a.nom AS nom_activite,
                    a.description AS description_activite,
                    CONCAT(u.prenom, ' ', u.nom) AS nom_entraineur,
                    u.id AS entraineur_id
             FROM plannings p
             JOIN activites_sportives a ON p.activite_id = a.id
             JOIN utilisateurs u ON a.entraineur_id = u.id
             WHERE u.id = :entraineur_id AND u.role = 'entraineur'
             ORDER BY
                CASE p.jour_semaine
                    WHEN 'Lundi' THEN 1
                    WHEN 'Mardi' THEN 2
                    WHEN 'Mercredi' THEN 3
                    WHEN 'Jeudi' THEN 4
                    WHEN 'Vendredi' THEN 5
                    WHEN 'Samedi' THEN 6
                    WHEN 'Dimanche' THEN 7
                END,
                p.heure_debut"
        );
        $stmt->execute([':entraineur_id' => $entraineur_id]);
        $rows = $stmt->fetchAll();

        $out = [];
        foreach ($rows as $r) {
            $out[] = new Planning($r);
        }
        return $out;
    }

    public function getAllSalles(): array
    {
        $stmt = $this->db->query("SELECT DISTINCT salle FROM plannings WHERE salle IS NOT NULL AND salle <> '' ORDER BY salle");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function deletePlanningsByActivite(int $activite_id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM plannings WHERE activite_id = :id");
        return $stmt->execute([':id' => $activite_id]);
    }

    /**
     * NOUVELLE MÉTHODE : Obtenir le planning hebdomadaire formaté
     */
    public function getPlanningHebdomadaire(): array
    {
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $planning_semaine = [];

        foreach ($jours as $jour) {
            $stmt = $this->db->prepare(
                "SELECT p.*, 
                        a.nom AS nom_activite,
                        CONCAT(u.prenom, ' ', u.nom) AS nom_entraineur,
                        p.salle
                 FROM plannings p
                 LEFT JOIN activites_sportives a ON p.activite_id = a.id
                 LEFT JOIN utilisateurs u ON a.entraineur_id = u.id
                 WHERE p.jour_semaine = :jour
                 ORDER BY p.heure_debut"
            );
            $stmt->execute([':jour' => $jour]);
            $planning_semaine[$jour] = $stmt->fetchAll();
        }

        return $planning_semaine;
    }
}