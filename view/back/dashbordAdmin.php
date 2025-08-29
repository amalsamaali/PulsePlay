<?php 
require_once '../../config.php';
session_start();

// Récupérer les informations de l'utilisateur admin
$user = $_SESSION['user'];
$initiales = strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1));

// Initialiser les contrôleurs
require_once __DIR__ . '/../../controller/AdherentController.php';
$adherentController = new AdherentController();
require_once __DIR__ . '/../../controller/EntraineurController.php';
$entraineurController = new EntraineurController();

// Modifier la section de gestion des requêtes AJAX pour inclure les entraîneurs
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    // Vérifier si c'est pour les entraîneurs
    if (isset($_POST['entity']) && $_POST['entity'] === 'entraineur') {
        $entraineurController->handleAjaxRequest();
    } else {
        // Par défaut, traiter comme adhérent
        $adherentController->handleAjaxRequest();
    }
    exit;
}

// Récupérer les données pour les entraîneurs
$entraineurs = $entraineurController->getAllEntraineurs();
$statsEntraineurs = $entraineurController->getEntraineurStats();

// Gérer les requêtes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $adherentController->handleAjaxRequest();
    exit;
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
    <title>Dashboard Admin - PulsePlay</title>
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
            <div class="logo">PulsePlay Admin</div>
            <div class="admin-info">
                <div class="admin-avatar"><?php echo $initiales; ?></div>
                <span><?php echo $user['prenom'] . ' ' . $user['nom']; ?></span>
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
</main>
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
        
        if (validateForm(formId)) {
            submitCallback();
        }
    });

    // Focus sur le premier champ
    setTimeout(() => {
        const firstInput = form.querySelector('input:not([type="hidden"])');
        if (firstInput) firstInput.focus();
    }, 100);
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