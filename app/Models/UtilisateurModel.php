<?php
// Models/UtilisateurModel.php

namespace App\Models;

use PDO;
use App\Entities\Utilisateur;

class UtilisateurModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère un utilisateur par son ID (id_utilisateur)
     */
    public function findById(int $id): ?Utilisateur
    {
        $sql = "SELECT id_utilisateur, nom_utilisateur, mot_de_passe, id_permission
                FROM utilisateurs
                WHERE id_utilisateur = :id
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Utilisateur::fromRow($row) : null;
    }

    /**
     * Récupère un utilisateur par son nom d'utilisateur (login)
     */
    public function findByNomUtilisateur(string $nomUtilisateur): ?Utilisateur
    {
        $sql = "SELECT id_utilisateur, nom_utilisateur, mot_de_passe, id_permission
                FROM utilisateurs
                WHERE nom_utilisateur = :nom
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['nom' => $nomUtilisateur]);
        $row = $stmt->fetch();

        return $row ? Utilisateur::fromRow($row) : null;
    }

    /**
     * Crée un utilisateur avec un mot de passe hashé.
     */
    public function create(string $nomUtilisateur, string $motDePasseClair, int $idPermission = 1): int
    {
        $hash = password_hash($motDePasseClair, PASSWORD_DEFAULT);

        $sql = "INSERT INTO utilisateurs (nom_utilisateur, mot_de_passe, id_permission)
                VALUES (:nom, :hash, :perm)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'nom'  => $nomUtilisateur,
            'hash' => $hash,
            'perm' => $idPermission,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Hash et met à jour le mot de passe.
     */
    public function updatePassword(int $id, string $nouveauMotDePasse): bool
    {
        $hash = password_hash($nouveauMotDePasse, PASSWORD_DEFAULT);

        $sql = "UPDATE utilisateurs
                SET mot_de_passe = :hash
                WHERE id_utilisateur = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['hash' => $hash, 'id' => $id]);
    }
}
