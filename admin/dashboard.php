<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /PulsePlay/view/front/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - PulsePlay</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6fa; margin: 0; }
        .dashboard-container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0001; padding: 2rem; }
        h1 { color: #16213e; }
        .quick-links { margin: 2rem 0; display: flex; gap: 2rem; }
        .quick-link { background: #00b4d8; color: #fff; padding: 1.5rem 2rem; border-radius: 8px; text-decoration: none; font-weight: bold; transition: background 0.2s; }
        .quick-link:hover { background: #00d4aa; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Bienvenue sur le Dashboard Admin</h1>
        <div class="quick-links">
            <a class="quick-link" href="#">Gérer les utilisateurs</a>
            <a class="quick-link" href="#">Gérer les activités</a>
            <a class="quick-link" href="#">Voir les inscriptions</a>
        </div>
        <p>Utilisez les liens ci-dessus pour accéder aux modules de gestion.</p>
    </div>
</body>
</html>
