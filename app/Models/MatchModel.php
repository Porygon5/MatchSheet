<?php

class MatchModel
{
    private $pdo;

    public function __construct($db)
    {
        $this->pdo = $db;
    }

    public function getAll()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM matchs ORDER BY date_heure DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
