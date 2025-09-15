<?php
// Models/EquipeModel.php

namespace App\Models;

use App\Entities\Equipe;

require_once __DIR__ . '/../Entities/Equipe.php';

class EquipeModel
{
    /**
     * @var PDO Connexion à la base de données
     */
    private $pdo;

    /**
     * Constructeur de la classe
     * 
     * @param PDO $db Instance de la connexion PDO
     */
    public function __construct($db)
    {
        $this->pdo = $db;
    }

    /**
     * Récupère toutes les équipes
     * 
     * @return Equipe[] Liste des équipes
     */
    public function getAll()
    {
        $sql = "SELECT * FROM equipes";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $equipes = [];
        foreach ($rows as $row) {
            $equipes[] = new Equipe(
                $row['id_equipe'],
                $row['nom'],
                $row['domicile'],
                $row['id_club'],
                $row['id_entraineur'],
            );
        }

        return $equipes;
    }

    /**
     * Récupère une équipe par son ID
     * 
     * @param int $id ID de l'équipe
     * @return Equipe|null L'équipe trouvée ou null si non trouvée
     */
    public function getById(int $id): ?Equipe
    {
        $sql = "SELECT * FROM equipes WHERE id_equipe = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new Equipe(
            $row['id_equipe'],
            $row['nom'],
            $row['domicile'],
            $row['id_club'],
            $row['id_entraineur'],
        );
    }

    /**
     * Crée une nouvelle équipe
     */
    public function create(string $nom, bool $domicile, int $idClub, ?int $idEntraineur = null): int
    {
        $sql = "INSERT INTO equipes (nom, domicile, id_club, id_entraineur) VALUES (:nom, :domicile, :id_club, :id_entraineur)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $nom,
            ':domicile' => $domicile ? 1 : 0,
            ':id_club' => $idClub,
            ':id_entraineur' => $idEntraineur
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Met à jour une équipe existante
     */
    public function update(int $id, string $nom, bool $domicile, int $idClub, ?int $idEntraineur = null): bool
    {
        $sql = "UPDATE equipes SET nom = :nom, domicile = :domicile, id_club = :id_club, id_entraineur = :id_entraineur WHERE id_equipe = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':nom' => $nom,
            ':domicile' => $domicile ? 1 : 0,
            ':id_club' => $idClub,
            ':id_entraineur' => $idEntraineur
        ]);
    }

    /**
     * Supprime une équipe
     */
    public function delete(int $id): bool
    {
        // Supprimer d'abord les joueurs de l'équipe
        $stmtJoueurs = $this->pdo->prepare("DELETE FROM joueurs WHERE id_equipe = :id");
        $stmtJoueurs->execute([':id' => $id]);

        // Puis supprimer l'équipe
        $stmt = $this->pdo->prepare("DELETE FROM equipes WHERE id_equipe = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Trouve l'équipe d'un entraîneur par son ID utilisateur
     */
    public function findTeamByCoachId(int $coachId): ?int
    {
        $stmt = $this->pdo->prepare("SELECT id_equipe FROM equipes WHERE id_entraineur = :coach_id LIMIT 1");
        $stmt->execute([':coach_id' => $coachId]);
        $result = $stmt->fetchColumn();

        return $result ? (int)$result : null;
    }
}
