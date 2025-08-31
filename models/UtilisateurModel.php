<?php
require_once __DIR__ . '/../config.php';

class UtilisateurModel {
    private $id;
    private $nom;
    private $prenom;
    private $email;
    private $mot_de_passe;
    private $role;
    private $is_actif;

    public function __construct($id = null, $nom = null, $prenom = null, $email = null, $mot_de_passe = null, $role = 'adherent', $is_actif = 0) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->mot_de_passe = $mot_de_passe;
        $this->role = $role;
        $this->is_actif = $is_actif;
    }

    // --- Getters ---
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getPrenom() { return $this->prenom; }
    public function getEmail() { return $this->email; }
    public function getMotDePasse() { return $this->mot_de_passe; }
    public function getRole() { return $this->role; }
    public function getIsActif() { return $this->is_actif; }

    // --- Setters ---
    public function setId($id) { $this->id = $id; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setPrenom($prenom) { $this->prenom = $prenom; }
    public function setEmail($email) { $this->email = $email; }
    public function setMotDePasse($mot_de_passe) { $this->mot_de_passe = $mot_de_passe; }
    public function setRole($role) { $this->role = $role; }
    public function setIsActif($is_actif) { $this->is_actif = $is_actif; }

    // --- CRUD ---
    public function getUserByEmail($email) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser() {
        $db = Database::connect();
        $stmt = $db->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, is_actif) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $this->nom,
            $this->prenom,
            $this->email,
            $this->mot_de_passe,
            $this->role,
            $this->is_actif
        ]);
    }

    public function getUserById($id) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateUser($data) {
        $db = Database::connect();
        
        // Construire la requête SQL dynamiquement
        $sql = "UPDATE utilisateurs SET nom = ?, prenom = ?, email = ?";
        $params = [$data['nom'], $data['prenom'], $data['email']];
        
        // Ajouter le mot de passe si fourni
        if (isset($data['mot_de_passe'])) {
            $sql .= ", mot_de_passe = ?";
            $params[] = $data['mot_de_passe'];
        }
        
        // Ajouter les autres champs si fournis
        if (isset($data['role'])) {
            $sql .= ", role = ?";
            $params[] = $data['role'];
        }
        if (isset($data['is_actif'])) {
            $sql .= ", is_actif = ?";
            $params[] = $data['is_actif'];
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $data['id'];
        
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }
}
