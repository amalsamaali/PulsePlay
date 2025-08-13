<?php
require_once '../models/UtilisateurModel.php';
require_once '../config.php';

class AdminController {

    public function createAdmin() {
        // Vérifier si un administrateur existe déjà
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE role = 'admin'");
        $stmt->execute();
        $adminCount = $stmt->fetchColumn();

        $message = '';
        $success = false;

        if ($adminCount > 0) {
            $message = "Un administrateur existe déjà dans la base de données.";
        } else {
            // Créer un administrateur par défaut
            $nom = 'Admin';
            $prenom = 'Super';
            $email = 'admin@pulseplay.com';
            $mot_de_passe = password_hash('admin123', PASSWORD_DEFAULT);
            $role = 'admin';
            $is_actif = 1;

            $admin = new UtilisateurModel(null, $nom, $prenom, $email, $mot_de_passe, $role, $is_actif);
            $result = $admin->createUser();

            if ($result) {
                $message = "Administrateur créé avec succès!";
                $success = true;
            } else {
                $message = "Erreur lors de la création de l'administrateur.";
            }
        }

        // Rediriger vers le tableau de bord admin
        header('Location: /PulsePlay/view/front/dashbeordAdmin.php');
        exit;
    }
}

// Route simple
$controller = new AdminController();
if(isset($_GET['action'])) {
    match($_GET['action']) {
        'create' => $controller->createAdmin(),
        default => null
    };
} else {
    // Action par défaut
    $controller->createAdmin();
}