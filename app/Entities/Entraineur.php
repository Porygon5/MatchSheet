<?php
// app/Entities/Entraineur.php

namespace App\Entities;

class Entraineur
{
    public int $id;
    public string $nom;
    public string $nationalite;
    public bool $entraineurAdjoint;
    public int $idUtilisateur;

    public function __construct(array $data)
    {
        $this->id = $data['id_entraineur'] ?? 0;
        $this->nom = $data['nom'] ?? '';
        $this->nationalite = $data['nationalite'] ?? '';
        $this->entraineurAdjoint = (bool)($data['entraineur_adjoint'] ?? false);
        $this->idUtilisateur = $data['id_utilisateur'] ?? 0;
    }

    /**
     * Vérifie si l'entraîneur est un adjoint
     */
    public function isAdjoint(): bool
    {
        return $this->entraineurAdjoint;
    }

    /**
     * Vérifie si l'entraîneur est un entraîneur principal
     */
    public function isPrincipal(): bool
    {
        return !$this->entraineurAdjoint;
    }

    /**
     * Retourne le type d'entraîneur sous forme de chaîne
     */
    public function getType(): string
    {
        return $this->entraineurAdjoint ? 'Adjoint' : 'Principal';
    }

    /**
     * Retourne le nom complet avec le type
     */
    public function getNomComplet(): string
    {
        return $this->nom . ' (' . $this->getType() . ')';
    }
}