<?php
require_once __DIR__ . '/../models/ActiviteSportive.php';
require_once __DIR__ . '/../config.php';

class ActiviteSportiveController
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

        // Fallback
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
                case 'export':            $this->exportActivitesCSV(); break;
                case 'entraineurs':       $this->ajaxGetEntraineurs(); break;
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
        $activite = new ActiviteSportive($_POST);
        $errors = $activite->validate();
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => 'Données invalides', 'errors' => $errors]);
            return;
        }

        // Vérifier que l'entraîneur existe
        if (!$this->entraineurExists($activite->getEntraineurId())) {
            echo json_encode(['success' => false, 'message' => 'Entraîneur inexistant']);
            return;
        }

        // Vérifier que le nom n'existe pas déjà
        if ($this->nomExists($activite->getNom())) {
            echo json_encode(['success' => false, 'message' => 'Une activité avec ce nom existe déjà']);
            return;
        }

        $sql = "INSERT INTO activites_sportives (nom, description, entraineur_id)
                VALUES (:nom, :description, :entraineur_id)";
        $ok = $this->db->prepare($sql)->execute([
            ':nom' => $activite->getNom(),
            ':description' => $activite->getDescription(),
            ':entraineur_id' => $activite->getEntraineurId(),
        ]);

        echo json_encode($ok
            ? ['success' => true, 'message' => 'Activité créée avec succès']
            : ['success' => false, 'message' => 'Erreur lors de la création de l\'activité']
        );
    }

    private function ajaxGet(): void
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID non spécifié']);
            return;
        }

        $activite = $this->getActiviteById($id);
        echo json_encode($activite
            ? ['success' => true, 'data' => $activite->toArray()]
            : ['success' => false, 'message' => 'Activité non trouvée']
        );
    }

    private function ajaxGetAll(): void
    {
        $rows = $this->getAllActivites();
        $data = array_map(fn($a) => $a->toArray(), $rows);
        echo json_encode(['success' => true, 'data' => $data]);
    }

    private function ajaxUpdate(): void
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID non spécifié']);
            return;
        }

        $activite = new ActiviteSportive($_POST);
        $activite->setId($id);

        $errors = $activite->validate();
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => 'Données invalides', 'errors' => $errors]);
            return;
        }

        // Vérifier que l'entraîneur existe
        if (!$this->entraineurExists($activite->getEntraineurId())) {
            echo json_encode(['success' => false, 'message' => 'Entraîneur inexistant']);
            return;
        }

        // Vérifier que le nom n'existe pas déjà (sauf pour cette activité)
        if ($this->nomExists($activite->getNom(), $activite->getId())) {
            echo json_encode(['success' => false, 'message' => 'Une activité avec ce nom existe déjà']);
            return;
        }

        $sql = "UPDATE activites_sportives
                SET nom = :nom, description = :description, entraineur_id = :entraineur_id
                WHERE id = :id";
        $ok = $this->db->prepare($sql)->execute([
            ':id' => $activite->getId(),
            ':nom' => $activite->getNom(),
            ':description' => $activite->getDescription(),
            ':entraineur_id' => $activite->getEntraineurId(),
        ]);

        echo json_encode($ok
            ? ['success' => true, 'message' => 'Activité mise à jour avec succès']
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

        // Vérifier s'il y a des plannings liés
        $planningsCount = $this->getPlanningsCountByActivite($id);
        if ($planningsCount > 0) {
            echo json_encode([
                'success' => false, 
                'message' => "Impossible de supprimer cette activité car elle est utilisée dans {$planningsCount} planning(s)"
            ]);
            return;
        }

        $ok = $this->db->prepare("DELETE FROM activites_sportives WHERE id = :id")->execute([':id' => $id]);
        echo json_encode($ok
            ? ['success' => true, 'message' => 'Activité supprimée avec succès']
            : ['success' => false, 'message' => 'Erreur lors de la suppression']
        );
    }

    private function ajaxStats(): void
    {
        $stats = $this->getActiviteStatistics();
        echo json_encode(['success' => true, 'data' => $stats]);
    }

    private function ajaxGetEntraineurs(): void
    {
        $entraineurs = $this->getAllEntraineurs();
        echo json_encode(['success' => true, 'data' => $entraineurs]);
    }

    /* =========================
     *   MÉTHODES MÉTIER/DAO
     * ========================= */
    /** @return ActiviteSportive[] */
    public function getAllActivites(): array
    {
        $sql = "SELECT a.*, CONCAT(u.prenom, ' ', u.nom) AS nom_entraineur,
                       (SELECT COUNT(*) FROM plannings WHERE activite_id = a.id) AS plannings_count
                FROM activites_sportives a
                LEFT JOIN utilisateurs u ON a.entraineur_id = u.id
                ORDER BY a.nom";
        
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();

        $out = [];
        foreach ($rows as $r) {
            $out[] = new ActiviteSportive($r);
        }
        return $out;
    }

    public function getActiviteById(int $id): ?ActiviteSportive
    {
        $sql = "SELECT a.*, CONCAT(u.prenom, ' ', u.nom) AS nom_entraineur
                FROM activites_sportives a
                LEFT JOIN utilisateurs u ON a.entraineur_id = u.id
                WHERE a.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $r = $stmt->fetch();
        return $r ? new ActiviteSportive($r) : null;
    }

    private function entraineurExists(int $entraineur_id): bool
    {
        $sql = "SELECT COUNT(*) FROM utilisateurs WHERE id = :id AND role = 'entraineur' AND is_actif = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $entraineur_id]);
        return $stmt->fetchColumn() > 0;
    }

    private function nomExists(string $nom, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM activites_sportives WHERE nom = :nom";
        $params = [':nom' => $nom];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    public function getPlanningsCountByActivite(int $activite_id): int
    {
        $sql = "SELECT COUNT(*) FROM plannings WHERE activite_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $activite_id]);
        return (int)$stmt->fetchColumn();
    }

    public function getAllEntraineurs(): array
    {
        $sql = "SELECT id, prenom, nom, email 
                FROM utilisateurs 
                WHERE role = 'entraineur' AND is_actif = 1 
                ORDER BY nom, prenom";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getActiviteStatistics(): array
    {
        $stats = [
            'total' => 0,
            'par_entraineur' => [],
            'avec_plannings' => 0,
            'sans_plannings' => 0
        ];

        // Total
        $stats['total'] = (int)$this->db->query("SELECT COUNT(*) FROM activites_sportives")->fetchColumn();

        // Par entraîneur
        $stmt = $this->db->query("
            SELECT CONCAT(u.prenom, ' ', u.nom) as entraineur, COUNT(a.id) as count
            FROM activites_sportives a
            LEFT JOIN utilisateurs u ON a.entraineur_id = u.id
            GROUP BY u.id, u.prenom, u.nom
            ORDER BY count DESC
        ");
        foreach ($stmt->fetchAll() as $r) {
            $stats['par_entraineur'][$r['entraineur']] = (int)$r['count'];
        }

        // Avec/sans plannings
        $stmt = $this->db->query("
            SELECT 
                COUNT(DISTINCT a.id) as avec_plannings,
                (SELECT COUNT(*) FROM activites_sportives) - COUNT(DISTINCT a.id) as sans_plannings
            FROM activites_sportives a
            INNER JOIN plannings p ON a.id = p.activite_id
        ");
        $r = $stmt->fetch();
        $stats['avec_plannings'] = (int)$r['avec_plannings'];
        $stats['sans_plannings'] = (int)$r['sans_plannings'];

        return $stats;
    }

    /** Export CSV */
    private function exportActivitesCSV(): void
    {
        $activites = $this->getAllActivites();

        $filename = 'activites_sportives_' . date('Y-m-d_H-i-s') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

        fputcsv($out, ['ID', 'Nom', 'Description', 'Entraîneur', 'Plannings'], ';');

        foreach ($activites as $a) {
            $planningsCount = $this->getPlanningsCountByActivite($a->getId());
            fputcsv($out, [
                $a->getId(),
                $a->getNom(),
                $a->getDescription(),
                $a->getEntraineurId(), // On pourrait récupérer le nom de l'entraîneur
                $planningsCount
            ], ';');
        }
        fclose($out);
        exit;
    }

    /** @return ActiviteSportive[] */
    public function searchActivites(?string $terme, ?int $entraineur_id = null): array
    {
        $sql = "SELECT a.*, CONCAT(u.prenom, ' ', u.nom) AS nom_entraineur
                FROM activites_sportives a
                LEFT JOIN utilisateurs u ON a.entraineur_id = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($terme)) {
            $sql .= " AND (a.nom LIKE :terme OR a.description LIKE :terme)";
            $params[':terme'] = "%{$terme}%";
        }
        if ($entraineur_id) {
            $sql .= " AND a.entraineur_id = :entraineur_id";
            $params[':entraineur_id'] = $entraineur_id;
        }

        $sql .= " ORDER BY a.nom";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $out = [];
        foreach ($rows as $r) {
            $out[] = new ActiviteSportive($r);
        }
        return $out;
    }

    public function getActivitesByEntraineur(int $entraineur_id): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, CONCAT(u.prenom, ' ', u.nom) AS nom_entraineur
             FROM activites_sportives a
             LEFT JOIN utilisateurs u ON a.entraineur_id = u.id
             WHERE a.entraineur_id = :entraineur_id
             ORDER BY a.nom"
        );
        $stmt->execute([':entraineur_id' => $entraineur_id]);
        $rows = $stmt->fetchAll();

        $out = [];
        foreach ($rows as $r) {
            $out[] = new ActiviteSportive($r);
        }
        return $out;
    }
}
