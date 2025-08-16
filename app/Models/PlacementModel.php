<?php
// Models/PlacementModel.php

namespace App\Models;

require_once __DIR__ . '/../Entities/Placement.php';

use App\Entities\Placement;

class PlacementModel
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les placement sous forme d'objets Placement
     * 
     * @return Placement[]
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM placements ORDER BY nom ASC");
        $rows = $stmt->fetchAll();

        $placements = [];
        foreach ($rows as $row) {
            $placements[] = new Placement((int)$row['id_placement'], $row['nom']);
        }

        return $placements;
    }

    /**
     * Récupère un placement par son ID
     */
    public function getById(int $id): ?Placement
    {
        $stmt = $this->pdo->prepare("SELECT * FROM placements WHERE id_placement = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ? new Placement((int)$row['id_placement'], $row['nom']) : null;
    }
}
