<?php
// Entities/Utilisateur.php

namespace App\Entities;

final class Utilisateur
{
    private ?int $id; // null si pas encore créé
    private string $nom_utilisateur;
    private string $mot_de_passe_hash;
    private int $id_permission;

    private function __construct(
        ?int $id,
        string $nom_utilisateur,
        string $mot_de_passe_hash,
        int $id_permission
    ) {
        $this->id = $id;
        $this->nom_utilisateur = $nom_utilisateur;
        $this->mot_de_passe_hash = $mot_de_passe_hash;
        $this->id_permission = $id_permission;
    }

    public static function fromRow(array $row): self
    {
        return new self(
            isset($row['id_utilisateur']) ? (int)$row['id_utilisateur'] : null,
            $row['nom_utilisateur'],
            $row['mot_de_passe'],
            (int)$row['id_permission']
        );
    }

    // Vérification du mot de passe
    public function verifierMotDePasse(string $motDePasseClair): bool
    {
        return password_verify($motDePasseClair, $this->mot_de_passe_hash);
    }

    // Changement de mot de passe
    public function withNouveauMotDePasse(string $motDePasseClair): self
    {
        $clone = clone $this;
        $clone->mot_de_passe_hash = password_hash($motDePasseClair, PASSWORD_DEFAULT);
        return $clone;
    }

    // Getters
    public function id(): ?int { return $this->id; }
    public function nomUtilisateur(): string { return $this->nom_utilisateur; }
    public function idPermission(): int { return $this->id_permission; }
}