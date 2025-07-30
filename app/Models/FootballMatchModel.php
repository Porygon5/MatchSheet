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

    public function getAll(): array
    {
        $sql = "
            SELECT 
                m.*,
                ed.nom AS equipe_dom_nom,
                ee.nom AS equipe_ext_nom,
                l.nom AS lieu_nom
            FROM matchs m
            LEFT JOIN equipes ed ON m.id_equipe_dom = ed.id_equipe
            LEFT JOIN equipes ee ON m.id_equipe_ext = ee.id_equipe
            LEFT JOIN lieux l ON m.id_lieu = l.id_lieu
            ORDER BY m.date_heure DESC
        ";

        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll();

        $matchs = [];

        foreach ($rows as $row) {
            $match = new FootballMatch($row);

            $formatter = new \IntlDateFormatter(
                'fr_FR',
                \IntlDateFormatter::FULL,
                \IntlDateFormatter::SHORT,
                'Europe/Paris',
                \IntlDateFormatter::GREGORIAN,
                'EEEE d MMMM y - HH:mm'
            );

            $dateStr = $formatter->format($match->dateHeure);
            $match->dateFormatee = ucfirst($dateStr); // Majuscule en première lettre.

            // Ajout des propriétés supplémentaires dynamiquement.
            $match->equipe_dom_nom = $row['equipe_dom_nom'] ?? 'Inconnu';
            $match->equipe_ext_nom = $row['equipe_ext_nom'] ?? 'Inconnu';
            $match->lieu_nom = $row['lieu_nom'] ?? 'Inconnu';
            $match->id = $row['id_match'] ?? 0;

            $match->equipeDom = (object)[
                'nom' => $match->equipe_dom_nom,
                'abreviation' => strtoupper(substr($match->equipe_dom_nom, 0, 2))
            ];

            $match->equipeExt = (object)[
                'nom' => $match->equipe_ext_nom,
                'abreviation' => strtoupper(substr($match->equipe_ext_nom, 0, 2))
            ];

            $match->lieu = (object)[
                'nom' => $row['lieu_nom'] ?? ''
            ];

            $matchs[] = $match;
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

    public function findById(int $id): ?FootballMatch
    {
        $sql = "
            SELECT 
                m.*,
                ed.nom AS equipe_dom_nom,
                ee.nom AS equipe_ext_nom,
                l.nom AS lieu_nom
            FROM matchs m
            LEFT JOIN equipes ed ON m.id_equipe_dom = ed.id_equipe
            LEFT JOIN equipes ee ON m.id_equipe_ext = ee.id_equipe
            LEFT JOIN lieux l ON m.id_lieu = l.id_lieu
            WHERE m.id_match = :id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $match = new FootballMatch($row);

        $formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::SHORT,
            'Europe/Paris',
            \IntlDateFormatter::GREGORIAN,
            'EEEE d MMMM y - HH:mm'
        );

        $dateStr = $formatter->format($match->dateHeure);
        $match->dateFormatee = ucfirst($dateStr);

        $match->equipe_dom_nom = $row['equipe_dom_nom'] ?? 'Inconnu';
        $match->equipe_ext_nom = $row['equipe_ext_nom'] ?? 'Inconnu';
        $match->lieu_nom = $row['lieu_nom'] ?? 'Inconnu';
        $match->id = $row['id_match'] ?? 0;

        $match->equipeDom = (object)[
            'nom' => $match->equipe_dom_nom,
            'abreviation' => strtoupper(substr($match->equipe_dom_nom, 0, 2))
        ];

        $match->equipeExt = (object)[
            'nom' => $match->equipe_ext_nom,
            'abreviation' => strtoupper(substr($match->equipe_ext_nom, 0, 2))
        ];

        $match->lieu = (object)[
            'nom' => $row['lieu_nom'] ?? ''
        ];

        return $match;
    }
}
