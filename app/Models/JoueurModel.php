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
        $stmt = $this->pdo->query("SELECT * FROM joueurs ORDER BY numero");
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => new Joueur($row), $rows);
    }

    /**
     * Récupère tous les joueurs d'une équipe donnée.
     */
    public function getByEquipe(int $equipeId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM joueurs WHERE id_equipe = :id ORDER BY numero");
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
     * Crée un nouveau joueur
     */
    public function create(string $nom, string $prenom, string $nationalite, int $numero, int $idPoste, int $idPlacement, int $idEquipe): int
    {
        // Vérifier que le numéro n'est pas déjà pris dans l'équipe
        if ($this->isNumeroTaken($numero, $idEquipe)) {
            throw new \Exception("Le numéro $numero est déjà attribué dans cette équipe");
        }

        $sql = "INSERT INTO joueurs (nom, prenom, nationalite, numero, id_poste_predilection, id_placement_predilection, id_equipe) 
            VALUES (:nom, :prenom, :nationalite, :numero, :id_poste, :id_placement, :id_equipe)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':nationalite' => $nationalite,
            ':numero' => $numero,
            ':id_poste' => $idPoste,
            ':id_placement' => $idPlacement,
            ':id_equipe' => $idEquipe
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Met à jour un joueur existant
     */
    public function update(int $id, string $nom, string $prenom, string $nationalite, int $numero, int $idPoste, int $idPlacement, int $idEquipe): bool
    {
        // Vérifier que le numéro n'est pas déjà pris dans l'équipe (sauf pour ce joueur)
        if ($this->isNumeroTaken($numero, $idEquipe, $id)) {
            throw new \Exception("Le numéro $numero est déjà attribué dans cette équipe");
        }

        $sql = "UPDATE joueurs SET nom = :nom, prenom = :prenom, nationalite = :nationalite, numero = :numero, 
            id_poste_predilection = :id_poste, id_placement_predilection = :id_placement, id_equipe = :id_equipe 
            WHERE id_joueur = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':nationalite' => $nationalite,
            ':numero' => $numero,
            ':id_poste' => $idPoste,
            ':id_placement' => $idPlacement,
            ':id_equipe' => $idEquipe
        ]);
    }

    /**
     * Supprime un joueur
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM joueurs WHERE id_joueur = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Transfère un joueur vers une autre équipe avec un nouveau numéro
     */
    public function transfer(int $idJoueur, int $idEquipeCible, int $nouveauNumero): void
    {
        // Vérifier que le nouveau numéro n'est pas pris
        if ($this->isNumeroTaken($nouveauNumero, $idEquipeCible)) {
            throw new \Exception("Le numéro $nouveauNumero est déjà attribué dans l'équipe cible");
        }

        // Mettre à jour l'équipe et le numéro du joueur
        $stmt = $this->pdo->prepare("
        UPDATE joueurs 
        SET id_equipe = :id_equipe_cible, numero = :nouveau_numero 
        WHERE id_joueur = :id_joueur
    ");

        $stmt->execute([
            ':id_joueur' => $idJoueur,
            ':id_equipe_cible' => $idEquipeCible,
            ':nouveau_numero' => $nouveauNumero
        ]);
    }

    /**
     * Vérifie si un numéro est déjà pris dans une équipe
     */
    private function isNumeroTaken(int $numero, int $idEquipe, ?int $exceptJoueurId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM joueurs WHERE numero = :numero AND id_equipe = :id_equipe";
        $params = [':numero' => $numero, ':id_equipe' => $idEquipe];

        // Exclure le joueur actuel lors d'une modification
        if ($exceptJoueurId !== null) {
            $sql .= " AND id_joueur != :except_id";
            $params[':except_id'] = $exceptJoueurId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() > 0;
    }

    /**
     * Récupère tous les joueurs avec leurs informations d'équipe
     */
    public function getAllWithTeam(): array
    {
        $stmt = $this->pdo->query("
        SELECT j.*, e.nom as equipe_nom 
        FROM joueurs j 
        LEFT JOIN equipes e ON j.id_equipe = e.id_equipe 
        ORDER BY j.nom, j.prenom
    ");

        $rows = $stmt->fetchAll();
        $joueurs = [];

        foreach ($rows as $row) {
            $joueur = new \App\Entities\Joueur($row);
            $joueur->equipeNom = $row['equipe_nom'] ?? 'Aucune équipe';
            $joueurs[] = $joueur;
        }

        return $joueurs;
    }
}
