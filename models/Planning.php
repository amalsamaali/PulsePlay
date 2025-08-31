<?php
/**
 * Modèle Planning
 * Représente un planning d'activité sportive selon la structure de base de données
 */
class Planning {
    private $id;
    private $activite_id;
    private $jour_semaine;
    private $heure_debut;
    private $heure_fin;
    private $salle;
    
    // Propriétés additionnelles pour les jointures
    private $nom_activite;
    private $nom_entraineur;
    private $description_activite;
    private $entraineur_id;

    public function __construct($data = []) {
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    /**
     * Hydrate l'objet avec des données
     */
    public function hydrate($data) {
        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($this, $method)) {
                $this->$method($value);
            } else {
                // Pour les propriétés avec underscores
                $property = $key;
                if (property_exists($this, $property)) {
                    $this->$property = $value;
                }
            }
        }
    }

    // ================== GETTERS ==================
    
    public function getId() {
        return $this->id;
    }

    public function getActiviteId() {
        return $this->activite_id;
    }

    public function getJourSemaine() {
        return $this->jour_semaine;
    }

    public function getHeureDebut() {
        return $this->heure_debut;
    }

    public function getHeureFin() {
        return $this->heure_fin;
    }

    public function getSalle() {
        return $this->salle;
    }

    public function getNomActivite() {
        return $this->nom_activite;
    }

    public function getNomEntraineur() {
        return $this->nom_entraineur;
    }

    public function getDescriptionActivite() {
        return $this->description_activite;
    }

    public function getEntraineurId() {
        return $this->entraineur_id;
    }

    // ================== SETTERS ==================
    
    public function setId($id) {
        $this->id = (int)$id;
    }

    public function setActiviteId($activite_id) {
        $this->activite_id = (int)$activite_id;
    }

    public function setJourSemaine($jour_semaine) {
        $this->jour_semaine = $jour_semaine;
    }

    public function setHeureDebut($heure_debut) {
        $this->heure_debut = $heure_debut;
    }

    public function setHeureFin($heure_fin) {
        $this->heure_fin = $heure_fin;
    }

    public function setSalle($salle) {
        $this->salle = $salle;
    }

    public function setNomActivite($nom_activite) {
        $this->nom_activite = $nom_activite;
    }

    public function setNomEntraineur($nom_entraineur) {
        $this->nom_entraineur = $nom_entraineur;
    }

    public function setDescriptionActivite($description_activite) {
        $this->description_activite = $description_activite;
    }

    public function setEntraineurId($entraineur_id) {
        $this->entraineur_id = (int)$entraineur_id;
    }

    // ================== MÉTHODES UTILITAIRES ==================
    
    /**
     * Retourne le créneau horaire formaté
     */
    public function getCreneauFormate() {
        if ($this->heure_debut && $this->heure_fin) {
            return date('H:i', strtotime($this->heure_debut)) . ' - ' . date('H:i', strtotime($this->heure_fin));
        }
        return '';
    }

    /**
     * Calcule la durée de la séance
     */
    public function getDuree() {
        if ($this->heure_debut && $this->heure_fin) {
            $debut = new DateTime($this->heure_debut);
            $fin = new DateTime($this->heure_fin);
            $duree = $debut->diff($fin);
            
            $heures = $duree->h;
            $minutes = $duree->i;
            
            if ($heures > 0) {
                return $heures . 'h' . ($minutes > 0 ? sprintf('%02d', $minutes) : '');
            } else {
                return $minutes . ' min';
            }
        }
        return '';
    }

    /**
     * Retourne la durée en minutes
     */
    public function getDureeMinutes() {
        if ($this->heure_debut && $this->heure_fin) {
            $debut = strtotime($this->heure_debut);
            $fin = strtotime($this->heure_fin);
            return ($fin - $debut) / 60;
        }
        return 0;
    }

    /**
     * Vérifie si le planning est valide
     */
    public function isValid() {
        return !empty($this->activite_id) && 
               !empty($this->jour_semaine) && 
               !empty($this->heure_debut) && 
               !empty($this->heure_fin) && 
               !empty($this->salle);
    }

    /**
     * Vérifie s'il y a conflit d'horaire
     */
    public function hasTimeConflict($autre_debut, $autre_fin) {
        if (!$this->heure_debut || !$this->heure_fin) {
            return false;
        }
        
        $debut1 = strtotime($this->heure_debut);
        $fin1 = strtotime($this->heure_fin);
        $debut2 = strtotime($autre_debut);
        $fin2 = strtotime($autre_fin);
        
        return ($debut1 < $fin2) && ($debut2 < $fin1);
    }

    /**
     * Retourne les données sous forme de tableau
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'activite_id' => $this->activite_id,
            'jour_semaine' => $this->jour_semaine,
            'heure_debut' => $this->heure_debut,
            'heure_fin' => $this->heure_fin,
            'salle' => $this->salle,
            'nom_activite' => $this->nom_activite,
            'nom_entraineur' => $this->nom_entraineur,
            'description_activite' => $this->description_activite,
            'entraineur_id' => $this->entraineur_id,
            'creneau_formatted' => $this->getCreneauFormate(),
            'duree' => $this->getDuree(),
            'duree_minutes' => $this->getDureeMinutes()
        ];
    }

    /**
     * Validation complète des données
     */
    public function validate() {
        $errors = [];

        // Validation activité
        if (empty($this->activite_id)) {
            $errors['activite_id'] = 'L\'activité est obligatoire';
        } elseif (!is_numeric($this->activite_id) || $this->activite_id <= 0) {
            $errors['activite_id'] = 'ID d\'activité invalide';
        }

        // Validation jour de la semaine
        $jours_valides = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        if (empty($this->jour_semaine)) {
            $errors['jour_semaine'] = 'Le jour de la semaine est obligatoire';
        } elseif (!in_array($this->jour_semaine, $jours_valides)) {
            $errors['jour_semaine'] = 'Jour de la semaine invalide';
        }

        // Validation heures
        if (empty($this->heure_debut)) {
            $errors['heure_debut'] = 'L\'heure de début est obligatoire';
        } elseif (!$this->isValidTimeFormat($this->heure_debut)) {
            $errors['heure_debut'] = 'Format d\'heure invalide (utilisez HH:MM)';
        }

        if (empty($this->heure_fin)) {
            $errors['heure_fin'] = 'L\'heure de fin est obligatoire';
        } elseif (!$this->isValidTimeFormat($this->heure_fin)) {
            $errors['heure_fin'] = 'Format d\'heure invalide (utilisez HH:MM)';
        }

        // Validation cohérence des heures
        if (!empty($this->heure_debut) && !empty($this->heure_fin)) {
            $debut = strtotime($this->heure_debut);
            $fin = strtotime($this->heure_fin);
            
            if ($debut === false || $fin === false) {
                $errors['heure'] = 'Format d\'heure invalide';
            } elseif ($debut >= $fin) {
                $errors['heure_fin'] = 'L\'heure de fin doit être postérieure à l\'heure de début';
            } else {
                $duree_minutes = ($fin - $debut) / 60;
                if ($duree_minutes < 30) {
                    $errors['heure_fin'] = 'La durée minimale est de 30 minutes';
                }
                
                if ($duree_minutes > 480) { // 8 heures
                    $errors['heure_fin'] = 'La durée maximale est de 8 heures';
                }
            }
        }

        // Validation salle
        if (empty($this->salle)) {
            $errors['salle'] = 'La salle est obligatoire';
        } elseif (strlen(trim($this->salle)) < 2) {
            $errors['salle'] = 'Le nom de la salle doit contenir au moins 2 caractères';
        } elseif (strlen(trim($this->salle)) > 50) {
            $errors['salle'] = 'Le nom de la salle ne peut pas dépasser 50 caractères';
        }

        return $errors;
    }

    /**
     * Valide le format d'une heure (HH:MM ou HH:MM:SS)
     */
    private function isValidTimeFormat($time) {
        $patterns = [
            '/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/',           // HH:MM
            '/^([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/' // HH:MM:SS
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $time)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Formate les heures pour l'affichage
     */
    public function formatHeures() {
        if ($this->heure_debut && $this->heure_fin) {
            return [
                'debut_formatted' => date('H:i', strtotime($this->heure_debut)),
                'fin_formatted' => date('H:i', strtotime($this->heure_fin)),
                'creneau_formatted' => $this->getCreneauFormate(),
                'duree' => $this->getDuree(),
                'duree_minutes' => $this->getDureeMinutes()
            ];
        }
        return null;
    }

    /**
     * Retourne une représentation textuelle du planning
     */
    public function __toString() {
        $activite = $this->nom_activite ?: "Activité #{$this->activite_id}";
        $entraineur = $this->nom_entraineur ?: "Entraîneur inconnu";
        $creneau = $this->getCreneauFormate();
        
        return "{$activite} - {$this->jour_semaine} {$creneau} - Salle {$this->salle} - {$entraineur}";
    }

    /**
     * Vérifie si le planning est complet (toutes les données liées sont présentes)
     */
    public function isComplete() {
        return $this->isValid() && 
               !empty($this->nom_activite) && 
               !empty($this->nom_entraineur);
    }
}