<?php 
require_once '../../config.php';

session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: ../front/login.php');
    exit;
}

// Vérifier si l'utilisateur a les droits d'accès (admin ou entraineur)
$user = $_SESSION['user'];
$allowedRoles = ['admin', 'entraineur'];

if (!in_array($user['role'], $allowedRoles)) {
    header('Location: ../front/login.php?error=access_denied');
    exit;
}

$initiales = strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1));

// Initialiser les contrôleurs
require_once __DIR__ . '/../../controller/AdherentController.php';
$adherentController = new AdherentController();
require_once __DIR__ . '/../../controller/EntraineurController.php';
$entraineurController = new EntraineurController();
require_once __DIR__ . '/../../controller/PlanningController.php';
$planningController = new PlanningController();
require_once __DIR__ . '/../../controller/ActiviteSportiveController.php';
$activiteController = new ActiviteSportiveController();

// Gérer les requêtes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    // Vérifier si c'est pour les activités sportives
    if (isset($_POST['entity']) && $_POST['entity'] === 'activite') {
        $activiteController->handleAjaxRequest();
    }
    // Vérifier si c'est pour les plannings
    elseif (isset($_POST['entity']) && $_POST['entity'] === 'planning') {
        $planningController->handleAjaxRequest();
    } 
    // Vérifier si c'est pour les entraîneurs
    elseif (isset($_POST['entity']) && $_POST['entity'] === 'entraineur') {
        $entraineurController->handleAjaxRequest();
    } 
    else {
        // Par défaut, traiter comme adhérent
        $adherentController->handleAjaxRequest();
    }
    exit;
}


// Récupérer les données pour les entraîneurs
$entraineurs = $entraineurController->getAllEntraineurs();
$statsEntraineurs = $entraineurController->getEntraineurStats();

// Récupérer les données pour les activités sportives
$activites = $activiteController->getAllActivites();
$statsActivites = $activiteController->getActiviteStatistics();

// Récupérer les données pour les plannings
$plannings = $planningController->getAllPlannings();
$statsPlanning = $planningController->getPlanningStatistics();

// Calculer le jour le plus chargé pour les statistiques
$jourPlusCharge = '-';
if (!empty($statsPlanning['par_jour'])) {
    $jourPlusCharge = array_keys($statsPlanning['par_jour'], max($statsPlanning['par_jour']))[0];
}



