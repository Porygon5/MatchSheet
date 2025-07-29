<?php
// Models/FootballMatchModel.php

namespace App\Models;

require_once __DIR__ . '/../Entities/FootballMatch.php';

use App\Entities\FootballMatch;

class FootballMatchModel
{
    private $pdo;

    public function __construct($db)
    {
        $this->pdo = $db;
    }

    public function getAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM matchs ORDER BY date_heure DESC");
        $rows = $stmt->fetchAll();
        
        $matchs = [];
        foreach ($rows as $row) {
            $matchs[] = new FootballMatch($row);
        }
        return $matchs;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO matchs 
            (date_heure, id_equipe_dom, id_equipe_ext, id_lieu, id_arbitre_principal, id_arbitre_assistant_1, id_arbitre_assistant_2)
            VALUES 
            (:date_heure, :id_equipe_dom, :id_equipe_ext, :id_lieu, :id_arbitre_principal, :id_arbitre_assistant_1, :id_arbitre_assistant_2)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':date_heure' => $data['date_heure'],
            ':id_equipe_dom' => $data['equipe_dom_id'],
            ':id_equipe_ext' => $data['equipe_ext_id'],
            ':id_lieu' => $data['lieu_id'],
            ':id_arbitre_principal' => $data['arbitre_central_id'],
            ':id_arbitre_assistant_1' => $data['arbitre_assistant_1_id'],
            ':id_arbitre_assistant_2' => $data['arbitre_assistant_2_id']
        ]);
    }
}
