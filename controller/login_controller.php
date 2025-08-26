<?php
session_start();
require_once '../models/UtilisateurModel.php';
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = trim($_POST['mot_de_passe'] ?? '');

    // Vérif champs
    if (empty($email) || empty($mot_de_passe)) {
        $_SESSION['error'] = "Tous les champs sont obligatoires.";
        header('Location: ../view/front/login.php');
        exit;
    }

    $utilisateurModel = new UtilisateurModel();
    $user = $utilisateurModel->getUserByEmail($email);

    if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
        // Stocker l'utilisateur dans la session
        $_SESSION['user'] = [
            'id'      => $user['id'],
            'nom'     => $user['nom'],
            'prenom'  => $user['prenom'],
            'email'   => $user['email'],
            'role'    => $user['role'],     // ⚠ Doit être "admin" dans la BDD pour l'admin
            'is_actif'=> $user['is_actif']
        ];

        // Rediriger selon le rôle
        if ($user['role'] === 'admin') {
            header('Location: ../view/back/dashbord.php');
        } else {
            header('Location: ../view/front/index.php');
        }
        exit;
    } else {
        $_SESSION['error'] = "Email ou mot de passe incorrect.";
        header('Location: ../view/front/login.php');
        exit;
    }
}