// Récupérer les données pour les adhérents
$adherents = $adherentController->getAllAdherents();
$stats = $adherentController->getAdherentStats();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard <?php echo ucfirst($user['role']); ?> - PulsePlay</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
            color: white;
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: rgba(15, 15, 35, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .logout-btn {
            background: transparent;
            border: 2px solid #00d4aa;
            color: #00d4aa;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .logout-btn:hover {
            background: #00d4aa;
            color: white;
        }

        /* Dashboard Container */
        .dashboard-container {
            display: flex;
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
            gap: 2rem;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(145deg, rgba(26, 26, 46, 0.8), rgba(22, 33, 62, 0.8));
            border-radius: 15px;
            padding: 2rem;
            height: fit-content;
            border: 1px solid rgba(0, 212, 170, 0.2);
        }

        .sidebar h3 {
            color: #00d4aa;
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #b8b8b8;
            border-left: 4px solid transparent;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(0, 212, 170, 0.1);
            color: #00d4aa;
            border-left-color: #00d4aa;
        }

        .nav-item.active {
            background: rgba(0, 212, 170, 0.15);
            font-weight: bold;
        }

        .nav-icon {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            background: linear-gradient(145deg, rgba(26, 26, 46, 0.6), rgba(22, 33, 62, 0.6));
            border-radius: 15px;
            padding: 2rem;
            border: 1px solid rgba(0, 212, 170, 0.2);
            min-height: 70vh;
            position: relative;
        }

        /* Section */
        .section {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
            width: 100%;
            position: relative;
            z-index: 1;
        }

        .section.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 2rem;
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .add-btn {
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 212, 170, 0.3);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: linear-gradient(145deg, rgba(26, 26, 46, 0.8), rgba(22, 33, 62, 0.8));
            padding: 2rem;
            border-radius: 15px;
            border: 1px solid rgba(0, 212, 170, 0.2);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(0, 212, 170, 0.5);
            box-shadow: 0 10px 20px rgba(0, 212, 170, 0.1);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            color: #b8b8b8;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        /* Table */
        .table-container {
            background: rgba(15, 15, 35, 0.5);
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid rgba(0, 212, 170, 0.2);
            overflow-x: auto;
            width: 100%;
            position: relative;
            z-index: 2;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            position: relative;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(0, 212, 170, 0.1);
            vertical-align: middle;
            position: relative;
        }

        th {
            background: linear-gradient(135deg, rgba(0, 212, 170, 0.2), rgba(0, 180, 216, 0.2));
            color: #00d4aa;
            font-weight: bold;
            user-select: none;
            cursor: pointer;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        tbody {
            position: relative;
        }

        tbody tr {
            position: relative;
        }

        th:hover {
            background: rgba(0, 212, 170, 0.15);
            color: white;
        }

        tr:hover {
            background: rgba(0, 212, 170, 0.05);
            transform: scale(1.01);
        }

        /* Status Badges */
        .statut-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .statut-admin, .statut-active {
            background: rgba(0, 212, 170, 0.2);
            color: #00d4aa;
        }

        .statut-user, .statut-inactive {
            background: rgba(255, 107, 107, 0.2);
            color: #ff6b6b;
        }

        .statut-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.3rem;
            flex-wrap: wrap;
        }

        .btn-edit, .btn-delete, .btn-view {
            padding: 0.4rem 0.6rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: bold;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
.btn-edit {
            background: rgba(0, 180, 216, 0.2);
            color: #00b4d8;
            border: 1px solid rgba(0, 180, 216, 0.3);
        }

        .btn-delete {
            background: rgba(255, 107, 107, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(255, 107, 107, 0.3);
        }

        .btn-view {
            background: rgba(0, 212, 170, 0.2);
            color: #00d4aa;
            border: 1px solid rgba(0, 212, 170, 0.3);
        }

        .btn-edit:hover, .btn-delete:hover, .btn-view:hover {
            transform: translateY(-2px);
            opacity: 0.8;
        }

        /* Search and Filter */
        .search-filter-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            align-items: center;
        }

        .search-container {
            flex: 1;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 0.8rem;
            padding-left: 2.5rem;
            border: 1px solid rgba(0, 212, 170, 0.3);
            border-radius: 8px;
            background: rgba(15, 15, 35, 0.5);
            color: white;
            font-size: 1rem;
        }

        .search-icon {
            position: absolute;
            left: 0.8rem;
            top: 50%;
            transform: translateY(-50%);
            color: #00d4aa;
            font-size: 1.2rem;
        }

        .filter-select {
            padding: 0.8rem;
            border: 1px solid rgba(0, 212, 170, 0.3);
            border-radius: 8px;
            background: rgba(15, 15, 35, 0.5);
            color: white;
            font-size: 1rem;
        }

        .result-count {
            color: #b8b8b8;
            font-size: 0.9rem;
            white-space: nowrap;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.show {
            opacity: 1;
        }

        .modal-content {
            background: linear-gradient(145deg, rgba(26, 26, 46, 0.95), rgba(22, 33, 62, 0.95));
            border-radius: 15px;
            border: 1px solid rgba(0, 212, 170, 0.3);
            min-width: 500px;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            backdrop-filter: blur(10px);
            transform: scale(0.8);
            transition: transform 0.3s ease;
        }

        .modal-overlay.show .modal-content {
            transform: scale(1);
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(0, 212, 170, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            color: #00d4aa;
            margin: 0;
            font-size: 1.5rem;
        }

        .modal-close {
            background: none;
            border: none;
            color: #ff6b6b;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            transition: background 0.3s;
        }

        .modal-close:hover {
            background: rgba(255, 107, 107, 0.1);
        }

        .modal-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            color: #00d4aa;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid rgba(0, 212, 170, 0.3);
            border-radius: 8px;
            background: rgba(15, 15, 35, 0.5);
            color: white;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #00d4aa;
            box-shadow: 0 0 0 2px rgba(0, 212, 170, 0.2);
        }

        .form-input.error {
            border-color: #ff6b6b !important;
            box-shadow: 0 0 0 2px rgba(255, 107, 107, 0.2) !important;
        }

        .error-message {
            color: #ff6b6b;
            font-size: 0.8rem;
            margin-top: 0.3rem;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-cancel, .btn-save {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-cancel {
            background: rgba(255, 107, 107, 0.2);
            color: #3e2525ff;
            border: 1px solid rgba(255, 107, 107, 0.3);
        }

        .btn-save {
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            color: white;
        }

        .btn-cancel:hover, .btn-save:hover {
            transform: translateY(-2px);
        }

        .btn-save:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Loading spinner */
        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid #bb0f8aff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 0.5rem;
        }

        .loading-spinner.show {
            display: inline-block;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            z-index: 10001;
            opacity: 0;
            transform: translateX(100px);
            transition: all 0.3s ease;
        }

        .notification.show {
            opacity: 1;
            transform: translateX(0);
        }

        .notification.success {
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
        }

        .notification.error {
            background: linear-gradient(45deg, #ff6b6b, #ff8e8e);
        }

        .notification.info {
            background: rgba(0, 180, 216, 0.9);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .dashboard-container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
            }
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            .modal-content {
                min-width: auto;
            }
        }

        @media (max-width: 768px) {
            .header-container {
                padding: 0 1rem;
            }
            .dashboard-container {
                padding: 0 1rem;
            }
            .search-filter-container {
                flex-direction: column;
                align-items: stretch;
            }
            .action-buttons {
                flex-direction: column;
                gap: 0.2rem;
            }
            .modal-content {
                margin: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <div class="logo">PulsePlay <?php echo ucfirst($user['role']); ?></div>
            <div class="admin-info">
                <div class="admin-avatar"><?php echo $initiales; ?></div>
                <span><?php echo $user['prenom'] . ' ' . $user['nom']; ?> (<?php echo ucfirst($user['role']); ?>)</span>
                <a href="/PulsePlay/logout.php" class="logout-btn">Déconnexion</a>
            </div>
        </div>
    </header>
     <!-- Dashboard Container -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h3>Navigation</h3>
            
            <div class="nav-item" onclick="showSection('dashboard-home')" data-section="dashboard-home">
                <span class="nav-icon">📊</span>
                <span>Tableau de bord</span>
            </div>
            
            <div class="nav-item" onclick="showSection('entraineurs')" data-section="entraineurs">
                <span class="nav-icon">🏃‍♂️</span>
                <span>Entraîneurs</span>
            </div>
            
            <div class="nav-item active" onclick="showSection('adherents')" data-section="adherents">
                <span class="nav-icon">👥</span>
                <span>Adhérents</span>
            </div>
            
            <div class="nav-item" onclick="showSection('planning')" data-section="planning">
                <span class="nav-icon">📅</span>
                <span>Planning</span>
            </div>
            
            <div class="nav-item" onclick="showSection('activites')" data-section="activites">
                <span class="nav-icon">🏃‍♀️</span>
                <span>Activités Sportives</span>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
          <div>
            <!-- Section Adhérents -->
            <div id="adherents" class="section active">
                <div class="section-header">
                    <h2 class="section-title">Gestion des Adhérents</h2>
                    <div style="display: flex; gap: 1rem;">
                        <button onclick="createAdherent()" class="add-btn">
                            ➕ Ajouter un adhérent
                        </button>
                        <button onclick="exportAdherents()" class="add-btn" style="background: linear-gradient(45deg, #00b4d8, #0077b6);">
                            📊 Exporter CSV
                        </button>
                    </div>
        </div>

                <!-- Statistiques -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">👥</div>
                        </div>
                        <div class="stat-number" id="stat-total"><?php echo $stats['total']; ?></div>
                        <div class="stat-label">Total Adhérents</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">✅</div>
                        </div>
                        <div class="stat-number" id="stat-actifs"><?php echo $stats['actifs']; ?></div>
                        <div class="stat-label">Comptes Actifs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">❌</div>
                        </div>
                        <div class="stat-number" id="stat-inactifs"><?php echo $stats['inactifs']; ?></div>
                        <div class="stat-label">Comptes Inactifs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">📈</div>
                        </div>
                        <div class="stat-number"><?php echo round($stats['total'] > 0 ? ($stats['actifs'] / $stats['total'] * 100) : 0, 1); ?>%</div>
                        <div class="stat-label">Taux d'activation</div>
                    </div>
                </div>

                <!-- Barre de recherche et filtres -->
                <div class="search-filter-container">
                    <div class="search-container">
                        <input type="text" id="searchInput" class="search-input" placeholder="Rechercher par nom, prénom ou email..." oninput="filterAdherents()">
                        <span class="search-icon">🔍</span>
                    </div>
                    <select id="statutFilter" class="filter-select" onchange="filterAdherents()">
                        <option value="">📋 Tous les statuts</option>
                        <option value="actif">✅ Comptes actifs</option>
                        <option value="inactif">❌ Comptes inactifs</option>
                    </select>
                    <div class="result-count">
                        <span id="resultCount"><?php echo count($adherents); ?></span> résultat(s)
                    </div>
                </div>

                <!-- Tableau des adhérents -->
                <div class="table-container">
                    <table id="adherentsTable">
                        <thead>
                            <tr>
                                <th onclick="sortTable(0)">👤 Nom <span id="sort-0">↕️</span></th>
                                <th onclick="sortTable(1)">👤 Prénom <span id="sort-1">↕️</span></th>
                                <th onclick="sortTable(2)">📧 Email <span id="sort-2">↕️</span></th>
                                <th onclick="sortTable(3)">📅 Date inscription <span id="sort-3">↕️</span></th>
                                <th onclick="sortTable(4)">🔄 Statut <span id="sort-4">↕️</span></th>
                                <th style="width: 200px;">⚙️ Actions</th>
                            </tr>
                        </thead>
                        <tbody id="adherentsTableBody">
                            <?php if (empty($adherents)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 3rem; color: #b8b8b8;">
                                    <div style="font-size: 3rem; margin-bottom: 1rem;">😔</div>
                                    <div style="font-size: 1.2rem; margin-bottom: 0.5rem;">Aucun adhérent trouvé</div>
                                    <div style="font-size: 0.9rem;">Commencez par ajouter votre premier adhérent !</div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($adherents as $adherent): ?>
                            <tr data-id="<?php echo $adherent->getId(); ?>">
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(45deg, #00d4aa, #00b4d8); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem;">
                                            <?php echo $adherent->getInitiales(); ?>
                                        </div>
                                        <span><?php echo htmlspecialchars($adherent->getNom()); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($adherent->getPrenom()); ?></td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($adherent->getEmail()); ?>" style="color: #00d4aa; text-decoration: none;">
                                        <?php echo htmlspecialchars($adherent->getEmail()); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php 
                                    if (!empty($adherent->getDateInscription())) {
                                        echo date('d/m/Y', strtotime($adherent->getDateInscription()));
                                    } else {
                                        echo '<span style="color: #b8b8b8;">-</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="statut-badge <?php echo $adherent->getIsActif() ? 'statut-admin' : 'statut-user'; ?>">
                                        <?php echo $adherent->getIsActif() ? '✅ Actif' : '❌ Inactif'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick="viewAdherent(<?php echo $adherent->getId(); ?>)" class="btn-view" title="Voir les détails">
                                            👁️ Voir
                                        </button>
                                        <button onclick="editAdherent(<?php echo $adherent->getId(); ?>)" class="btn-edit" title="Modifier">
                                            ✏️ Modifier
                                        </button>
                                        <button onclick="deleteAdherent(<?php echo $adherent->getId(); ?>)" class="btn-delete" title="Supprimer">
                                            🗑️ Supprimer
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
   </div>             
     <div>       
            <!-- Section Entraîneurs -->
            <div id="entraineurs" class="section">
                <div class="section-header">
                    <h2 class="section-title">Gestion des Entraîneurs</h2>
                    <div style="display: flex; gap: 1rem;">
                        <button onclick="createEntraineur()" class="add-btn">
                            ➕ Ajouter un entraîneur
                        </button>
                        <button onclick="exportEntraineurs()" class="add-btn" style="background: linear-gradient(45deg, #00b4d8, #0077b6);">
                            📊 Exporter CSV
                        </button>
                    </div>
                </div>

                <!-- Statistiques Entraîneurs -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">👨‍🏫</div>
                        </div>
                        <div class="stat-number" id="stat-total-entraineurs"><?php echo $statsEntraineurs['total']; ?></div>
                        <div class="stat-label">Total Entraîneurs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">✅</div>
                        </div>
                        <div class="stat-number" id="stat-actifs-entraineurs"><?php echo $statsEntraineurs['actifs']; ?></div>
                        <div class="stat-label">Comptes Actifs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">❌</div>
                        </div>
                        <div class="stat-number" id="stat-inactifs-entraineurs"><?php echo $statsEntraineurs['inactifs']; ?></div>
                        <div class="stat-label">Comptes Inactifs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">📈</div>
                        </div>
                        <div class="stat-number"><?php echo round($statsEntraineurs['total'] > 0 ? ($statsEntraineurs['actifs'] / $statsEntraineurs['total'] * 100) : 0, 1); ?>%</div>
                        <div class="stat-label">Taux d'activation</div>
                    </div>
                </div>

                <!-- Barre de recherche et filtres -->
                <div class="search-filter-container">
                    <div class="search-container">
                        <input type="text" id="searchInputEntraineurs" class="search-input" placeholder="Rechercher par nom, prénom ou email..." oninput="filterEntraineurs()">
                        <span class="search-icon">🔍</span>
                    </div>
                    <select id="statutFilterEntraineurs" class="filter-select" onchange="filterEntraineurs()">
                        <option value="">📋 Tous les statuts</option>
                        <option value="actif">✅ Comptes actifs</option>
                        <option value="inactif">❌ Comptes inactifs</option>
                    </select>
                    <div class="result-count">
                        <span id="resultCountEntraineurs"><?php echo count($entraineurs); ?></span> résultat(s)
                    </div>
                </div>

                <!-- Tableau des entraîneurs -->
                <div class="table-container">
                    <table id="entraineursTable">
                        <thead>
                            <tr>
                                <th onclick="sortTableEntraineurs(0)">👤 Nom <span id="sort-entraineurs-0">↕️</span></th>
                                <th onclick="sortTableEntraineurs(1)">👤 Prénom <span id="sort-entraineurs-1">↕️</span></th>
                                <th onclick="sortTableEntraineurs(2)">📧 Email <span id="sort-entraineurs-2">↕️</span></th>
                                <th onclick="sortTableEntraineurs(3)">📅 Date inscription <span id="sort-entraineurs-3">↕️</span></th>
                                <th onclick="sortTableEntraineurs(4)">🔄 Statut <span id="sort-entraineurs-4">↕️</span></th>
                                <th style="width: 200px;">⚙️ Actions</th>
                            </tr>
                        </thead>
                        <tbody id="entraineursTableBody">
                            <?php if (empty($entraineurs)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 3rem; color: #b8b8b8;">
                                    <div style="font-size: 3rem; margin-bottom: 1rem;">😔</div>
                                    <div style="font-size: 1.2rem; margin-bottom: 0.5rem;">Aucun entraîneur trouvé</div>
                                    <div style="font-size: 0.9rem;">Commencez par ajouter votre premier entraîneur !</div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($entraineurs as $entraineur): ?>
                            <tr data-id="<?php echo $entraineur->getId(); ?>">
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(45deg, #00d4aa, #00b4d8); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem;">
                                            <?php echo $entraineur->getInitiales(); ?>
                                        </div>
                                        <span><?php echo htmlspecialchars($entraineur->getNom()); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($entraineur->getPrenom()); ?></td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($entraineur->getEmail()); ?>" style="color: #00d4aa; text-decoration: none;">
                                        <?php echo htmlspecialchars($entraineur->getEmail()); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php 
                                    if (!empty($entraineur->getDateInscription())) {
                                        echo date('d/m/Y', strtotime($entraineur->getDateInscription()));
                                    } else {
                                        echo '<span style="color: #b8b8b8;">-</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="statut-badge <?php echo $entraineur->getIsActif() ? 'statut-admin' : 'statut-user'; ?>">
                                        <?php echo $entraineur->getIsActif() ? '✅ Actif' : '❌ Inactif'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick="viewEntraineur(<?php echo $entraineur->getId(); ?>)" class="btn-view" title="Voir les détails">
                                            👁️ Voir
                                        </button>
                                        <button onclick="editEntraineur(<?php echo $entraineur->getId(); ?>)" class="btn-edit" title="Modifier">
                                            ✏️ Modifier
                                        </button>
                                        <button onclick="deleteEntraineur(<?php echo $entraineur->getId(); ?>)" class="btn-delete" title="Supprimer">
                                            🗑️ Supprimer
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


<div>
   <!-- Section Planning -->
<div id="planning" class="section">
    <div class="section-header">
        <h2 class="section-title">Gestion des Plannings</h2>
        <div style="display: flex; gap: 1rem;">
            <button onclick="createPlanning()" class="add-btn">
                ➕ Ajouter un planning
            </button>
            <button onclick="exportPlannings()" class="add-btn" style="background: linear-gradient(45deg, #00b4d8, #0077b6);">
                📊 Exporter CSV
            </button>
        </div>
    </div>

    <!-- Statistiques Planning -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon">📅</div>
            </div>
            <div class="stat-number" id="stat-total-plannings"><?php echo $statsPlanning['total'] ?? 0; ?></div>
            <div class="stat-label">Total Plannings</div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon">🏢</div>
            </div>
            <div class="stat-number" id="stat-salles-utilisees"><?php echo count($statsPlanning['par_salle'] ?? []); ?></div>
            <div class="stat-label">Salles Utilisées</div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon">📊</div>
            </div>
            <div class="stat-number" id="stat-jour-plus-charge"><?php echo $jourPlusCharge; ?></div>
            <div class="stat-label">Jour le Plus Chargé</div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon">⏱️</div>
            </div>
            <div class="stat-number"><?php echo $statsPlanning['duree_moyenne'] ?? 0; ?> min</div>
            <div class="stat-label">Durée Moyenne</div>
        </div>
    </div>

    <!-- Tableau des plannings -->
    <div class="table-container">
        <table id="planningsTable">
            <thead>
                <tr>
                    <th onclick="sortTablePlannings(0)">🏃‍♀️ Activité <span id="sort-plannings-0">↕️</span></th>
                    <th onclick="sortTablePlannings(1)">👨‍🏫 Entraîneur <span id="sort-plannings-1">↕️</span></th>
                    <th onclick="sortTablePlannings(2)">📅 Jour <span id="sort-plannings-2">↕️</span></th>
                    <th onclick="sortTablePlannings(3)">⏰ Horaires <span id="sort-plannings-3">↕️</span></th>
                    <th onclick="sortTablePlannings(4)">🏢 Salle <span id="sort-plannings-4">↕️</span></th>
                    <th style="width: 200px;">⚙️ Actions</th>
                </tr>
            </thead>
            <tbody id="planningsTableBody">
                <?php 
                if (!empty($plannings)) {
                    foreach ($plannings as $planning) {
                        echo "<tr data-id='" . $planning->getId() . "'>";
                        echo "<td>
                                <div style='display: flex; align-items: center; gap: 0.5rem;'>
                                    <div style='width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(45deg, #00d4aa, #00b4d8); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem;'>
                                        🏃‍♀️
                                    </div>
                                    <span>" . htmlspecialchars($planning->getNomActivite() ?: 'Activité #' . $planning->getActiviteId()) . "</span>
                                </div>
                              </td>";
                        echo "<td>" . htmlspecialchars($planning->getNomEntraineur() ?: 'Non assigné') . "</td>";
                        echo "<td>
                                <span class='statut-badge statut-admin' style='font-size: 0.8rem;'>
                                    " . htmlspecialchars($planning->getJourSemaine()) . "
                                </span>
                              </td>";
                        echo "<td>
                                <div style='font-family: monospace; color: #00b4d8;'>
                                    " . htmlspecialchars($planning->getHeureDebut()) . " - " . htmlspecialchars($planning->getHeureFin()) . "
                                </div>
                              </td>";
                        echo "<td>
                                <span style='background: rgba(0, 180, 216, 0.2); color: #00b4d8; padding: 0.2rem 0.5rem; border-radius: 12px; font-size: 0.8rem;'>
                                    " . htmlspecialchars($planning->getSalle()) . "
                                </span>
                              </td>";
                        echo "<td>
                                <div class='action-buttons'>
                                    <button onclick='viewPlanning(" . $planning->getId() . ")' class='btn-view' title='Voir les détails'>
                                        👁️ Voir
                                    </button>
                                    <button onclick='editPlanning(" . $planning->getId() . ")' class='btn-edit' title='Modifier'>
                                        ✏️ Modifier
                                    </button>
                                    <button onclick='deletePlanning(" . $planning->getId() . ")' class='btn-delete' title='Supprimer'>
                                        🗑️ Supprimer
                                    </button>
                                </div>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr>
                            <td colspan='6' style='text-align: center; padding: 2rem; color: #b8b8b8;'>
                                📅 Aucun planning trouvé
                            </td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Section Activités Sportives -->
<div id="activites" class="section">
    <div class="section-header">
        <h2 class="section-title">Gestion des Activités Sportives</h2>
        <div style="display: flex; gap: 1rem;">
            <button onclick="createActivite()" class="add-btn">
                ➕ Ajouter une activité
            </button>
            <button onclick="exportActivites()" class="add-btn" style="background: linear-gradient(45deg, #00b4d8, #0077b6);">
                📊 Exporter CSV
            </button>
        </div>
    </div>

    <!-- Statistiques Activités -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon">🏃‍♀️</div>
            </div>
            <div class="stat-number" id="stat-total-activites"><?php echo $statsActivites['total'] ?? 0; ?></div>
            <div class="stat-label">Total Activités</div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon">👨‍🏫</div>
            </div>
            <div class="stat-number" id="stat-entraineurs-activites"><?php echo count($statsActivites['par_entraineur'] ?? []); ?></div>
            <div class="stat-label">Entraîneurs Actifs</div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon">📅</div>
            </div>
            <div class="stat-number" id="stat-avec-plannings"><?php echo $statsActivites['avec_plannings'] ?? 0; ?></div>
            <div class="stat-label">Avec Plannings</div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon">⏳</div>
            </div>
            <div class="stat-number" id="stat-sans-plannings"><?php echo $statsActivites['sans_plannings'] ?? 0; ?></div>
            <div class="stat-label">Sans Plannings</div>
        </div>
    </div>

    <!-- Tableau des activités -->
    <div class="table-container">
        <table id="activitesTable">
            <thead>
                <tr>
                    <th onclick="sortTableActivites(0)">🏃‍♀️ Activité <span id="sort-activites-0">↕️</span></th>
                    <th onclick="sortTableActivites(1)">📝 Description <span id="sort-activites-1">↕️</span></th>
                    <th onclick="sortTableActivites(2)">👨‍🏫 Entraîneur <span id="sort-activites-2">↕️</span></th>
                    <th onclick="sortTableActivites(3)">📅 Plannings <span id="sort-activites-3">↕️</span></th>
                    <th style="width: 200px;">⚙️ Actions</th>
                </tr>
            </thead>
            <tbody id="activitesTableBody">
                <?php 
                if (!empty($activites)) {
                    foreach ($activites as $activite) {
                        $planningsCount = $activiteController->getPlanningsCountByActivite($activite->getId());
                        echo "<tr data-id='" . $activite->getId() . "'>";
                        echo "<td>
                                <div style='display: flex; align-items: center; gap: 0.5rem;'>
                                    <div style='width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(45deg, #00d4aa, #00b4d8); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem;'>
                                        🏃‍♀️
                                    </div>
                                    <span>" . htmlspecialchars($activite->getNom()) . "</span>
                                </div>
                              </td>";
                        echo "<td>
                                <div style='max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;'>
                                    " . htmlspecialchars($activite->getDescriptionCourte()) . "
                                </div>
                              </td>";
                        echo "<td>
                                <span style='background: rgba(0, 212, 170, 0.2); color: #00d4aa; padding: 0.2rem 0.5rem; border-radius: 12px; font-size: 0.8rem;'>
                                    Entraîneur " . htmlspecialchars($activite->getEntraineurId()) . "
                                </span>
                              </td>";
                        echo "<td>
                                <span class='statut-badge statut-admin' style='font-size: 0.8rem;'>
                                    " . $planningsCount . " planning(s)
                                </span>
                              </td>";
                        echo "<td>
                                <div class='action-buttons'>
                                    <button onclick='viewActivite(" . $activite->getId() . ")' class='btn-view' title='Voir les détails'>
                                        👁️ Voir
                                    </button>
                                    <button onclick='editActivite(" . $activite->getId() . ")' class='btn-edit' title='Modifier'>
                                        ✏️ Modifier
                                    </button>
                                    <button onclick='deleteActivite(" . $activite->getId() . ")' class='btn-delete' title='Supprimer'>
                                        🗑️ Supprimer
                                    </button>
                                </div>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr>
                            <td colspan='5' style='text-align: center; padding: 2rem; color: #b8b8b8;'>
                                🏃‍♀️ Aucune activité trouvée
                            </td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</div>



<script>
    // ================== VARIABLES GLOBALES PLANNINGS ==================
let currentSortColumnPlannings = -1;
let isAscendingPlannings = true;

// ================== INITIALISATION PLANNING ==================
function initPlanningSection() {
    // Charger les plannings
    loadPlannings();
    // Charger les statistiques
    loadPlanningStats();
}

// ================== INITIALISATION ACTIVITÉS SPORTIVES ==================
function initActivitesSection() {
    // Charger les activités
    loadActivites();
    // Charger les statistiques
    loadActiviteStats();
}
// Modifier la fonction showSection existante pour inclure l'initialisation Planning
function showSection(sectionId) {
    // Masquer toutes les sections
    const sections = document.querySelectorAll('.section');
    sections.forEach(section => section.classList.remove('active'));
    
    // Retirer la classe active de tous les nav-items
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => item.classList.remove('active'));
    
    // Afficher la section sélectionnée
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
        targetSection.classList.add('active');
    }
    
    // Ajouter la classe active au nav-item correspondant
    const activeNavItem = document.querySelector(`[data-section="${sectionId}"]`);
    if (activeNavItem) {
        activeNavItem.classList.add('active');
    }
    
    // NOUVEAU : Initialiser la section Planning si c'est celle sélectionnée
    if (sectionId === 'planning') {
        initPlanningSection();
    }
    
    // NOUVEAU : Initialiser la section Activités Sportives si c'est celle sélectionnée
    if (sectionId === 'activites') {
        initActivitesSection();
    }
}

// ================== INITIALISATION SECTION PLANNING ==================
function initPlanningSection() {
    // Charger les plannings
    loadPlannings();
    // Charger les statistiques
    loadPlanningStats();
}

// ================== CHARGEMENT DES DONNÉES ==================
function loadPlannings() {
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'ajax=1&entity=planning&action=get_all'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populatePlanningsTable(data.data);
        }
    })
    .catch(error => console.error('Erreur chargement plannings:', error));
}



function loadPlanningStats() {
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'ajax=1&entity=planning&action=stats'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updatePlanningStats(data.data);
        }
    })
    .catch(error => console.error('Erreur chargement stats planning:', error));
}

function updatePlanningStats(stats) {
    document.getElementById('stat-total-plannings').textContent = stats.total || 0;
    document.getElementById('stat-salles-utilisees').textContent = stats.salles_utilisees || 0;
    
    // Calculer le jour le plus chargé
    let jourPlusCharge = '-';
    if (stats.par_jour && Object.keys(stats.par_jour).length > 0) {
        const maxCount = Math.max(...Object.values(stats.par_jour));
        jourPlusCharge = Object.keys(stats.par_jour).find(jour => stats.par_jour[jour] === maxCount) || '-';
    }
    document.getElementById('stat-jour-plus-charge').textContent = jourPlusCharge;
}

// ================== CRUD PLANNINGS ==================
function createPlanning() {
    // Charger les activités disponibles
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'ajax=1&entity=planning&action=activites'
    })
    .then(response => response.json())
    .then(data => {
        let activitesOptions = '<option value="">Sélectionner une activité</option>';
        
        if (data.success && data.data && data.data.length > 0) {
            data.data.forEach(activite => {
                activitesOptions += `<option value="${activite.id}">${activite.nom} - ${activite.entraineur}</option>`;
            });
        } else {
            activitesOptions += '<option value="" disabled>Aucune activité disponible</option>';
        }

        const modalContent = `
            <form id="createPlanningForm" novalidate>
                <input type="hidden" name="entity" value="planning">
                <div class="form-group">
                    <label class="form-label">Activité *</label>
                    <select name="activite_id" class="form-select" required>
                        ${activitesOptions}
                    </select>
                    <div class="error-message"></div>
                </div>
            <div class="form-group">
                <label class="form-label">Jour de la semaine *</label>
                <select name="jour_semaine" class="form-select" required>
                    <option value="">Sélectionner un jour</option>
                    <option value="Lundi">Lundi</option>
                    <option value="Mardi">Mardi</option>
                    <option value="Mercredi">Mercredi</option>
                    <option value="Jeudi">Jeudi</option>
                    <option value="Vendredi">Vendredi</option>
                    <option value="Samedi">Samedi</option>
                    <option value="Dimanche">Dimanche</option>
                </select>
                <div class="error-message"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Heure de début *</label>
                <input type="time" name="heure_debut" class="form-input" required>
                <div class="error-message"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Heure de fin *</label>
                <input type="time" name="heure_fin" class="form-input" required>
                <div class="error-message"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Salle *</label>
                <input type="text" name="salle" class="form-input" required placeholder="Nom de la salle">
                <div class="error-message"></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal()">Annuler</button>
                <button type="submit" class="btn-save">
                    <div class="loading-spinner"></div>
                    Créer le planning
                </button>
            </div>
        </form>
    `;

            createModal('Nouveau Planning', modalContent);
        setupFormValidation('createPlanningForm', savePlanning);
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors du chargement des activités', 'error');
    });
}

function editPlanning(id) {
    const button = document.querySelector(`[onclick="editPlanning(${id})"]`);
    showLoading(button);
    
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=1&entity=planning&action=get&id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        hideLoading(button);
        
        if (data.success && data.data) {
            const planning = data.data;

            // Charger les activités disponibles pour l'édition
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'ajax=1&entity=planning&action=activites'
            })
            .then(response => response.json())
            .then(activitesData => {
                let activitesOptions = '<option value="">Sélectionner une activité</option>';
                
                if (activitesData.success && activitesData.data && activitesData.data.length > 0) {
                    activitesData.data.forEach(activite => {
                        const selected = planning.activite_id == activite.id ? 'selected' : '';
                        activitesOptions += `<option value="${activite.id}" ${selected}>${activite.nom} - ${activite.entraineur}</option>`;
                    });
                } else {
                    activitesOptions += '<option value="" disabled>Aucune activité disponible</option>';
                }

                const modalContent = `
                    <form id="editPlanningForm" novalidate>
                        <input type="hidden" name="entity" value="planning">
                        <input type="hidden" name="id" value="${planning.id}">
                        <div class="form-group">
                            <label class="form-label">Activité *</label>
                            <select name="activite_id" class="form-select" required>
                                ${activitesOptions}
                            </select>
                            <div class="error-message"></div>
                        </div>
                    <div class="form-group">
                        <label class="form-label">Jour de la semaine *</label>
                        <select name="jour_semaine" class="form-select" required>
                            <option value="">Sélectionner un jour</option>
                            <option value="Lundi" ${planning.jour_semaine === 'Lundi' ? 'selected' : ''}>Lundi</option>
                            <option value="Mardi" ${planning.jour_semaine === 'Mardi' ? 'selected' : ''}>Mardi</option>
                            <option value="Mercredi" ${planning.jour_semaine === 'Mercredi' ? 'selected' : ''}>Mercredi</option>
                            <option value="Jeudi" ${planning.jour_semaine === 'Jeudi' ? 'selected' : ''}>Jeudi</option>
                            <option value="Vendredi" ${planning.jour_semaine === 'Vendredi' ? 'selected' : ''}>Vendredi</option>
                            <option value="Samedi" ${planning.jour_semaine === 'Samedi' ? 'selected' : ''}>Samedi</option>
                            <option value="Dimanche" ${planning.jour_semaine === 'Dimanche' ? 'selected' : ''}>Dimanche</option>
                        </select>
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Heure de début *</label>
                        <input type="time" name="heure_debut" class="form-input" value="${planning.heure_debut}" required>
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Heure de fin *</label>
                        <input type="time" name="heure_fin" class="form-input" value="${planning.heure_fin}" required>
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Salle *</label>
                        <input type="text" name="salle" class="form-input" value="${planning.salle}" required>
                        <div class="error-message"></div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeModal()">Annuler</button>
                        <button type="submit" class="btn-save">
                            <div class="loading-spinner"></div>
                            Sauvegarder
                        </button>
                    </div>
                </form>
            `;

                            createModal('Modifier le Planning', modalContent);
                setupFormValidation('editPlanningForm', updatePlanning);
            })
            .catch(error => {
                hideLoading(button);
                console.error('Erreur:', error);
                showNotification('Erreur de connexion', 'error');
            });
        } else {
            showNotification('Erreur lors de la récupération des données', 'error');
        }
    })
    .catch(error => {
        hideLoading(button);
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

function viewPlanning(id) {
    const button = document.querySelector(`[onclick="viewPlanning(${id})"]`);
    showLoading(button);
    
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=1&entity=planning&action=get&id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        hideLoading(button);
        
        if (data.success && data.data) {
            const planning = data.data;
            const modalContent = `
                <div style="padding: 1rem;">
                    <div style="text-align: center; margin-bottom: 2rem;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📅</div>
                        <h3 style="color: #00d4aa; margin: 0;">${planning.nom_activite || 'Activité #' + planning.activite_id}</h3>
                        <p style="color: #b8b8b8; margin: 0.5rem 0;">${planning.jour_semaine} - ${planning.heure_debut} à ${planning.heure_fin}</p>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div style="background: rgba(15, 15, 35, 0.5); padding: 1rem; border-radius: 8px;">
                            <strong style="color: #00d4aa;">Activité:</strong><br>
                            <span>${planning.nom_activite || 'Activité #' + planning.activite_id}</span>
                        </div>
                        <div style="background: rgba(15, 15, 35, 0.5); padding: 1rem; border-radius: 8px;">
                            <strong style="color: #00d4aa;">Entraîneur:</strong><br>
                            <span>${planning.nom_entraineur || 'Non assigné'}</span>
                        </div>
                        <div style="background: rgba(15, 15, 35, 0.5); padding: 1rem; border-radius: 8px;">
                            <strong style="color: #00d4aa;">Jour:</strong><br>
                            <span>${planning.jour_semaine}</span>
                        </div>
                        <div style="background: rgba(15, 15, 35, 0.5); padding: 1rem; border-radius: 8px;">
                            <strong style="color: #00d4aa;">Horaires:</strong><br>
                            <span>${planning.heure_debut} - ${planning.heure_fin}</span>
                        </div>
                        <div style="background: rgba(15, 15, 35, 0.5); padding: 1rem; border-radius: 8px;">
                            <strong style="color: #00d4aa;">Salle:</strong><br>
                            <span>${planning.salle}</span>
                        </div>
                    </div>
                    
                    <div style="text-align: center; margin-top: 2rem;">
                        <button type="button" class="btn-save" onclick="editPlanning(${planning.id}); closeModal();" style="margin-right: 1rem;">
                            Modifier
                        </button>
                        <button type="button" class="btn-cancel" onclick="closeModal()">
                            Fermer
                        </button>
                    </div>
                </div>
            `;

            createModal('Détails du Planning', modalContent);
        } else {
            showNotification('Planning non trouvé', 'error');
        }
    })
    .catch(error => {
        hideLoading(button);
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

function deletePlanning(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce planning ?\n\nCette action est irréversible !')) {
        const button = document.querySelector(`[onclick="deletePlanning(${id})"]`);
        showLoading(button);
        
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `ajax=1&entity=planning&action=delete&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            hideLoading(button);
            
            if (data.success) {
                showNotification(data.message || 'Planning supprimé avec succès', 'success');
                
                const row = document.querySelector(`#planningsTable tr[data-id="${id}"]`);
                if (row) {
                    row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(-20px)';
                    setTimeout(() => {
                        row.remove();
                        updateResultCountPlannings();
                        loadPlanningStats();
                    }, 300);
                }
            } else {
                showNotification(data.message || 'Erreur lors de la suppression', 'error');
            }
        })
        .catch(error => {
            hideLoading(button);
            console.error('Erreur:', error);
            showNotification('Erreur de connexion', 'error');
        });
    }
}

function savePlanning() {
    const form = document.getElementById('createPlanningForm');
    const submitBtn = form.querySelector('.btn-save');
    showLoading(submitBtn);

    const formData = new FormData(form);
    formData.append('ajax', '1');
    formData.append('action', 'create');

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading(submitBtn);
        
        if (data.success) {
            showNotification(data.message || 'Planning créé avec succès', 'success');
            closeModal();
            // Recharger les données au lieu de recharger la page
            loadPlannings();
            loadPlanningStats();
        } else {
            if (data.errors) {
                // Afficher les erreurs de validation
                Object.keys(data.errors).forEach(field => {
                    const fieldElement = form.querySelector(`[name="${field}"]`);
                    if (fieldElement) {
                        fieldElement.classList.add('error');
                        const errorMsg = fieldElement.parentNode.querySelector('.error-message');
                        if (errorMsg) {
                            errorMsg.textContent = data.errors[field];
                            errorMsg.classList.add('show');
                        }
                    }
                });
            }
            showNotification(data.message || 'Erreur lors de la création', 'error');
        }
    })
    .catch(error => {
        hideLoading(submitBtn);
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

function updatePlanning() {
    const form = document.getElementById('editPlanningForm');
    const submitBtn = form.querySelector('.btn-save');
    showLoading(submitBtn);

    const formData = new FormData(form);
    
    // Formater les heures pour la base de données
    const heureDebut = formData.get('heure_debut');
    const heureFin = formData.get('heure_fin');
    
    if (heureDebut) {
        formData.set('heure_debut', heureDebut + ':00');
    }
    if (heureFin) {
        formData.set('heure_fin', heureFin + ':00');
    }
    
    formData.append('ajax', '1');
    formData.append('action', 'update');

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading(submitBtn);
        
        if (data.success) {
            showNotification(data.message || 'Planning mis à jour avec succès', 'success');
            closeModal();
            // Recharger les données au lieu de recharger la page
            loadPlannings();
            loadPlanningStats();
        } else {
            if (data.errors) {
                // Afficher les erreurs de validation
                Object.keys(data.errors).forEach(field => {
                    const fieldElement = form.querySelector(`[name="${field}"]`);
                    if (fieldElement) {
                        fieldElement.classList.add('error');
                        const errorMsg = fieldElement.parentNode.querySelector('.error-message');
                        if (errorMsg) {
                            errorMsg.textContent = data.errors[field];
                            errorMsg.classList.add('show');
                        }
                    }
                });
            }
            showNotification(data.message || 'Erreur lors de la mise à jour', 'error');
        }
    })
    .catch(error => {
        hideLoading(submitBtn);
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

// ================== FONCTIONS UTILITAIRES PLANNINGS ==================
function populatePlanningsTable(plannings) {
    const tbody = document.getElementById('planningsTableBody');
    if (!tbody) return;

    if (plannings.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 3rem; color: #b8b8b8;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📅</div>
                    <div style="font-size: 1.2rem; margin-bottom: 0.5rem;">Aucun planning trouvé</div>
                    <div style="font-size: 0.9rem;">Commencez par créer votre premier planning !</div>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = plannings.map(planning => `
        <tr data-id="${planning.id}">
            <td>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(45deg, #00d4aa, #00b4d8); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem;">
                        🏃‍♀️
                    </div>
                    <span>${planning.nom_activite || 'Activité #' + planning.activite_id}</span>
                </div>
            </td>
            <td>${planning.nom_entraineur || 'Non assigné'}</td>
            <td>
                <span class="statut-badge statut-admin" style="font-size: 0.8rem;">
                    ${planning.jour_semaine}
                </span>
            </td>
            <td>
                <div style="font-family: monospace; color: #00b4d8;">
                    ${planning.heure_debut} - ${planning.heure_fin}
                </div>
            </td>
            <td>
                <span style="background: rgba(0, 180, 216, 0.2); color: #00b4d8; padding: 0.2rem 0.5rem; border-radius: 12px; font-size: 0.8rem;">
                    ${planning.salle}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button onclick="viewPlanning(${planning.id})" class="btn-view" title="Voir les détails">
                        👁️ Voir
                    </button>
                    <button onclick="editPlanning(${planning.id})" class="btn-edit" title="Modifier">
                        ✏️ Modifier
                    </button>
                    <button onclick="deletePlanning(${planning.id})" class="btn-delete" title="Supprimer">
                        🗑️ Supprimer
                    </button>
                </div>
            </td>
        </tr>
    `).join('');

    updateResultCountPlannings();
}

function filterPlannings() {
    const searchTerm = document.getElementById('searchInputPlannings')?.value.toLowerCase() || '';
    const jourValue = document.getElementById('jourFilter')?.value || '';
    const salleValue = document.getElementById('salleFilter')?.value || '';
    const rows = document.querySelectorAll('#planningsTable tbody tr[data-id]');
    let visibleCount = 0;

    rows.forEach(row => {
        const activite = row.cells[0].textContent.toLowerCase();
        const jour = row.cells[2].textContent.toLowerCase();
        const salle = row.cells[4].textContent.toLowerCase();

        const matchesSearch = searchTerm === '' || 
            activite.includes(searchTerm) || 
            salle.includes(searchTerm);

        const matchesJour = jourValue === '' || jour.includes(jourValue.toLowerCase());
        const matchesSalle = salleValue === '' || salle.includes(salleValue.toLowerCase());

        if (matchesSearch && matchesJour && matchesSalle) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    updateResultCountPlannings(visibleCount);
}

function updateResultCountPlannings(count) {
    if (count === undefined) {
        const visibleRows = document.querySelectorAll('#planningsTable tbody tr[data-id]');
        count = Array.from(visibleRows).filter(row => row.style.display !== 'none').length;
    }
    
    const resultCount = document.getElementById('resultCountPlannings');
    if (resultCount) {
        resultCount.textContent = count;
    }
}

function loadSallesFilter() {
    const salleFilter = document.getElementById('salleFilter');
    if (!salleFilter) return;

    // Extraire les salles uniques du tableau
    const rows = document.querySelectorAll('#planningsTable tbody tr[data-id]');
    const salles = new Set();
    
    rows.forEach(row => {
        const salle = row.cells[4].textContent.trim();
        if (salle && salle !== 'Non assigné') {
            salles.add(salle);
        }
    });

    // Ajouter les options au filtre
    const sortedSalles = Array.from(salles).sort();
    sortedSalles.forEach(salle => {
        const option = document.createElement('option');
        option.value = salle;
        option.textContent = salle;
        salleFilter.appendChild(option);
    });
}

function exportPlannings() {
    showNotification('Export en cours...', 'info');
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = window.location.href;
    
    const ajaxInput = document.createElement('input');
    ajaxInput.type = 'hidden';
    ajaxInput.name = 'ajax';
    ajaxInput.value = '1';
    
    const entityInput = document.createElement('input');
    entityInput.type = 'hidden';
    entityInput.name = 'entity';
    entityInput.value = 'planning';
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'export';
    
    form.appendChild(ajaxInput);
    form.appendChild(entityInput);
    form.appendChild(actionInput);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    setTimeout(() => {
        showNotification('Export terminé !', 'success');
    }, 1500);
}

function sortTablePlannings(columnIndex) {
    const table = document.getElementById('planningsTable');
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr[data-id]'));

    if (currentSortColumnPlannings === columnIndex) {
        isAscendingPlannings = !isAscendingPlannings;
    } else {
        isAscendingPlannings = true;
        currentSortColumnPlannings = columnIndex;
    }

    // Réinitialiser les indicateurs
    for (let i = 0; i <= 5; i++) {
        const sortIndicator = document.getElementById(`sort-plannings-${i}`);
        if (sortIndicator) {
            sortIndicator.textContent = '↕️';
            sortIndicator.style.color = '#b8b8b8';
        }
    }

    // Mettre à jour l'indicateur actif
    const activeSortIndicator = document.getElementById(`sort-plannings-${columnIndex}`);
    if (activeSortIndicator) {
        activeSortIndicator.textContent = isAscendingPlannings ? '🔼' : '🔽';
        activeSortIndicator.style.color = '#00d4aa';
    }

    rows.sort((a, b) => {
        let aValue = a.cells[columnIndex].textContent.trim();
        let bValue = b.cells[columnIndex].textContent.trim();

        const result = aValue.localeCompare(bValue, 'fr', { numeric: true });
        return isAscendingPlannings ? result : -result;
    });

    rows.forEach(row => tbody.appendChild(row));
}

// ================== VARIABLES GLOBALES ACTIVITÉS ==================
let currentSortColumnActivites = -1;
let isAscendingActivites = true;

// ================== CRUD ACTIVITÉS SPORTIVES ==================
function createActivite() {
    // Charger les entraîneurs disponibles
    loadEntraineursForActivite().then(entraineurs => {
        const modalContent = `
            <form id="createActiviteForm" novalidate>
                <input type="hidden" name="entity" value="activite">
                <div class="form-group">
                    <label class="form-label">Nom de l'activité *</label>
                    <input type="text" name="nom" class="form-input" required placeholder="Ex: Fitness, Yoga, Musculation">
                    <div class="error-message"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description *</label>
                    <textarea name="description" class="form-input" rows="4" required placeholder="Description détaillée de l'activité"></textarea>
                    <div class="error-message"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Entraîneur responsable *</label>
                    <select name="entraineur_id" class="form-select" required>
                        <option value="">Sélectionner un entraîneur</option>
                        ${entraineurs.map(e => `<option value="${e.id}">${e.prenom} ${e.nom}</option>`).join('')}
                    </select>
                    <div class="error-message"></div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Annuler</button>
                    <button type="submit" class="btn-save">
                        <div class="loading-spinner"></div>
                        Créer l'activité
                    </button>
                </div>
            </form>
        `;

        createModal('Nouvelle Activité Sportive', modalContent);
        setupFormValidation('createActiviteForm', saveActivite);
    });
}

function editActivite(id) {
    const button = document.querySelector(`[onclick="editActivite(${id})"]`);
    showLoading(button);
    
    Promise.all([
        fetch(window.location.href, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `ajax=1&entity=activite&action=get&id=${id}`
        }).then(response => response.json()),
        loadEntraineursForActivite()
    ])
    .then(([activiteData, entraineurs]) => {
        hideLoading(button);
        
        if (activiteData.success && activiteData.data) {
            const activite = activiteData.data;

            const modalContent = `
                <form id="editActiviteForm" novalidate>
                    <input type="hidden" name="entity" value="activite">
                    <input type="hidden" name="id" value="${activite.id}">
                    <div class="form-group">
                        <label class="form-label">Nom de l'activité *</label>
                        <input type="text" name="nom" class="form-input" value="${activite.nom}" required>
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description *</label>
                        <textarea name="description" class="form-input" rows="4" required>${activite.description}</textarea>
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Entraîneur responsable *</label>
                        <select name="entraineur_id" class="form-select" required>
                            <option value="">Sélectionner un entraîneur</option>
                            ${entraineurs.map(e => `<option value="${e.id}" ${activite.entraineur_id == e.id ? 'selected' : ''}>${e.prenom} ${e.nom}</option>`).join('')}
                        </select>
                        <div class="error-message"></div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeModal()">Annuler</button>
                        <button type="submit" class="btn-save">
                            <div class="loading-spinner"></div>
                            Sauvegarder
                        </button>
                    </div>
                </form>
            `;

            createModal('Modifier l\'Activité Sportive', modalContent);
            setupFormValidation('editActiviteForm', updateActivite);
        } else {
            showNotification(activiteData.message || 'Erreur lors du chargement', 'error');
        }
    })
    .catch(error => {
        hideLoading(button);
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

function viewActivite(id) {
    const button = document.querySelector(`[onclick="viewActivite(${id})"]`);
    showLoading(button);
    
    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `ajax=1&entity=activite&action=get&id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        hideLoading(button);
        
        if (data.success && data.data) {
            const activite = data.data;
            const planningsCount = document.querySelector(`#activitesTable tr[data-id="${id}"] td:nth-child(4) .statut-badge`)?.textContent || '0 planning(s)';

            const modalContent = `
                <div class="activite-details">
                    <div class="detail-group">
                        <label class="detail-label">🏃‍♀️ Activité:</label>
                        <div class="detail-value">${activite.nom}</div>
                    </div>
                    <div class="detail-group">
                        <label class="detail-label">📝 Description:</label>
                        <div class="detail-value">${activite.description}</div>
                    </div>
                    <div class="detail-group">
                        <label class="detail-label">👨‍🏫 Entraîneur:</label>
                        <div class="detail-value">${activite.nom_entraineur || 'Non assigné'}</div>
                    </div>
                    <div class="detail-group">
                        <label class="detail-label">📅 Plannings associés:</label>
                        <div class="detail-value">${planningsCount}</div>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Fermer</button>
                    <button type="button" class="btn-edit" onclick="editActivite(${activite.id})">✏️ Modifier</button>
                </div>
            `;

            createModal('Détails de l\'Activité Sportive', modalContent);
        } else {
            showNotification(data.message || 'Erreur lors du chargement', 'error');
        }
    })
    .catch(error => {
        hideLoading(button);
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

function deleteActivite(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette activité ? Cette action est irréversible.')) {
        const button = document.querySelector(`[onclick="deleteActivite(${id})"]`);
        showLoading(button);
        
        fetch(window.location.href, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `ajax=1&entity=activite&action=delete&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            hideLoading(button);
            
            if (data.success) {
                showNotification(data.message || 'Activité supprimée avec succès', 'success');
                
                const row = document.querySelector(`#activitesTable tr[data-id="${id}"]`);
                if (row) {
                    row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(-20px)';
                    setTimeout(() => {
                        row.remove();
                        updateResultCountActivites();
                        loadActiviteStats();
                    }, 300);
                }
            } else {
                showNotification(data.message || 'Erreur lors de la suppression', 'error');
            }
        })
        .catch(error => {
            hideLoading(button);
            console.error('Erreur:', error);
            showNotification('Erreur de connexion', 'error');
        });
    }
}

function saveActivite() {
    const form = document.getElementById('createActiviteForm');
    const submitBtn = form.querySelector('.btn-save');
    showLoading(submitBtn);

    const formData = new FormData(form);
    formData.append('ajax', '1');
    formData.append('action', 'create');

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading(submitBtn);
        
        if (data.success) {
            showNotification(data.message || 'Activité créée avec succès', 'success');
            closeModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const fieldElement = form.querySelector(`[name="${field}"]`);
                    if (fieldElement) {
                        fieldElement.classList.add('error');
                        const errorMsg = fieldElement.parentNode.querySelector('.error-message');
                        if (errorMsg) {
                            errorMsg.textContent = data.errors[field];
                            errorMsg.classList.add('show');
                        }
                    }
                });
            }
            showNotification(data.message || 'Erreur lors de la création', 'error');
        }
    })
    .catch(error => {
        hideLoading(submitBtn);
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

function updateActivite() {
    const form = document.getElementById('editActiviteForm');
    const submitBtn = form.querySelector('.btn-save');
    showLoading(submitBtn);

    const formData = new FormData(form);
    formData.append('ajax', '1');
    formData.append('action', 'update');

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading(submitBtn);
        
        if (data.success) {
            showNotification(data.message || 'Activité mise à jour avec succès', 'success');
            closeModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const fieldElement = form.querySelector(`[name="${field}"]`);
                    if (fieldElement) {
                        fieldElement.classList.add('error');
                        const errorMsg = fieldElement.parentNode.querySelector('.error-message');
                        if (errorMsg) {
                            errorMsg.textContent = data.errors[field];
                            errorMsg.classList.add('show');
                        }
                    }
                });
            }
            showNotification(data.message || 'Erreur lors de la mise à jour', 'error');
        }
    })
    .catch(error => {
        hideLoading(submitBtn);
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

// ================== FONCTIONS UTILITAIRES ACTIVITÉS ==================
function loadEntraineursForActivite() {
    return fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'ajax=1&entity=activite&action=entraineurs'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            return data.data;
        } else {
            console.error('Erreur lors du chargement des entraîneurs:', data.message);
            return [];
        }
    })
    .catch(error => {
        console.error('Erreur de connexion:', error);
        return [];
    });
}

