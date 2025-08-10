<?php
class Utilisateur {
    private ?int $id;
    private string $nom;
    private string $prenom;
    private string $email;
    private string $motDePasse;
    private ?string $dateInscription;
    private string $role;
    private bool $isActif;

    public function __construct(
        string $nom,
        string $prenom,
        string $email,
        string $motDePasse,
        string $role = 'adherent',
        bool $isActif = false,
        ?string $dateInscription = null,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->motDePasse = $motDePasse;
        $this->role = $role;
        $this->isActif = $isActif;
        $this->dateInscription = $dateInscription;
    }

    // Getters et setters
    public function getId(): ?int {
        return $this->id;
    }
    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getNom(): string {
        return $this->nom;
    }
    public function setNom(string $nom): void {
        $this->nom = $nom;
    }

    public function getPrenom(): string {
        return $this->prenom;
    }
    public function setPrenom(string $prenom): void {
        $this->prenom = $prenom;
    }

    public function getEmail(): string {
        return $this->email;
    }
    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function getMotDePasse(): string {
        return $this->motDePasse;
    }
    public function setMotDePasse(string $motDePasse): void {
        $this->motDePasse = $motDePasse;
    }

    public function getDateInscription(): ?string {
        return $this->dateInscription;
    }
    public function setDateInscription(?string $dateInscription): void {
        $this->dateInscription = $dateInscription;
    }

    public function getRole(): string {
        return $this->role;
    }
    public function setRole(string $role): void {
        $this->role = $role;
    }

    public function getIsActif(): bool {
        return $this->isActif;
    }
    public function setIsActif(bool $isActif): void {
        $this->isActif = $isActif;
    }
}
?>
