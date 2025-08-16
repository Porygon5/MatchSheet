<?php
// Models/PosteModel.php

namespace App\Models;

require_once __DIR__ . '/../Entities/Poste.php';

use App\Entities\Poste;

class PosteModel
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les postes sous forme d'objets Poste
     * 
     * @return Poste[]
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM postes ORDER BY nom ASC");
        $rows = $stmt->fetchAll();

        $postes = [];
        foreach ($rows as $row) {
            $postes[] = new Poste((int)$row['id_poste'], $row['nom']);
        }

        return $postes;
    }

    /**
     * Récupère un poste par son ID
     */
    public function getById(int $id): ?Poste
    {
        $stmt = $this->pdo->prepare("SELECT * FROM postes WHERE id_poste = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ? new Poste((int)$row['id_poste'], $row['nom']) : null;
    }
}
