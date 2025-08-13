<?php
session_start();

// Vérifier si l'utilisateur est connecté et a le rôle admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    // Rediriger vers la page d'accueil si l'utilisateur n'est pas admin
    header('Location: /PulsePlay/view/front/index.php');
    exit;
}

// Récupérer les informations de l'utilisateur admin
$user = $_SESSION['user'];
$initiales = strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1));
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

        /* Sidebar */
        .dashboard-container {
            display: flex;
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
            gap: 2rem;
        }

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
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(0, 212, 170, 0.1);
            color: #00d4aa;
            border-left: 4px solid #00d4aa;
        }

        .nav-icon {
            font-size: 1.2rem;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            background: linear-gradient(145deg, rgba(26, 26, 46, 0.6), rgba(22, 33, 62, 0.6));
            border-radius: 15px;
            padding: 2rem;
            border: 1px solid rgba(0, 212, 170, 0.2);
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

        /* Section */
        .section {
            margin-bottom: 3rem;
            display: none;
        }

        .section.active {
            display: block;
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

        /* Table */
        .table-container {
            background: rgba(15, 15, 35, 0.5);
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid rgba(0, 212, 170, 0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(0, 212, 170, 0.1);
        }

        th {
            background: rgba(0, 212, 170, 0.1);
            color: #00d4aa;
            font-weight: bold;
        }

        tr:hover {
            background: rgba(0, 212, 170, 0.05);
        }

        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .status-active {
            background: rgba(0, 212, 170, 0.2);
            color: #00d4aa;
        }

        .status-inactive {
            background: rgba(255, 107, 107, 0.2);
            color: #ff6b6b;
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-edit, .btn-delete, .btn-view {
            padding: 0.4rem 0.8rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: rgba(0, 180, 216, 0.2);
            color: #00b4d8;
        }

        .btn-delete {
            background: rgba(255, 107, 107, 0.2);
            color: #ff6b6b;
        }

        .btn-view {
            background: rgba(0, 212, 170, 0.2);
            color: #00d4aa;
        }

        .btn-edit:hover, .btn-delete:hover, .btn-view:hover {
            transform: translateY(-2px);
            opacity: 0.8;
        }

        /* Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: linear-gradient(145deg, #1a1a2e, #16213e);
            padding: 2rem;
            border-radius: 15px;
            width: 500px;
            max-width: 90vw;
            border: 1px solid rgba(0, 212, 170, 0.2);
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .modal-title {
            font-size: 1.5rem;
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .close-btn {
            background: none;
            border: none;
            color: #b8b8b8;
            font-size: 1.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .close-btn:hover {
            color: #00d4aa;
            transform: rotate(90deg);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #00d4aa;
            font-weight: bold;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid rgba(0, 212, 170, 0.3);
            border-radius: 8px;
            background: rgba(15, 15, 35, 0.5);
            color: white;
            font-size: 1rem;
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #00d4aa;
            box-shadow: 0 0 0 2px rgba(0, 212, 170, 0.2);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        .btn-save, .btn-cancel {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-save {
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            color: white;
        }

        .btn-cancel {
            background: transparent;
            color: #b8b8b8;
            border: 1px solid #b8b8b8;
        }

        .btn-save:hover, .btn-cancel:hover {
            transform: translateY(-2px);
        }

        /* Calendar Grid */
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: rgba(0, 212, 170, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .calendar-day {
            background: rgba(15, 15, 35, 0.8);
            padding: 1rem;
            min-height: 120px;
            position: relative;
        }

        .calendar-day-number {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .calendar-event {
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-bottom: 0.2rem;
            cursor: pointer;
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
        }

        @media (max-width: 768px) {
            .header-container {
                padding: 0 1rem;
            }
            
            .dashboard-container {
                padding: 0 1rem;
            }
            
            .modal-content {
                width: 95vw;
                padding: 1rem;
            }
        }

        /* Search Bar */
        .search-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            align-items: center;
        }

        .search-input {
            flex: 1;
            padding: 0.8rem;
            border: 1px solid rgba(0, 212, 170, 0.3);
            border-radius: 25px;
            background: rgba(15, 15, 35, 0.5);
            color: white;
            font-size: 1rem;
        }

        .search-input:focus {
            outline: none;
            border-color: #00d4aa;
            box-shadow: 0 0 0 2px rgba(0, 212, 170, 0.2);
        }

        .filter-select {
            padding: 0.8rem;
            border: 1px solid rgba(0, 212, 170, 0.3);
            border-radius: 8px;
            background: rgba(15, 15, 35, 0.5);
            color: white;
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
            <div class="nav-item active" data-section="dashboard">
                <span class="nav-icon">📊</span>
                <span>Tableau de bord</span>
            </div>
            <div class="nav-item" data-section="adherents">
                <span class="nav-icon">👥</span>
                <span>Adhérents</span>
            </div>
            <div class="nav-item" data-section="entraineurs">
                <span class="nav-icon">🏃‍♂️</span>
                <span>Entraîneurs</span>
            </div>
            <div class="nav-item" data-section="planning">
                <span class="nav-icon">📅</span>
                <span>Planning</span>
            </div>
            <div class="nav-item" data-section="inscriptions">
                <span class="nav-icon">📝</span>
                <span>Inscriptions</span>
            </div>
            <div class="nav-item" data-section="activites">
                <span class="nav-icon">⚽</span>
                <span>Activités</span>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Dashboard Section -->
            <div id="dashboard" class="section active">
                <div class="section-header">
                    <h2 class="section-title">Tableau de bord</h2>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">👥</div>
                        </div>
                        <div class="stat-number">247</div>
                        <div class="stat-label">Adhérents actifs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">🏃‍♂️</div>
                        </div>
                        <div class="stat-number">18</div>
                        <div class="stat-label">Entraîneurs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">📅</div>
                        </div>
                        <div class="stat-number">45</div>
                        <div class="stat-label">Sessions cette semaine</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">📝</div>
                        </div>
                        <div class="stat-number">89</div>
                        <div class="stat-label">Inscriptions en attente</div>
                    </div>
                </div>

                <div class="section-header">
                    <h3 style="color: #00d4aa;">Activité récente</h3>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Action</th>
                                <th>Utilisateur</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Aujourd'hui 14:30</td>
                                <td>Nouvelle inscription</td>
                                <td>Marie Dubois</td>
                                <td><span class="status-badge status-pending">En attente</span></td>
                            </tr>
                            <tr>
                                <td>Aujourd'hui 12:15</td>
                                <td>Session annulée</td>
                                <td>Pierre Martin</td>
                                <td><span class="status-badge status-inactive">Annulé</span></td>
                            </tr>
                            <tr>
                                <td>Hier 16:45</td>
                                <td>Nouvel entraîneur</td>
                                <td>Jean Leroy</td>
                                <td><span class="status-badge status-active">Approuvé</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Adhérents Section -->
            <div id="adherents" class="section">
                <div class="section-header">
                    <h2 class="section-title">Gestion des Adhérents</h2>
                    <button class="add-btn" onclick="openModal('adherentModal')">
                        <span>+</span> Ajouter Adhérent
                    </button>
                </div>

                <div class="search-bar">
                    <input type="text" class="search-input" placeholder="Rechercher un adhérent...">
                    <select class="filter-select">
                        <option>Tous les statuts</option>
                        <option>Actif</option>
                        <option>Inactif</option>
                        <option>Suspendu</option>
                    </select>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Date d'inscription</th>
                                <th>Activités</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Sophie Laurent</td>
                                <td>sophie.laurent@email.com</td>
                                <td>15 Jan 2025</td>
                                <td>Fitness, Yoga</td>
                                <td><span class="status-badge status-active">Actif</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-view">Voir</button>
                                        <button class="btn-edit">Modifier</button>
                                        <button class="btn-delete">Supprimer</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Marc Durand</td>
                                <td>marc.durand@email.com</td>
                                <td>12 Jan 2025</td>
                                <td>Musculation</td>
                                <td><span class="status-badge status-active">Actif</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-view">Voir</button>
                                        <button class="btn-edit">Modifier</button>
                                        <button class="btn-delete">Supprimer</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Julie Moreau</td>
                                <td>julie.moreau@email.com</td>
                                <td>10 Jan 2025</td>
                                <td>Natation, Aquafitness</td>
                                <td><span class="status-badge status-pending">En attente</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-view">Voir</button>
                                        <button class="btn-edit">Modifier</button>
                                        <button class="btn-delete">Supprimer</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Entraîneurs Section -->
            <div id="entraineurs" class="section">
                <div class="section-header">
                    <h2 class="section-title">Gestion des Entraîneurs</h2>
                    <button class="add-btn" onclick="openModal('entraineurModal')">
                        <span>+</span> Ajouter Entraîneur
                    </button>
                </div>

                <div class="search-bar">
                    <input type="text" class="search-input" placeholder="Rechercher un entraîneur...">
                    <select class="filter-select">
                        <option>Toutes les spécialités</option>
                        <option>Fitness</option>
                        <option>Yoga</option>
                        <option>Musculation</option>
                        <option>Natation</option>
                    </select>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Spécialités</th>
                                <th>Sessions/semaine</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Alexandre Petit</td>
                                <td>alex.petit@email.com</td>
                                <td>Fitness, Musculation</td>
                                <td>12</td>
                                <td><span class="status-badge status-active">Actif</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-view">Voir</button>
                                        <button class="btn-edit">Modifier</button>
                                        <button class="btn-delete">Supprimer</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Camille Bernard</td>
                                <td>camille.bernard@email.com</td>
                                <td>Yoga, Pilates</td>
                                <td>8</td>
                                <td><span class="status-badge status-active">Actif</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-view">Voir</button>
                                        <button class="btn-edit">Modifier</button>
                                        <button class="btn-delete">Supprimer</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Thomas Roux</td>
                                <td>thomas.roux@email.com</td>
                                <td>Natation</td>
                                <td>10</td>
                                <td><span class="status-badge status-inactive">Congé</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-view">Voir</button>
                                        <button class="btn-edit">Modifier</button>
                                        <button class="btn-delete">Supprimer</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Planning Section -->
            <div id="planning" class="section">
                <div class="section-header">
                    <h2 class="section-title">Planning des Activités</h2>
                    <button class="add-btn" onclick="openModal('sessionModal')">
                        <span>+</span> Nouvelle Session
                    </button>
                </div>

                <div class="calendar-grid">
                    <div class="calendar-day">
                        <div class="calendar-day-number">Lun 13</div>
                        <div class="calendar-event">9h00 - Yoga</div>
                        <div class="calendar-event">14h00 - Fitness</div>
                        <div class="calendar-event">18h00 - Musculation</div>
                    </div>
                    <div class="calendar-day">
                        <div class="calendar-day-number">Mar 14</div>
                        <div class="calendar-event">8h00 - Natation</div>
                        <div class="calendar-event">10h00 - Aquafitness</div>
                        <div class="calendar-event">17h00 - Pilates</div>
                    </div>
                    <div class="calendar-day">
                        <div class="calendar-day-number">Mer 15</div>
                        <div class="calendar-event">9h00 - Yoga</div>
                        <div class="calendar-event">15h00 - Fitness</div>
                    </div>
                    <div class="calendar-day">
                        <div class="calendar-day-number">Jeu 16</div>
                        <div class="calendar-event">7h00 - Running</div>
                        <div class="calendar-event">12h00 - Zumba</div>
                        <div class="calendar-event">19h00 - Musculation</div>
                    </div>
                    <div class="calendar-day">
                        <div class="calendar-day-number">Ven 17</div>
                        <div class="calendar-event">9h00 - Yoga</div>
                        <div class="calendar-event">16h00 - Fitness</div>
                    </div>
                    <div class="calendar-day">
                        <div class="calendar-day-number">Sam 18</div>
                        <div class="calendar-event">10h00 - Natation</div>
                        <div class="calendar-event">11h00 - Aquafitness</div>
                        <div class="calendar-event">14h00 - Tennis</div>