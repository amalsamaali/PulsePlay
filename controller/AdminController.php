<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/AdminModel.php';

class AdminController {
    private $model;
    public function __construct($pdo) {
        $this->model = new AdminModel($pdo);
    }
    public function afficherDashboard() {
        $utilisateurs = $this->model->getUtilisateursEnAttente();
        require __DIR__ . '/../view/admin/dashboard.php';
    }
}
