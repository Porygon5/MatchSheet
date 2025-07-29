<?php
// Models/LieuModel.php

namespace App\Models;

require_once __DIR__ . '/../Entities/Lieu.php';

use App\Entities\Lieu;

class LieuModel
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les lieux sous forme d'objets Lieu
     * 
     * @return Lieu[]
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM lieux ORDER BY nom ASC");
        $rows = $stmt->fetchAll();

        $lieux = [];
        foreach ($rows as $row) {
            $lieux[] = new Lieu((int)$row['id_lieu'], $row['nom']);
        }

        return $lieux;
    }

    /**
     * Récupère un lieu par son ID
     */
    public function getById(int $id): ?Lieu
    {
        $stmt = $this->pdo->prepare("SELECT * FROM lieux WHERE id_lieu = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ? new Lieu((int)$row['id_lieu'], $row['nom']) : null;
    }
}
