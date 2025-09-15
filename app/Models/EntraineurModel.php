<?php
// app/Models/EntraineurModel.php

namespace App\Models;

use App\Entities\Entraineur;
use PDO;

require_once __DIR__ . '/../Entities/Entraineur.php';

class EntraineurModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les entraîneurs
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM entraineurs ORDER BY nom ASC");
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => new Entraineur($row), $rows);
    }

    /**
     * Récupère un entraîneur par son ID
     */
    public function findById(int $id): ?Entraineur
    {
        $stmt = $this->pdo->prepare("SELECT * FROM entraineurs WHERE id_entraineur = :id");
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        return $row ? new Entraineur($row) : null;
    }

    /**
     * Récupère un entraîneur par l'ID de l'utilisateur
     */
    public function findByUserId(int $userId): ?Entraineur
    {
        $stmt = $this->pdo->prepare("SELECT * FROM entraineurs WHERE id_utilisateur = :userId");
        $stmt->execute(['userId' => $userId]);

        $row = $stmt->fetch();
        return $row ? new Entraineur($row) : null;
    }

    /**
     * Récupère les entraîneurs principaux seulement
     */
    public function getPrincipaux(): array
    {
        $stmt = $this->pdo->query("
            SELECT * FROM entraineurs 
            WHERE entraineur_adjoint = 0 
            ORDER BY nom ASC
        ");
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => new Entraineur($row), $rows);
    }

    /**
     * Récupère les entraîneurs adjoints seulement
     */
    public function getAdjoints(): array
    {
        $stmt = $this->pdo->query("
            SELECT * FROM entraineurs 
            WHERE entraineur_adjoint = 1 
            ORDER BY nom ASC
        ");
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => new Entraineur($row), $rows);
    }

    /**
     * Crée un nouvel entraîneur
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO entraineurs (nom, nationalite, entraineur_adjoint, id_utilisateur)
            VALUES (:nom, :nationalite, :entraineur_adjoint, :id_utilisateur)
        ");

        $stmt->execute([
            'nom' => $data['nom'],
            'nationalite' => $data['nationalite'],
            'entraineur_adjoint' => (int)($data['entraineur_adjoint'] ?? 0),
            'id_utilisateur' => $data['id_utilisateur']
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Met à jour un entraîneur
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE entraineurs 
            SET nom = :nom, 
                nationalite = :nationalite, 
                entraineur_adjoint = :entraineur_adjoint, 
                id_utilisateur = :id_utilisateur
            WHERE id_entraineur = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'nom' => $data['nom'],
            'nationalite' => $data['nationalite'],
            'entraineur_adjoint' => (int)($data['entraineur_adjoint'] ?? 0),
            'id_utilisateur' => $data['id_utilisateur']
        ]);
    }

    /**
     * Supprime un entraîneur
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM entraineurs WHERE id_entraineur = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Vérifie si un utilisateur est déjà associé à un entraîneur
     */
    public function isUserAssigned(int $userId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM entraineurs WHERE id_utilisateur = :userId
        ");
        $stmt->execute(['userId' => $userId]);
        
        return (int)$stmt->fetchColumn() > 0;
    }
}