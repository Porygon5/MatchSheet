<?php
// Entities/Joueur.php

namespace App\Entities;

class Joueur
{
    public int $id;
    public string $nom;
    public string $prenom;
    public string $nationalite;
    public int $numero;
    public int $posteId;
    public int $placementId;
    public int $equipeId;

    public function __construct(array $data)
    {
        $this->id = $data['id_joueur'];
        $this->nom = $data['nom'];
        $this->prenom = $data['prenom'];
        $this->nationalite = $data['nationalite'];
        $this->numero = $data['numero'];
        $this->posteId = $data['id_poste_predilection'];
        $this->placementId = $data['id_placement_predilection'];
        $this->equipeId = $data['id_equipe'];
    }

    public function getNomComplet(): string
    {
        return "{$this->prenom} {$this->nom}";
    }
}