function loadActivites() {
    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'ajax=1&entity=activite&action=get_all'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populateActivitesTable(data.data);
        } else {
            showNotification(data.message || 'Erreur lors du chargement', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

function loadActiviteStats() {
    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'ajax=1&entity=activite&action=stats'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateActiviteStats(data.data);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}

function updateActiviteStats(stats) {
    document.getElementById('stat-total-activites').textContent = stats.total || 0;
    document.getElementById('stat-entraineurs-activites').textContent = Object.keys(stats.par_entraineur || {}).length;
    document.getElementById('stat-avec-plannings').textContent = stats.avec_plannings || 0;
    document.getElementById('stat-sans-plannings').textContent = stats.sans_plannings || 0;
}

function populateActivitesTable(activites) {
    const tbody = document.getElementById('activitesTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    
    if (activites.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center; padding: 2rem; color: #b8b8b8;">
                    🏃‍♀️ Aucune activité trouvée
                </td>
            </tr>
        `;
        return;
    }

    activites.forEach(activite => {
        const row = document.createElement('tr');
        row.setAttribute('data-id', activite.id);
        
        row.innerHTML = `
            <td>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(45deg, #00d4aa, #00b4d8); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem;">
                        🏃‍♀️
                    </div>
                    <span>${activite.nom}</span>
                </div>
            </td>
            <td>
                <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    ${activite.description.length > 100 ? activite.description.substring(0, 100) + '...' : activite.description}
                </div>
            </td>
            <td>
                <span style="background: rgba(0, 212, 170, 0.2); color: #00d4aa; padding: 0.2rem 0.5rem; border-radius: 12px; font-size: 0.8rem;">
                    ${activite.nom_entraineur || 'Non assigné'}
                </span>
            </td>
            <td>
                <span class="statut-badge statut-admin" style="font-size: 0.8rem;">
                    ${activite.plannings_count || 0} planning(s)
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button onclick="viewActivite(${activite.id})" class="btn-view" title="Voir les détails">
                        👁️ Voir
                    </button>
                    <button onclick="editActivite(${activite.id})" class="btn-edit" title="Modifier">
                        ✏️ Modifier
                    </button>
                    <button onclick="deleteActivite(${activite.id})" class="btn-delete" title="Supprimer">
                        🗑️ Supprimer
                    </button>
                </div>
            </td>
        `;
        
        tbody.appendChild(row);
    });
    
    updateResultCountActivites(activites.length);
}

function updateResultCountActivites(count) {
    const countElement = document.getElementById('activites-count');
    if (countElement) {
        countElement.textContent = count || 0;
    }
}

function exportActivites() {
    window.location.href = window.location.href + '?' + new URLSearchParams({
        ajax: '1',
        entity: 'activite',
        action: 'export'
    });
}

function sortTableActivites(columnIndex) {
    const table = document.getElementById('activitesTable');
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr[data-id]'));

    if (currentSortColumnActivites === columnIndex) {
        isAscendingActivites = !isAscendingActivites;
    } else {
        isAscendingActivites = true;
        currentSortColumnActivites = columnIndex;
    }

    // Réinitialiser les indicateurs
    for (let i = 0; i <= 4; i++) {
        const sortIndicator = document.getElementById(`sort-activites-${i}`);
        if (sortIndicator) {
            sortIndicator.textContent = '↕️';
            sortIndicator.style.color = '#b8b8b8';
        }
    }

    // Mettre à jour l'indicateur actif
    const activeSortIndicator = document.getElementById(`sort-activites-${columnIndex}`);
    if (activeSortIndicator) {
        activeSortIndicator.textContent = isAscendingActivites ? '🔼' : '🔽';
        activeSortIndicator.style.color = '#00d4aa';
    }

    rows.sort((a, b) => {
        let aValue = a.cells[columnIndex].textContent.trim();
        let bValue = b.cells[columnIndex].textContent.trim();

        const result = aValue.localeCompare(bValue, 'fr', { numeric: true });
        return isAscendingActivites ? result : -result;
    });

    rows.forEach(row => tbody.appendChild(row));
}
</script>









































<!-- ============= FONCTIONS JAVASCRIPT À AJOUTER DANS LA SECTION SCRIPT ============= -->

<script>
// ================== VARIABLES GLOBALES ENTRAÎNEURS ==================
let currentSortColumnEntraineurs = -1;
let isAscendingEntraineurs = true;

// ================== CRUD ENTRAÎNEURS ==================
function createEntraineur() {
    const modalContent = `
        <form id="createEntraineurForm" novalidate>
            <input type="hidden" name="entity" value="entraineur">
            <div class="form-group">
                <label class="form-label">Prénom *</label>
                <input type="text" name="prenom" class="form-input" required autocomplete="given-name">
                <div class="error-message"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Nom *</label>
                <input type="text" name="nom" class="form-input" required autocomplete="family-name">
                <div class="error-message"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-input" required autocomplete="email">
                <div class="error-message"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Mot de passe *</label>
                <input type="password" name="mot_de_passe" class="form-input" required autocomplete="new-password">
                <div class="error-message"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Statut</label>
                <select name="is_actif" class="form-select">
                    <option value="1">Actif</option>
                    <option value="0">Inactif</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal()">Annuler</button>
                <button type="submit" class="btn-save">
                    <div class="loading-spinner"></div>
                    Créer l'entraîneur
                </button>
            </div>
        </form>
    `;

    createModal('Nouvel Entraîneur', modalContent);

    // Configuration du formulaire
    setupFormValidation('createEntraineurForm', saveEntraineur);
}

function editEntraineur(id) {
    const button = document.querySelector(`[onclick="editEntraineur(${id})"]`);
    showLoading(button);
    
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=1&entity=entraineur&action=get&id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        hideLoading(button);
        
        if (data.success && data.data) {
            const entraineur = data.data;
            const modalContent = `
                <form id="editEntraineurForm" novalidate>
                    <input type="hidden" name="entity" value="entraineur">
                    <input type="hidden" name="id" value="${entraineur.id}">
                    <div class="form-group">
                        <label class="form-label">Prénom *</label>
                        <input type="text" name="prenom" class="form-input" value="${entraineur.prenom}" required>
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="nom" class="form-input" value="${entraineur.nom}" required>
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-input" value="${entraineur.email}" required>
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nouveau mot de passe (laisser vide pour ne pas modifier)</label>
                        <input type="password" name="mot_de_passe" class="form-input">
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Statut</label>
                        <select name="is_actif" class="form-select">
                            <option value="1" ${entraineur.is_actif == 1 ? 'selected' : ''}>Actif</option>
                            <option value="0" ${entraineur.is_actif == 0 ? 'selected' : ''}>Inactif</option>
                        </select>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeModal()">Annuler</button>
                        <button type="submit" class="btn-save">
                            <div class="loading-spinner"></div>
                            Sauvegarder
                        </button>
                    </div>
                </form>
            `;

            createModal('Modifier l\'Entraîneur', modalContent);
            setupFormValidation('editEntraineurForm', updateEntraineur);
        } else {
            showNotification('Erreur lors de la récupération des données', 'error');
        }
    })
    .catch(error => {
        hideLoading(button);
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

function viewEntraineur(id) {
    const button = document.querySelector(`[onclick="viewEntraineur(${id})"]`);
    showLoading(button);
    
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ajax=1&entity=entraineur&action=get&id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        hideLoading(button);
        
        if (data.success && data.data) {
            const entraineur = data.data;
            const modalContent = `
                <div style="padding: 1rem;">
                    <div style="text-align: center; margin-bottom: 2rem;">
                        <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(45deg, #00d4aa, #00b4d8); display: inline-flex; align-items: center; justify-content: center; font-weight: bold; font-size: 2rem; margin-bottom: 1rem;">
                            ${entraineur.prenom.charAt(0).toUpperCase()}${entraineur.nom.charAt(0).toUpperCase()}
                        </div>
                        <h3 style="color: #00d4aa; margin: 0;">👨‍🏫 ${entraineur.prenom} ${entraineur.nom}</h3>
                        <p style="color: #b8b8b8; margin: 0.5rem 0;">Entraîneur</p>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div style="background: rgba(15, 15, 35, 0.5); padding: 1rem; border-radius: 8px;">
                            <strong style="color: #00d4aa;">ID:</strong><br>
                            <span>${entraineur.id}</span>
                        </div>
                        <div style="background: rgba(15, 15, 35, 0.5); padding: 1rem; border-radius: 8px;">
                            <strong style="color: #00d4aa;">Email:</strong><br>
                            <a href="mailto:${entraineur.email}" style="color: #00b4d8;">${entraineur.email}</a>
                        </div>
                        <div style="background: rgba(15, 15, 35, 0.5); padding: 1rem; border-radius: 8px;">
                            <strong style="color: #00d4aa;">Statut:</strong><br>
                            <span class="statut-badge ${entraineur.is_actif == 1 ? 'statut-active' : 'statut-inactive'}">
                                ${entraineur.is_actif == 1 ? 'Actif' : 'Inactif'}
                            </span>
                        </div>
                        <div style="background: rgba(15, 15, 35, 0.5); padding: 1rem; border-radius: 8px;">
                            <strong style="color: #00d4aa;">Date d'inscription:</strong><br>
                            <span>${entraineur.date_inscription ? new Date(entraineur.date_inscription).toLocaleDateString('fr-FR') : 'Non définie'}</span>
                        </div>
                    </div>
                    
                    <div style="text-align: center; margin-top: 2rem;">
                        <button type="button" class="btn-save" onclick="editEntraineur(${entraineur.id}); closeModal();" style="margin-right: 1rem;">
                            Modifier
                        </button>
                        <button type="button" class="btn-cancel" onclick="closeModal()">
                            Fermer
                        </button>
                    </div>
                </div>
            `;

            createModal('Détails de l\'Entraîneur', modalContent);
        } else {
            showNotification('Entraîneur non trouvé', 'error');
        }
    })
    .catch(error => {
        hideLoading(button);
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

function deleteEntraineur(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet entraîneur ?\n\nCette action est irréversible !')) {
        const button = document.querySelector(`[onclick="deleteEntraineur(${id})"]`);
        showLoading(button);
        
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `ajax=1&entity=entraineur&action=delete&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            hideLoading(button);
            
            if (data.success) {
                showNotification(data.message || 'Entraîneur supprimé avec succès', 'success');
                
                const row = document.querySelector(`#entraineursTable tr[data-id="${id}"]`);
                if (row) {
                    row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(-20px)';
                    setTimeout(() => {
                        row.remove();
                        updateResultCountEntraineurs();
                        refreshStatsEntraineurs();
                    }, 300);
                }
            } else {
                showNotification(data.message || 'Erreur lors de la suppression', 'error');
            }
        })
        .catch(error => {
            hideLoading(button);
            console.error('Erreur:', error);
            showNotification('Erreur de connexion', 'error');
        });
    }
}

function saveEntraineur() {
    const form = document.getElementById('createEntraineurForm');
    const submitBtn = form.querySelector('.btn-save');
    showLoading(submitBtn);

    const formData = new FormData(form);
    formData.append('ajax', '1');
    formData.append('action', 'create');

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading(submitBtn);
        
        if (data.success) {
            showNotification(data.message || 'Entraîneur créé avec succès', 'success');
            closeModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Erreur lors de la création', 'error');
        }
    })
    .catch(error => {
        hideLoading(submitBtn);
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

function updateEntraineur() {
    const form = document.getElementById('editEntraineurForm');
    const submitBtn = form.querySelector('.btn-save');
    showLoading(submitBtn);

    const formData = new FormData(form);
    formData.append('ajax', '1');
    formData.append('action', 'update');

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading(submitBtn);
        
        if (data.success) {
            showNotification(data.message || 'Entraîneur mis à jour avec succès', 'success');
            closeModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Erreur lors de la mise à jour', 'error');
        }
    })
    .catch(error => {
        hideLoading(submitBtn);
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

// ================== FONCTIONS UTILITAIRES ENTRAÎNEURS ==================
function filterEntraineurs() {
    const searchTerm = document.getElementById('searchInputEntraineurs')?.value.toLowerCase() || '';
    const statutValue = document.getElementById('statutFilterEntraineurs')?.value.toLowerCase() || '';
    const rows = document.querySelectorAll('#entraineursTable tbody tr[data-id]');
    let visibleCount = 0;

    rows.forEach(row => {
        const nom = row.cells[0].textContent.toLowerCase();
        const prenom = row.cells[1].textContent.toLowerCase();
        const email = row.cells[2].textContent.toLowerCase();
        const statut = row.cells[4].textContent.toLowerCase();

        const matchesSearch = searchTerm === '' || 
            nom.includes(searchTerm) || 
            prenom.includes(searchTerm) || 
            email.includes(searchTerm);

        const matchesStatus = statutValue === '' || 
            (statutValue === 'actif' && statut.includes('actif')) ||
            (statutValue === 'inactif' && statut.includes('inactif'));

        if (matchesSearch && matchesStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    updateResultCountEntraineurs(visibleCount);
}

function updateResultCountEntraineurs(count) {
    if (count === undefined) {
        const visibleRows = document.querySelectorAll('#entraineursTable tbody tr[data-id]');
        count = Array.from(visibleRows).filter(row => row.style.display !== 'none').length;
    }
    
    const resultCount = document.getElementById('resultCountEntraineurs');
    if (resultCount) {
        resultCount.textContent = count;
    }
}

function exportEntraineurs() {
    showNotification('Export en cours...', 'info');
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = window.location.href;
    
    const ajaxInput = document.createElement('input');
    ajaxInput.type = 'hidden';
    ajaxInput.name = 'ajax';
    ajaxInput.value = '1';
    
    const entityInput = document.createElement('input');
    entityInput.type = 'hidden';
    entityInput.name = 'entity';
    entityInput.value = 'entraineur';
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'export';
    
    form.appendChild(ajaxInput);
    form.appendChild(entityInput);
    form.appendChild(actionInput);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    setTimeout(() => {
        showNotification('Export terminé !', 'success');
    }, 1500);
}

function refreshStatsEntraineurs() {
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'ajax=1&entity=entraineur&action=stats'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const stats = data.data;
            document.getElementById('stat-total-entraineurs').textContent = stats.total;
            document.getElementById('stat-actifs-entraineurs').textContent = stats.actifs;
            document.getElementById('stat-inactifs-entraineurs').textContent = stats.inactifs;
        }
    })
    .catch(error => console.error('Erreur refresh stats:', error));
}

function sortTableEntraineurs(columnIndex) {
    const table = document.getElementById('entraineursTable');
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr[data-id]'));

    if (currentSortColumnEntraineurs === columnIndex) {
        isAscendingEntraineurs = !isAscendingEntraineurs;
    } else {
        isAscendingEntraineurs = true;
        currentSortColumnEntraineurs = columnIndex;
    }

    // Réinitialiser les indicateurs
    for (let i = 0; i <= 4; i++) {
        const sortIndicator = document.getElementById(`sort-entraineurs-${i}`);
        if (sortIndicator) {
            sortIndicator.textContent = '↕️';
            sortIndicator.style.color = '#b8b8b8';
        }
    }

    // Mettre à jour l'indicateur actif
    const activeSortIndicator = document.getElementById(`sort-entraineurs-${columnIndex}`);
    if (activeSortIndicator) {
        activeSortIndicator.textContent = isAscendingEntraineurs ? '🔼' : '🔽';
        activeSortIndicator.style.color = '#00d4aa';
    }

    rows.sort((a, b) => {
        let aValue = a.cells[columnIndex].textContent.trim();
        let bValue = b.cells[columnIndex].textContent.trim();

        if (columnIndex === 3) { // Date
            const parseDate = (dateStr) => {
                if (dateStr === '-') return new Date(0);
                const parts = dateStr.split('/');
                if (parts.length === 3) {
                    return new Date(parts[2], parts[1] - 1, parts[0]);
                }
                return new Date(dateStr);
            };
            aValue = parseDate(aValue);
            bValue = parseDate(bValue);
            const result = aValue - bValue;
            return isAscendingEntraineurs ? result : -result;
        }

        const result = aValue.localeCompare(bValue, 'fr', { numeric: true });
        return isAscendingEntraineurs ? result : -result;
    });

    rows.forEach(row => tbody.appendChild(row));
}

// ================== FONCTION UTILITAIRE POUR LA VALIDATION ==================
function setupFormValidation(formId, submitCallback) {
    const form = document.getElementById(formId);
    const fields = form.querySelectorAll('input, select, textarea');

    // Validation en temps réel
    fields.forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
        });
        
        field.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                validateField(this);
            }
        });
    });

    // Gestion de la soumission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Nettoyer les erreurs précédentes
        const errorMessages = form.querySelectorAll('.error-message');
        errorMessages.forEach(msg => {
            msg.textContent = '';
            msg.classList.remove('show');
        });
        
        const inputs = form.querySelectorAll('.form-input, .form-select');
        inputs.forEach(input => {
            input.classList.remove('error');
        });
        
        if (validateForm(formId)) {
            submitCallback();
        }
    });

            // Focus sur le premier champ
        setTimeout(() => {
            const firstInput = form.querySelector('input:not([type="hidden"]), select');
            if (firstInput) firstInput.focus();
        }, 100);
    }

    function validateField(field) {
        const errorMsg = field.parentNode.querySelector('.error-message');
        
        // Nettoyer l'erreur précédente
        if (errorMsg) {
            errorMsg.textContent = '';
            errorMsg.classList.remove('show');
        }
        field.classList.remove('error');
        
        // Validation selon le type de champ
        if (field.hasAttribute('required') && !field.value.trim()) {
            field.classList.add('error');
            if (errorMsg) {
                errorMsg.textContent = 'Ce champ est obligatoire';
                errorMsg.classList.add('show');
            }
            return false;
        }
        
        // Validation spécifique pour les heures
        if (field.type === 'time' && field.value) {
            const timeRegex = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
            if (!timeRegex.test(field.value)) {
                field.classList.add('error');
                if (errorMsg) {
                    errorMsg.textContent = 'Format d\'heure invalide (HH:MM)';
                    errorMsg.classList.add('show');
                }
                return false;
            }
        }
        
        return true;
    }

    function validateForm(formId) {
        const form = document.getElementById(formId);
        const fields = form.querySelectorAll('input, select, textarea');
        let isValid = true;
        
        fields.forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });
        
        // Validation spécifique pour les heures de planning
        const heureDebut = form.querySelector('input[name="heure_debut"]');
        const heureFin = form.querySelector('input[name="heure_fin"]');
        
        if (heureDebut && heureFin && heureDebut.value && heureFin.value) {
            const debut = new Date(`2000-01-01T${heureDebut.value}`);
            const fin = new Date(`2000-01-01T${heureFin.value}`);
            
            if (debut >= fin) {
                isValid = false;
                heureFin.classList.add('error');
                const errorMsg = heureFin.parentNode.querySelector('.error-message');
                if (errorMsg) {
                    errorMsg.textContent = 'L\'heure de fin doit être postérieure à l\'heure de début';
                    errorMsg.classList.add('show');
                }
            }
        }
        
        return isValid;
    }
