<?php
// Models/EquipeModel.php

namespace App\Models;

use App\Entities\Equipe;

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
}
