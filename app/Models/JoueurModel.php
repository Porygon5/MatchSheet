<?php
// Models/JoueurModel.php

namespace App\Models;

use App\Entities\Joueur;
use PDO;

class JoueurModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les joueurs.
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM joueurs");
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => new Joueur($row), $rows);
    }

    /**
     * Récupère tous les joueurs d’une équipe donnée.
     */
    public function getByEquipe(int $equipeId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM joueurs WHERE id_equipe = :id");
        $stmt->execute(['id' => $equipeId]);

        $rows = $stmt->fetchAll();
        return array_map(fn($row) => new Joueur($row), $rows);
    }

    /**
     * Récupère un joueur par son ID.
     */
    public function findById(int $id): ?Joueur
    {
        $stmt = $this->pdo->prepare("SELECT * FROM joueurs WHERE id_joueur = :id");
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        return $row ? new Joueur($row) : null;
    }
}
