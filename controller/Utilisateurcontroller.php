<?php
require_once '../models/UtilisateurModel.php';

class UtilisateurController {

    public function login() {
        // Vérification POST
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $model = new UtilisateurModel();
        $user = $model->getUserByEmail($email);

        header('Content-Type: application/json');

        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user'] = $user;

            // Redirection selon rôle
            $redirect = match($user['role']) {
                'admin' => '/PulsePlay/admin/dashboard.php',
                'entraineur' => '/PulsePlay/entraineur/planning.php',
                'adherent' => '/PulsePlay/adherent/activites.php',
                default => '/PulsePlay/index.php'
            };

            echo json_encode(['success' => true, 'redirect' => $redirect]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
        }
    }

    public function signup() {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mot_de_passe = $_POST['mot_de_passe'] ?? '';
        $confirm = $_POST['confirm_mot_de_passe'] ?? '';
        $role = isset($_POST['role']) && in_array($_POST['role'], ['adherent','entraineur']) ? $_POST['role'] : 'adherent';
        $is_actif = 0;

        header('Content-Type: application/json');

        if (!$nom || !$prenom || !$email || !$mot_de_passe || !$confirm) {
            echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires.']);
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email invalide.']);
            return;
        }
        if ($mot_de_passe !== $confirm) {
            echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas.']);
            return;
        }
        if (strlen($mot_de_passe) < 6) {
            echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères.']);
            return;
        }

        $model = new UtilisateurModel();
        if ($model->getUserByEmail($email)) {
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé.']);
            return;
        }

        $hashed = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        $result = $model->createUser($nom, $prenom, $email, $hashed, $role, $is_actif);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Inscription réussie ! Vous pouvez vous connecter.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription.']);
        }
    }
}

$controller = new UtilisateurController();
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'login') {
        $controller->login();
    } elseif ($_GET['action'] === 'signup') {
        $controller->signup();
    }
}
