<?php
// models/Entraineur.php

class Entraineur {
  
    private $id;
    private $nom;
    private $prenom;
    private $email;
    private $motDePasse;
    private $dateInscription;
    private $isActif;

    public function __construct($data = []) {
        if (is_array($data) && !empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->nom = $data['nom'] ?? '';
            $this->prenom = $data['prenom'] ?? '';
            $this->email = $data['email'] ?? '';
            $this->motDePasse = $data['mot_de_passe'] ?? '';
            $this->dateInscription = $data['date_inscription'] ?? null;
            $this->isActif = isset($data['is_actif']) ? (bool)$data['is_actif'] : true;
        }
    }

    // ================== GETTERS ==================
       public function getId() {
        return $this->id;
    }

    public function getNom() {
        return $this->nom;
    }

    public function getPrenom() {
        return $this->prenom;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getMotDePasse() {
        return $this->motDePasse;
    }

    public function getDateInscription() {
        return $this->dateInscription;
    }

    public function getIsActif() {
        return $this->isActif;
    }

    public function getInitiales() {
        $nom = strtoupper(substr($this->nom, 0, 1));
        $prenom = strtoupper(substr($this->prenom, 0, 1));
        return $prenom . $nom;
    }

    public function getNomComplet() {
        return $this->prenom . ' ' . $this->nom;
    }

    // ================== SETTERS ==================
    public function setId($id) {
        $this->id = $id;
    }

    public function setNom($nom) {
        $this->nom = trim($nom);
    }

    public function setPrenom($prenom) {
        $this->prenom = trim($prenom);
    }

    public function setEmail($email) {
        $this->email = trim(strtolower($email));
    }

    public function setMotDePasse($motDePasse) {
        $this->motDePasse = $motDePasse;
    }

    public function setDateInscription($dateInscription) {
        $this->dateInscription = $dateInscription;
    }

    public function setIsActif($isActif) {
        $this->isActif = (bool)$isActif;
    }

    // ================== VALIDATION ==================
    public function isValid() {
        return !empty($this->nom) && 
               !empty($this->prenom) && 
               !empty($this->email) && 
               filter_var($this->email, FILTER_VALIDATE_EMAIL) &&
               (!empty($this->motDePasse) || $this->id); // Mot de passe requis seulement pour création
    }

    // ================== SERIALIZATION ==================
    public function toArray() {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'date_inscription' => $this->dateInscription,
            'is_actif' => $this->isActif ? 1 : 0,
            'initiales' => $this->getInitiales(),
            'nom_complet' => $this->getNomComplet()
        ];
    }

    public function toJson() {
        return json_encode($this->toArray());
    }

    // ================== UTILITAIRES ==================
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function sanitizeName($name) {
        return trim(ucwords(strtolower($name)));
    }

    public static function generatePassword($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
        return substr(str_shuffle($chars), 0, $length);
    }
}