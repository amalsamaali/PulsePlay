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
        table { width: 100%; border-collapse: collapse; margin-top: 2rem; }
        th, td { padding: 0.7rem 1rem; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #00b4d8; color: #fff; }
        tr:nth-child(even) { background: #f8fafd; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Bienvenue sur le Dashboard Admin</h1>
        <h2>Utilisateurs en attente de validation</h2>
        <table>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Action</th>
            </tr>
            <?php if (!empty($utilisateurs)) : foreach ($utilisateurs as $u) : ?>
            <tr>
                <td><?= htmlspecialchars($u['nom']) ?></td>
                <td><?= htmlspecialchars($u['prenom']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td>
                    <form method="post" action="/PulsePlay/controller/AdminController.php?action=valider" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button type="submit">Accepter</button>
                    </form>
                    <form method="post" action="/PulsePlay/controller/AdminController.php?action=refuser" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button type="submit">Refuser</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="5">Aucun utilisateur en attente.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>
