<?php
// app/Models/ClubModel.php

namespace App\Models;

use App\Entities\Club;
use PDO;

require_once __DIR__ . '/../Entities/Club.php';

class ClubModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les clubs
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM clubs ORDER BY nom ASC");
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => new Club($row), $rows);
    }

    /**
     * Récupère un club par son ID
     */
    public function getById(int $id): ?Club
    {
        $stmt = $this->pdo->prepare("SELECT * FROM clubs WHERE id_club = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? new Club($row) : null;
    }

    /**
     * Crée un nouveau club
     */
    public function create(string $nom, string $ville, string $pays): int
    {
        $sql = "INSERT INTO clubs (nom, ville, pays) VALUES (:nom, :ville, :pays)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $nom,
            ':ville' => $ville,
            ':pays' => $pays
        ]);
        
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Met à jour un club existant
     */
    public function update(int $id, string $nom, string $ville, string $pays): bool
    {
        $sql = "UPDATE clubs SET nom = :nom, ville = :ville, pays = :pays WHERE id_club = :id";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            ':id' => $id,
            ':nom' => $nom,
            ':ville' => $ville,
            ':pays' => $pays
        ]);
    }

    /**
     * Supprime un club
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM clubs WHERE id_club = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Récupère le club d'une équipe par l'ID de l'équipe
     */
    public function getClubByTeamId(int $teamId): ?Club
    {
        $stmt = $this->pdo->prepare("
            SELECT c.* 
            FROM clubs c
            INNER JOIN equipes e ON c.id_club = e.id_club
            WHERE e.id_equipe = :team_id
        ");
        $stmt->execute(['team_id' => $teamId]);
        $row = $stmt->fetch();

        return $row ? new Club($row) : null;
    }
}