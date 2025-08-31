<?php

class ActiviteSportive
{
    private ?int $id;
    private string $nom;
    private string $description;
    private int $entraineur_id;
    private ?int $plannings_count;
    private ?string $nom_entraineur; // Added for display purposes

    public function __construct(array $data = [])
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->nom = $data['nom'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->entraineur_id = isset($data['entraineur_id']) ? (int)$data['entraineur_id'] : 0;
        $this->plannings_count = isset($data['plannings_count']) ? (int)$data['plannings_count'] : null;
        $this->nom_entraineur = $data['nom_entraineur'] ?? null; // Added for display purposes
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getEntraineurId(): int
    {
        return $this->entraineur_id;
    }

    public function getPlanningsCount(): ?int
    {
        return $this->plannings_count;
    }

    public function getNomEntraineur(): ?string
    {
        return $this->nom_entraineur;
    }

    // Setters
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setEntraineurId(int $entraineur_id): void
    {
        $this->entraineur_id = $entraineur_id;
    }

    public function setPlanningsCount(?int $plannings_count): void
    {
        $this->plannings_count = $plannings_count;
    }

    public function setNomEntraineur(?string $nom_entraineur): void
    {
        $this->nom_entraineur = $nom_entraineur;
    }

    // Validation
    public function validate(): array
    {
        $errors = [];

        if (empty($this->nom)) {
            $errors[] = "Le nom de l'activité est requis";
        }

        if (empty($this->description)) {
            $errors[] = "La description est requise";
        }

        if ($this->entraineur_id <= 0) {
            $errors[] = "Un entraîneur doit être sélectionné";
        }

        return $errors;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'description' => $this->description,
            'entraineur_id' => $this->entraineur_id,
            'plannings_count' => $this->plannings_count,
            'nom_entraineur' => $this->nom_entraineur
        ];
    }

    // Méthodes utilitaires
    public function getNomCourt(): string
    {
        return strlen($this->nom) > 30 ? substr($this->nom, 0, 30) . '...' : $this->nom;
    }

    public function getDescriptionCourte(): string
    {
        return strlen($this->description) > 100 ? substr($this->description, 0, 100) . '...' : $this->description;
    }
}
