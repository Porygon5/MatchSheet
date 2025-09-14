<?php
// Models/JoueurModel.php

namespace App\Models;

use App\Entities\Joueur;
use PDO;

require_once __DIR__ . '/../Entities/Joueur.php';

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

    /**
     * Transfère un joueur d'une équipe à une autre.
     */
    public function transfer(int $idJoueur, int $idEquipeCible): void
    {
        // Trouver l'équipe source actuelle du joueur
        $stmt = $this->pdo->prepare("SELECT id_equipe FROM joueurs WHERE id_joueur = :id_joueur");
        $stmt->execute([':id_joueur' => $idJoueur]);
        $idEquipeSource = $stmt->fetchColumn();

        if (!$idEquipeSource) {
            // Joueur non trouvé, rien à faire
            return;
        }

        // Met à jour l'équipe du joueur dans la table des joueurs
        $stmt = $this->pdo->prepare("
            UPDATE joueurs
            SET id_equipe = :id_equipe_cible
            WHERE id_joueur = :id_joueur
        ");
        $stmt->execute([
            ':id_joueur' => $idJoueur,
            ':id_equipe_cible' => $idEquipeCible
        ]);
    }
}
