<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un adhérent
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'adherent') {
    header('Location: ../front/login.php');
    exit;
}

$user = $_SESSION['user'];

// Inclure les contrôleurs nécessaires
require_once '../../controller/ActiviteSportiveController.php';
require_once '../../controller/PlanningController.php';
require_once '../../controller/AdherentController.php';
require_once '../../controller/InscriptionActiviteController.php';

$activiteController = new ActiviteSportiveController();
$planningController = new PlanningController();
$adherentController = new AdherentController();
$inscriptionController = new InscriptionActiviteController();

// Récupérer les données
$activites = $activiteController->getAllActivites();
$plannings = $planningController->getAllPlannings();
$statsPlanning = $planningController->getPlanningStatistics();

// Récupérer les informations de l'adhérent
$adherent = $adherentController->getAdherentById($user['id']);

// Récupérer les activités auxquelles l'adhérent est inscrit
$userActivityIds = $inscriptionController->getAdherentActivityIds($user['id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Adhérent - <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?> - PulsePlay</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
            min-height: 100vh;
            color: white;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .header {
            background: rgba(15, 15, 35, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 20px 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo i {
            font-size: 2.5rem;
            color: #00d4aa;
        }

        .logo h1 {
            font-size: 2rem;
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .user-details h3 {
            color: white;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .user-details span {
            color: #00d4aa;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .logout-btn {
            background: transparent;
            border: 2px solid #00d4aa;
            color: #00d4aa;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .logout-btn:hover {
            background: #00d4aa;
            color: white;
            transform: translateY(-2px);
        }

        /* Navigation */
        .nav-tabs {
            display: flex;
            background: rgba(15, 15, 35, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            gap: 10px;
        }

        .nav-tab {
            flex: 1;
            padding: 15px 20px;
            background: transparent;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 500;
            color: #b8b8b8;
            transition: all 0.3s ease;
            text-align: center;
        }

        .nav-tab.active {
            background: rgba(0, 212, 170, 0.1);
            color: #00d4aa;
            box-shadow: 0 4px 15px rgba(0, 212, 170, 0.2);
        }

        .nav-tab:hover:not(.active) {
            background: rgba(0, 212, 170, 0.1);
            color: #00d4aa;
        }

        /* Sections */
        .section {
            display: none;
            background: rgba(15, 15, 35, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .section.active {
            display: block;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .section-title {
            font-size: 1.8rem;
            color: #00d4aa;
            font-weight: 700;
        }

        .section-subtitle {
            color: #b8b8b8;
            font-size: 1rem;
            margin-top: 5px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #00d4aa, #00b4d8);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 212, 170, 0.3);
        }

        .stat-card h3 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .stat-card p {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* Tables */
        .table-container {
            background: rgba(26, 26, 46, 0.8);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .table-header {
            background: linear-gradient(135deg, #00d4aa, #00b4d8);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            font-size: 1.3rem;
            font-weight: 600;
        }

        .search-box {
            display: flex;
            gap: 10px;
        }

        .search-input {
            padding: 10px 15px;
            border: none;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            outline: none;
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.8);
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #00d4aa, #00b4d8);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #00d4aa, #20c997);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b, #fd7e14);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        th {
            background: rgba(0, 212, 170, 0.1);
            font-weight: 600;
            color: #00d4aa;
        }

        tr:hover {
            background: rgba(0, 212, 170, 0.05);
        }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #2a2a4a;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(15, 15, 35, 0.5);
            color: white;
        }

        .form-input:focus {
            outline: none;
            border-color: #00d4aa;
            box-shadow: 0 0 0 3px rgba(0, 212, 170, 0.2);
        }

        .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #2a2a4a;
            border-radius: 10px;
            font-size: 1rem;
            background: rgba(15, 15, 35, 0.5);
            color: white;
            cursor: pointer;
        }

        /* Modals */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: rgba(15, 15, 35, 0.95);
            margin: 5% auto;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            position: relative;
            border: 1px solid rgba(0, 212, 170, 0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #00d4aa;
        }

        .close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
            padding: 5px;
        }

        .close:hover {
            color: #333;
        }

        /* Messages */
        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .nav-tabs {
                flex-direction: column;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .table-header {
                flex-direction: column;
                gap: 15px;
            }

            .search-box {
                width: 100%;
            }

            .search-input {
                flex: 1;
            }
        }

        /* Loading */
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Activity cards */
        .activities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .activity-card {
            background: rgba(26, 26, 46, 0.8);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 212, 170, 0.1);
        }

        .activity-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .activity-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: white;
        }

        .activity-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-available {
            background: #d4edda;
            color: #155724;
        }

        .status-registered {
            background: #cce5ff;
            color: #004085;
        }

        .activity-description {
            color: #b8b8b8;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .activity-trainer {
            color: #00d4aa;
            font-weight: 500;
            margin-bottom: 15px;
        }

        .activity-sessions {
            color: #b8b8b8;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        /* Profile section */
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }

        .profile-card {
            background: rgba(26, 26, 46, 0.8);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(0, 212, 170, 0.1);
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 3rem;
            margin: 0 auto 20px;
        }

        .profile-info h3 {
            text-align: center;
            margin-bottom: 20px;
            color: white;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-label {
            font-weight: 500;
            color: #b8b8b8;
        }

        .info-value {
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                <i class="fas fa-dumbbell"></i>
                <h1>PulsePlay Adhérent</h1>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1)); ?>
                </div>
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h3>
                    <span>Adhérent</span>
                </div>
                <a href="../../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Déconnexion
                </a>
            </div>
        </div>

        <!-- Navigation -->
        <div class="nav-tabs">
            <button class="nav-tab active" onclick="showSection('activities')">
                <i class="fas fa-dumbbell"></i>
                Activités Sportives
            </button>
            <button class="nav-tab" onclick="showSection('planning')">
                <i class="fas fa-calendar-alt"></i>
                Planning
            </button>
            <button class="nav-tab" onclick="showSection('profile')">
                <i class="fas fa-user"></i>
                Mon Profil
            </button>
        </div>

        <!-- Activities Section -->
        <div id="activities" class="section active">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Activités Sportives</h2>
                    <p class="section-subtitle">Inscrivez-vous aux activités qui vous intéressent</p>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo count($activites); ?></h3>
                    <p>Activités Disponibles</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $statsPlanning['total_plannings'] ?? 0; ?></h3>
                    <p>Sessions Planifiées</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $statsPlanning['activites_planifiees'] ?? 0; ?></h3>
                    <p>Activités avec Planning</p>
                </div>
            </div>

            <div class="activities-grid" id="activitiesGrid">
                <!-- Les activités seront chargées dynamiquement -->
            </div>
        </div>

        <!-- Planning Section -->
        <div id="planning" class="section">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Planning des Activités</h2>
                    <p class="section-subtitle">Consultez les horaires de toutes les activités</p>
                </div>
                <div class="search-box">
                    <input type="text" id="planningSearch" class="search-input" placeholder="Rechercher une activité...">
                    <button class="btn btn-primary" onclick="searchPlanning()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <div class="table-container">
                <table id="planningTable">
                    <thead>
                        <tr>
                            <th>Activité</th>
                            <th>Jour</th>
                            <th>Heure Début</th>
                            <th>Heure Fin</th>
                            <th>Salle</th>
                            <th>Entraîneur</th>
                        </tr>
                    </thead>
                    <tbody id="planningTableBody">
                        <!-- Les données seront chargées dynamiquement -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Profile Section -->
        <div id="profile" class="section">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Mon Profil</h2>
                    <p class="section-subtitle">Gérez vos informations personnelles</p>
                </div>
                <button class="btn btn-primary" onclick="editProfile()">
                    <i class="fas fa-edit"></i>
                    Modifier
                </button>
            </div>

            <div class="profile-grid">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1)); ?>
                    </div>
                    <div class="profile-info">
                        <h3><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h3>
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Rôle:</span>
                            <span class="info-value">Adhérent</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Statut:</span>
                            <span class="info-value"><?php echo $user['is_actif'] ? 'Actif' : 'Inactif'; ?></span>
                        </div>
                    </div>
                </div>

                <div class="profile-card">
                    <h3>Mes Activités Inscrites</h3>
                    <div id="myActivities">
                        <!-- Les activités inscrites seront chargées dynamiquement -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Modifier Mon Profil</h3>
                <button class="close" onclick="closeModal('editProfileModal')">&times;</button>
            </div>
            <form id="editProfileForm">
                <div class="form-group">
                    <label class="form-label">Prénom</label>
                    <input type="text" id="editPrenom" class="form-input" value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nom</label>
                    <input type="text" id="editNom" class="form-input" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" id="editEmail" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nouveau mot de passe (optionnel)</label>
                    <input type="password" id="editPassword" class="form-input" placeholder="Laissez vide pour ne pas changer">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <input type="password" id="editPasswordConfirm" class="form-input" placeholder="Confirmez le nouveau mot de passe">
                </div>
                <div style="display: flex; gap: 15px; justify-content: flex-end;">
                    <button type="button" class="btn" onclick="closeModal('editProfileModal')">Annuler</button>
                    <button type="submit" class="btn btn-primary">Sauvegarder</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading -->
    <div id="loading" class="loading">
        <div class="spinner"></div>
        <p>Chargement en cours...</p>
    </div>

    <script>
        // Variables globales
        let activities = <?php echo json_encode($activites); ?>;
        let plannings = <?php echo json_encode($plannings); ?>;
        let userActivities = <?php echo json_encode($userActivityIds); ?>; // Activités auxquelles l'adhérent est inscrit
        const userId = <?php echo $user['id']; ?>;

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            loadActivities();
            loadPlanning();
            loadUserActivities();
        });

        // Navigation
        function showSection(sectionName) {
            // Masquer toutes les sections
            document.querySelectorAll('.section').forEach(section => {
                section.classList.remove('active');
            });

            // Masquer tous les onglets
            document.querySelectorAll('.nav-tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Afficher la section demandée
            document.getElementById(sectionName).classList.add('active');

            // Activer l'onglet correspondant
            event.target.classList.add('active');
        }

        // Charger les activités
        function loadActivities() {
            const grid = document.getElementById('activitiesGrid');
            grid.innerHTML = '';

            activities.forEach(activity => {
                const isRegistered = userActivities.includes(activity.id);
                const card = document.createElement('div');
                card.className = 'activity-card';
                card.innerHTML = `
                    <div class="activity-header">
                        <h4 class="activity-title">${activity.nom}</h4>
                        <span class="activity-status ${isRegistered ? 'status-registered' : 'status-available'}">
                            ${isRegistered ? 'Inscrit' : 'Disponible'}
                        </span>
                    </div>
                    <p class="activity-description">${activity.description || 'Aucune description disponible'}</p>
                    <p class="activity-trainer">
                        <i class="fas fa-user-tie"></i>
                        Entraîneur: ${activity.nom_entraineur || 'Non assigné'}
                    </p>
                    <p class="activity-sessions">
                        <i class="fas fa-calendar"></i>
                        ${activity.plannings_count || 0} session(s) planifiée(s)
                    </p>
                    <button class="btn ${isRegistered ? 'btn-danger' : 'btn-success'}" 
                            onclick="${isRegistered ? 'unregisterActivity' : 'registerActivity'}(${activity.id})">
                        <i class="fas ${isRegistered ? 'fa-times' : 'fa-plus'}"></i>
                        ${isRegistered ? 'Se désinscrire' : 'S\'inscrire'}
                    </button>
                `;
                grid.appendChild(card);
            });
        }

        // S'inscrire à une activité
        function registerActivity(activityId) {
            showLoading();
            
            fetch('../../controller/InscriptionActiviteController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'ajax': '1',
                    'action': 'register',
                    'adherent_id': userId,
                    'activite_id': activityId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    userActivities.push(activityId);
                    showMessage(data.message, 'success');
                    loadActivities();
                    loadUserActivities();
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showMessage('Erreur lors de l\'inscription', 'error');
            })
            .finally(() => {
                hideLoading();
            });
        }

        // Se désinscrire d'une activité
        function unregisterActivity(activityId) {
            if (confirm('Êtes-vous sûr de vouloir vous désinscrire de cette activité ?')) {
                showLoading();
                
                fetch('../../controller/InscriptionActiviteController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'ajax': '1',
                        'action': 'unregister',
                        'adherent_id': userId,
                        'activite_id': activityId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        userActivities = userActivities.filter(id => id !== activityId);
                        showMessage(data.message, 'success');
                        loadActivities();
                        loadUserActivities();
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showMessage('Erreur lors de la désinscription', 'error');
                })
                .finally(() => {
                    hideLoading();
                });
            }
        }

        // Charger le planning
        function loadPlanning() {
            const tbody = document.getElementById('planningTableBody');
            tbody.innerHTML = '';

            plannings.forEach(planning => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${planning.nom_activite || 'Activité inconnue'}</td>
                    <td>${planning.jour_semaine}</td>
                    <td>${planning.heure_debut}</td>
                    <td>${planning.heure_fin}</td>
                    <td>${planning.salle || 'Non spécifiée'}</td>
                    <td>${planning.nom_entraineur || 'Non assigné'}</td>
                `;
                tbody.appendChild(row);
            });
        }

        // Rechercher dans le planning
        function searchPlanning() {
            const searchTerm = document.getElementById('planningSearch').value.toLowerCase();
            const rows = document.querySelectorAll('#planningTableBody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        }

        // Charger les activités de l'utilisateur
        function loadUserActivities() {
            const container = document.getElementById('myActivities');
            container.innerHTML = '';

            if (userActivities.length === 0) {
                container.innerHTML = '<p style="color: #666; text-align: center;">Aucune activité inscrite pour le moment.</p>';
                return;
            }

            const userActivitiesList = activities.filter(activity => userActivities.includes(activity.id));
            
            userActivitiesList.forEach(activity => {
                const activityDiv = document.createElement('div');
                activityDiv.style.cssText = 'padding: 15px; border-bottom: 1px solid #f0f0f0;';
                activityDiv.innerHTML = `
                    <h4 style="margin-bottom: 5px; color: #333;">${activity.nom}</h4>
                    <p style="color: #666; font-size: 0.9rem;">${activity.description || 'Aucune description'}</p>
                    <p style="color: #667eea; font-size: 0.8rem; margin-top: 5px;">
                        <i class="fas fa-user-tie"></i> ${activity.nom_entraineur || 'Non assigné'}
                    </p>
                    <button class="btn btn-danger" style="margin-top: 10px; font-size: 0.8rem;" 
                            onclick="unregisterActivity(${activity.id})">
                        <i class="fas fa-times"></i> Se désinscrire
                    </button>
                `;
                container.appendChild(activityDiv);
            });
        }

        // Modifier le profil
        function editProfile() {
            document.getElementById('editProfileModal').style.display = 'block';
        }

        // Fermer un modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Gestion du formulaire de modification du profil
        document.getElementById('editProfileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('editPassword').value;
            const passwordConfirm = document.getElementById('editPasswordConfirm').value;
            
            if (password && password !== passwordConfirm) {
                showMessage('Les mots de passe ne correspondent pas.', 'error');
                return;
            }
            
            showLoading();
            
            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('action', 'update');
            formData.append('id', userId);
            formData.append('prenom', document.getElementById('editPrenom').value);
            formData.append('nom', document.getElementById('editNom').value);
            formData.append('email', document.getElementById('editEmail').value);
            if (password) {
                formData.append('mot_de_passe', password);
            }
            
            fetch('../../controller/UtilisateurController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Profil mis à jour avec succès !', 'success');
                    closeModal('editProfileModal');
                    // Recharger la page pour mettre à jour les informations affichées
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showMessage(data.message || 'Erreur lors de la mise à jour', 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showMessage('Erreur lors de la mise à jour du profil', 'error');
            })
            .finally(() => {
                hideLoading();
            });
        });

        // Afficher un message
        function showMessage(text, type) {
            const message = document.createElement('div');
            message.className = `message ${type}`;
            message.textContent = text;
            
            document.querySelector('.container').insertBefore(message, document.querySelector('.nav-tabs'));
            
            setTimeout(() => {
                message.remove();
            }, 5000);
        }

        // Afficher/masquer le loading
        function showLoading() {
            document.getElementById('loading').style.display = 'block';
        }

        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
        }

        // Fermer les modals en cliquant à l'extérieur
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