</script>



















            
    <script>
        // ================== VARIABLES GLOBALES ==================
        let currentModal = null;
        let currentSortColumn = -1;
        let isAscending = true;

        // ================== NAVIGATION ==================
        function showSection(sectionId) {
            // Masquer toutes les sections
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => section.classList.remove('active'));
            
            // Retirer la classe active de tous les nav-items
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => item.classList.remove('active'));
            
            // Afficher la section sélectionnée
            const targetSection = document.getElementById(sectionId);
            if (targetSection) {
                targetSection.classList.add('active');
            }
            
            // Ajouter la classe active au nav-item correspondant
            const activeNavItem = document.querySelector(`[data-section="${sectionId}"]`);
            if (activeNavItem) {
                activeNavItem.classList.add('active');
            }
            
            console.log(`Section active: ${sectionId}`);
        }

        // ================== UTILITAIRES ==================
        function showNotification(message, type = 'info') {
            // Supprimer les notifications existantes
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(n => n.remove());

            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Animation d'apparition
            setTimeout(() => notification.classList.add('show'), 10);
            
            // Animation de disparition
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 4000);
        }

        function showLoading(button) {
            if (button) {
                const spinner = button.querySelector('.loading-spinner');
                if (spinner) {
                    spinner.classList.add('show');
                }
                button.disabled = true;
            }
        }

        function hideLoading(button) {
            if (button) {
                const spinner = button.querySelector('.loading-spinner');
                if (spinner) {
                    spinner.classList.remove('show');
                }
                button.disabled = false;
            }
        }

        // ================== VALIDATION AVANCÉE ==================
        function validateField(field) {
            const errorMsg = field.parentNode.querySelector('.error-message');
            let isValid = true;
            let errorText = '';

            // Réinitialiser les styles d'erreur
            field.classList.remove('error');
            if (errorMsg) errorMsg.classList.remove('show');

            // Validation des champs requis
            if (field.hasAttribute('required') && !field.value.trim()) {
                isValid = false;
                errorText = 'Ce champ est obligatoire';
            }
            // Validation spécifique par type
            else if (field.type === 'email' && field.value.trim()) {
                const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                if (!emailRegex.test(field.value.trim())) {
                    isValid = false;
                    errorText = 'Format d\'email invalide';
                }
            }
            else if (field.name === 'prenom' || field.name === 'nom') {
                if (field.value.trim().length < 2) {
                    isValid = false;
                    errorText = 'Minimum 2 caractères requis';
                }
                else if (!/^[a-zA-ZÀ-ÿ\s\-']+$/.test(field.value.trim())) {
                    isValid = false;
                    errorText = 'Caractères spéciaux non autorisés';
                }
            }
            else if (field.name === 'mot_de_passe' && field.value.trim()) {
                if (field.value.length < 6) {
                    isValid = false;
                    errorText = 'Minimum 6 caractères requis';
                }
            }

            // Affichage des erreurs
            if (!isValid) {
                field.classList.add('error');
                if (errorMsg) {
                    errorMsg.textContent = errorText;
                    errorMsg.classList.add('show');
                }
            }

            return isValid;
        }

        function validateForm(formId) {
            const form = document.getElementById(formId);
            if (!form) return false;

            let isValid = true;
            const fields = form.querySelectorAll('input, select, textarea');

            // Valider chaque champ
            fields.forEach(field => {
                if (!validateField(field)) {
                    isValid = false;
                }
            });

            // Afficher une notification globale si erreur
            if (!isValid) {
                showNotification('Veuillez corriger les erreurs dans le formulaire', 'error');
            }

            return isValid;
        }

        // ================== MODAL MANAGEMENT ==================
        function createModal(title, content) {
            // Fermer toute modal existante
            closeModal();

            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title">${title}</h2>
                        <button class="modal-close" onclick="closeModal()">&times;</button>
                    </div>
                    <div class="modal-body">
                        ${content}
                    </div>
                </div>
            `;

            // Fermer en cliquant sur l'overlay
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeModal();
            });

            document.body.appendChild(modal);
            currentModal = modal;

            // Animation d'apparition
            setTimeout(() => modal.classList.add('show'), 10);

            return modal;
        }

        function closeModal() {
            if (currentModal) {
                currentModal.classList.remove('show');
                setTimeout(() => {
                    if (currentModal && currentModal.parentNode) {
                        currentModal.parentNode.removeChild(currentModal);
                    }
                    currentModal = null;
                }, 300);
            }
        }
 // ================== CRUD ADHÉRENTS ==================
        function createAdherent() {
            const modalContent = `
                <form id="createAdherentForm" novalidate>
                    <div class="form-group">
                        <label class="form-label">Prénom *</label>
                        <input type="text" name="prenom" class="form-input" required autocomplete="given-name">
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="nom" class="form-input" required autocomplete="family-name">
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-input" required autocomplete="email">
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mot de passe *</label>
                        <input type="password" name="mot_de_passe" class="form-input" required autocomplete="new-password">
                        <div class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Statut</label>
                        <select name="is_actif" class="form-select">
                            <option value="1">Actif</option>
                            <option value="0">Inactif</option>
                        </select>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeModal()">Annuler</button>
                        <button type="submit" class="btn-save">
                            <div class="loading-spinner"></div>
                            Créer l'adhérent
                        </button>
                    </div>
                </form>
            `;

            createModal('Nouvel Adhérent', modalContent);

            // Configuration du formulaire
            const form = document.getElementById('createAdherentForm');
            const fields = form.querySelectorAll('input');

            // Validation en temps réel
            fields.forEach(field => {
                field.addEventListener('blur', function() {
                    validateField(this);
                });
                
                field.addEventListener('input', function() {
                    if (this.classList.contains('error')) {
                        validateField(this);
                    }
                });
            });

            // Gestion de la soumission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Tentative de soumission du formulaire');
                
                if (validateForm('createAdherentForm')) {
                    console.log('Validation réussie - Envoi des données');
                    saveAdherent();
                } else {
                    console.log('Validation échouée - Formulaire non envoyé');
                }
            });

            // Focus sur le premier champ
            setTimeout(() => {
                const firstInput = form.querySelector('input');
                if (firstInput) firstInput.focus();
            }, 100);
        }

        function editAdherent(id) {
            const button = document.querySelector(`[onclick="editAdherent(${id})"]`);
            showLoading(button);
            
            // Récupérer les données de l'adhérent
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `ajax=1&action=get&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                hideLoading(button);
                
                if (data.success && data.data) {
                    const adherent = data.data;
                    const modalContent = `
                        <form id="editAdherentForm" novalidate>
                            <input type="hidden" name="id" value="${adherent.id}">
                            <div class="form-group">
                                <label class="form-label">Prénom *</label>
                                <input type="text" name="prenom" class="form-input" value="${adherent.prenom}" required>
                                <div class="error-message"></div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nom *</label>
                                <input type="text" name="nom" class="form-input" value="${adherent.nom}" required>
                                <div class="error-message"></div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-input" value="${adherent.email}" required>
                                <div class="error-message"></div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nouveau mot de passe (laisser vide pour ne pas modifier)</label>
                                <input type="password" name="mot_de_passe" class="form-input">
                                <div class="error-message"></div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Statut</label>
                                <select name="is_actif" class="form-select">
                                    <option value="1" ${adherent.is_actif == 1 ? 'selected' : ''}>Actif</option>
                                    <option value="0" ${adherent.is_actif == 0 ? 'selected' : ''}>Inactif</option>
                                </select>
                            </div>
                            <div class="modal-actions">
                                <button type="button" class="btn-cancel" onclick="closeModal()">Annuler</button>
                                <button type="submit" class="btn-save">
                                    <div class="loading-spinner"></div>
                                    Sauvegarder
                                </button>
                            </div>
                        </form>
                    `;

                    createModal('Modifier l\'Adhérent', modalContent);

                    // Configuration du formulaire d'édition
                    const form = document.getElementById('editAdherentForm');
                    const fields = form.querySelectorAll('input');

                    // Validation en temps réel
                    fields.forEach(field => {
                        field.addEventListener('blur', function() {
                            validateField(this);
                        });
                        
                        field.addEventListener('input', function() {
                            if (this.classList.contains('error')) {
                                validateField(this);
                            }
                        });
                    });

                    // Gestion de la soumission
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        if (validateForm('editAdherentForm')) {
                            updateAdherent();
                        }
                    });
                } else {
                    showNotification('Erreur lors de la récupération des données', 'error');
                }
            })
            .catch(error => {
                hideLoading(button);
                console.error('Erreur:', error);
                showNotification('Erreur de connexion', 'error');
            });
        }

        function viewAdherent(id) {
            const button = document.querySelector(`[onclick="viewAdherent(${id})"]`);
            showLoading(button);
            
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `ajax=1&action=get&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                hideLoading(button);
                
                if (data.success && data.data) {
                    const adherent = data.data;
                    const modalContent = `
                        <div style="padding: 1rem;">
                            <div style="text-align: center; margin-bottom: 2rem;">
                                <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(45deg, #00d4aa, #00b4d8); display: inline-flex; align-items: center; justify-content: center; font-weight: bold; font-size: 2rem; margin-bottom: 1rem;">
                                    ${adherent.prenom.charAt(0).toUpperCase()}${adherent.nom.charAt(0).toUpperCase()}
                                </div>
                                <h3 style="color: #00d4aa; margin: 0;">${adherent.prenom} ${adherent.nom}</h3>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                                <div style="background: rgba(15, 15, 35, 0.5); padding: 1rem; border-radius: 8px;">
                                    <strong style="color: #00d4aa;">ID:</strong><br>
                                    <span>${adherent.id}</span>
                                </div>
                                <div style="background: rgba(15, 15, 35, 0.5); padding: 1rem; border-radius: 8px;">
                                    <strong style="color: #00d4aa;">Email:</strong><br>
                                    <a href="mailto:${adherent.email}" style="color: #00b4d8;">${adherent.email}</a>
                                </div>
                                <div style="background: rgba(15, 15, 35, 0.5); padding: 1rem; border-radius: 8px;">
                                    <strong style="color: #00d4aa;">Statut:</strong><br>
                                    <span class="statut-badge ${adherent.is_actif == 1 ? 'statut-active' : 'statut-inactive'}">
                                        ${adherent.is_actif == 1 ? 'Actif' : 'Inactif'}
                                    </span>
                                </div>
                                <div style="background: rgba(15, 15, 35, 0.5); padding: 1rem; border-radius: 8px;">
                                    <strong style="color: #00d4aa;">Date d'inscription:</strong><br>
                                    <span>${adherent.date_inscription ? new Date(adherent.date_inscription).toLocaleDateString('fr-FR') : 'Non définie'}</span>
                                </div>
                            </div>
                            
                            <div style="text-align: center; margin-top: 2rem;">
                                <button type="button" class="btn-save" onclick="editAdherent(${adherent.id}); closeModal();" style="margin-right: 1rem;">
                                    Modifier
                                </button>
                                <button type="button" class="btn-cancel" onclick="closeModal()">
                                    Fermer
                                </button>
                            </div>
                        </div>
                    `;

                    createModal('Détails de l\'Adhérent', modalContent);
                } else {
                    showNotification('Adhérent non trouvé', 'error');
                }
            })
            .catch(error => {
                hideLoading(button);
                console.error('Erreur:', error);
                showNotification('Erreur de connexion', 'error');
            });
        }

        function deleteAdherent(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet adhérent ?\n\nCette action est irréversible !')) {
                const button = document.querySelector(`[onclick="deleteAdherent(${id})"]`);
                showLoading(button);
                
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `ajax=1&action=delete&id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading(button);
                    
                    if (data.success) {
                        showNotification(data.message || 'Adhérent supprimé avec succès', 'success');
                        
                        // Supprimer la ligne du tableau avec animation
                        const row = document.querySelector(`tr[data-id="${id}"]`);
                        if (row) {
                            row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(-20px)';
                            setTimeout(() => {
                                row.remove();
                                updateResultCount();
                                refreshStats();
                            }, 300);
                        }
                    } else {
                        showNotification(data.message || 'Erreur lors de la suppression', 'error');
                    }
                })
                .catch(error => {
                    hideLoading(button);
                    console.error('Erreur:', error);
                    showNotification('Erreur de connexion', 'error');
                });
            }
        }

        function saveAdherent() {
            const form = document.getElementById('createAdherentForm');
            const submitBtn = form.querySelector('.btn-save');
            showLoading(submitBtn);

            const formData = new FormData(form);
            formData.append('ajax', '1');
            formData.append('action', 'create');

            console.log('Envoi des données:', Array.from(formData.entries()));

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideLoading(submitBtn);
                
                console.log('Réponse serveur:', data);
                
                if (data.success) {
                    showNotification(data.message || 'Adhérent créé avec succès', 'success');
                    closeModal();
                    // Recharger la page pour afficher le nouvel adhérent
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Erreur lors de la création', 'error');
                }
            })
            .catch(error => {
                hideLoading(submitBtn);
                console.error('Erreur:', error);
                showNotification('Erreur de connexion', 'error');
            });
        }

        function updateAdherent() {
            const form = document.getElementById('editAdherentForm');
            const submitBtn = form.querySelector('.btn-save');
            showLoading(submitBtn);

            const formData = new FormData(form);
            formData.append('ajax', '1');
            formData.append('action', 'update');

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideLoading(submitBtn);
                
                if (data.success) {
                    showNotification(data.message || 'Adhérent mis à jour avec succès', 'success');
                    closeModal();
                    // Recharger la page pour afficher les modifications
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Erreur lors de la mise à jour', 'error');
                }
            })
            .catch(error => {
                hideLoading(submitBtn);
                console.error('Erreur:', error);
                showNotification('Erreur de connexion', 'error');
            });
        }

        // ================== FONCTIONS UTILITAIRES ==================
        function refreshStats() {
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'ajax=1&action=stats'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const stats = data.data;
                    document.getElementById('stat-total').textContent = stats.total;
                    document.getElementById('stat-actifs').textContent = stats.actifs;
                    document.getElementById('stat-inactifs').textContent = stats.inactifs;
                }
            })
            .catch(error => console.error('Erreur refresh stats:', error));
        }

        function filterAdherents() {
            const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
            const statutValue = document.getElementById('statutFilter')?.value.toLowerCase() || '';
            const rows = document.querySelectorAll('#adherentsTable tbody tr[data-id]');
            let visibleCount = 0;

            rows.forEach(row => {
                const nom = row.cells[0].textContent.toLowerCase();
                const prenom = row.cells[1].textContent.toLowerCase();
                const email = row.cells[2].textContent.toLowerCase();
                const statut = row.cells[4].textContent.toLowerCase();

                const matchesSearch = searchTerm === '' || 
                    nom.includes(searchTerm) || 
                    prenom.includes(searchTerm) || 
                    email.includes(searchTerm);

                const matchesStatus = statutValue === '' || 
                    (statutValue === 'actif' && statut.includes('actif')) ||
                    (statutValue === 'inactif' && statut.includes('inactif'));

                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            updateResultCount(visibleCount);
        }

        function updateResultCount(count) {
            if (count === undefined) {
                const visibleRows = document.querySelectorAll('#adherentsTable tbody tr[data-id]');
                count = Array.from(visibleRows).filter(row => row.style.display !== 'none').length;
            }
            
            const resultCount = document.getElementById('resultCount');
            if (resultCount) {
                resultCount.textContent = count;
            }
        }

        function exportAdherents() {
            showNotification('Export en cours...', 'info');
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = window.location.href;
            
            const ajaxInput = document.createElement('input');
            ajaxInput.type = 'hidden';
            ajaxInput.name = 'ajax';
            ajaxInput.value = '1';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'export';
            
            form.appendChild(ajaxInput);
            form.appendChild(actionInput);
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
            
            setTimeout(() => {
                showNotification('Export terminé !', 'success');
            }, 1500);
        }

        // ================== TRI DU TABLEAU ==================
        function sortTable(columnIndex) {
            const table = document.getElementById('adherentsTable');
            if (!table) return;
            
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr[data-id]'));

            if (currentSortColumn === columnIndex) {
                isAscending = !isAscending;
            } else {
                isAscending = true;
                currentSortColumn = columnIndex;
            }

            // Réinitialiser les indicateurs
            for (let i = 0; i <= 4; i++) {
                const sortIndicator = document.getElementById(`sort-${i}`);
                if (sortIndicator) {
                    sortIndicator.textContent = '↕️';
                    sortIndicator.style.color = '#b8b8b8';
                }
            }
 // Mettre à jour l'indicateur actif
            const activeSortIndicator = document.getElementById(`sort-${columnIndex}`);
            if (activeSortIndicator) {
                activeSortIndicator.textContent = isAscending ? '🔼' : '🔽';
                activeSortIndicator.style.color = '#00d4aa';
            }

            rows.sort((a, b) => {
                let aValue = a.cells[columnIndex].textContent.trim();
                let bValue = b.cells[columnIndex].textContent.trim();

                if (columnIndex === 3) { // Date
                    const parseDate = (dateStr) => {
                        if (dateStr === '-') return new Date(0);
                        const parts = dateStr.split('/');
                        if (parts.length === 3) {
                            return new Date(parts[2], parts[1] - 1, parts[0]);
                        }
                        return new Date(dateStr);
                    };
                    aValue = parseDate(aValue);
                    bValue = parseDate(bValue);
                    const result = aValue - bValue;
                    return isAscending ? result : -result;
                }

                const result = aValue.localeCompare(bValue, 'fr', { numeric: true });
                return isAscending ? result : -result;
            });

            rows.forEach(row => tbody.appendChild(row));
        }

        // ================== GESTION DES ÉVÉNEMENTS ==================
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // ================== INITIALISATION ==================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Dashboard PulsePlay CRUD initialisé');
            
            // Auto-focus sur la recherche
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.focus();
            }
            
            showNotification('Dashboard chargé avec succès!', 'success');
        });
    </script>
</body>
</html>
                </div>       