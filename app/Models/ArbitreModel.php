<?php
// Models/ArbitreModel.php

namespace App\Models;

use App\Entities\Arbitre;
use PDO;

class ArbitreModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM arbitres");

        $arbitres = [];
        while ($row = $stmt->fetch()) {
            $arbitres[] = new Arbitre($row);
        }

        return $arbitres;
    }

    public function getById(int $id): ?Arbitre
    {
        $stmt = $this->db->prepare("SELECT * FROM arbitres WHERE id_arbitre = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        return $data ? new Arbitre($data) : null;
    }
}
