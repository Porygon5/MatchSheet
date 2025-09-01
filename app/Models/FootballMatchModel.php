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

    /**
     * Récupère tous les matchs de football avec des informations enrichies.
     *
     * Cette méthode effectue une requête SQL pour obtenir la liste de tous les matchs,
     * en joignant les informations des équipes (domicile et extérieur) et du lieu.
     * Pour chaque match, elle calcule dynamiquement :
     *   - Le nombre de joueurs sélectionnés pour le match.
     *   - Le nombre d'événements liés au match.
     * Elle formate également la date du match en français, et ajoute des propriétés
     * supplémentaires (noms des équipes, lieu, abréviations, etc.) à l'objet FootballMatch.
     *
     * @return FootballMatch[] Tableau d'objets FootballMatch enrichis avec des propriétés supplémentaires.
     */
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
            // Calcul du nombre de joueurs selectionnés
            $stmtJoueurs = $this->pdo->prepare("
                SELECT COUNT(*) 
                FROM l_joueurs_selectionnes_matchs mj
                WHERE mj.id_match = :id_match
            ");

            $stmtJoueurs->execute([':id_match' => $row['id_match']]);
            $nb_joueurs_selectionnes = (int)$stmtJoueurs->fetchColumn();

            // Calcul du nombre d'événements liés à ce match
            $stmtE = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM evenements_match 
            WHERE id_match = :id
            ");
            $stmtE->execute([':id' => $row['id_match']]);
            $nb_evenements = (int)$stmtE->fetchColumn();

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

    /**
     * Insère un nouveau match de football dans la base de données.
     *
     * @param array $data Tableau associatif contenant les informations du match :
     *  - 'date_heure' (string) : Date et heure du match (format 'Y-m-d H:i:s').
     *  - 'equipe_dom_id' (int) : Identifiant de l'équipe à domicile.
     *  - 'equipe_ext_id' (int) : Identifiant de l'équipe à l'extérieur.
     *  - 'lieu_id' (int) : Identifiant du lieu du match.
     *  - 'arbitre_central_id' (int) : Identifiant de l'arbitre principal.
     *  - 'arbitre_assistant_1_id' (int) : Identifiant du premier arbitre assistant.
     *  - 'arbitre_assistant_2_id' (int) : Identifiant du second arbitre assistant.
     *
     * @return bool Retourne true si l'insertion a réussi, false sinon.
     */
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

    /**
     * Recherche et retourne un objet FootballMatch correspondant à l'identifiant donné.
     *
     * Cette méthode effectue une requête SQL pour récupérer les informations d'un match,
     * ainsi que les noms des équipes et du lieu associés. Elle calcule également le nombre
     * de joueurs sélectionnés et d'événements liés à ce match afin de déterminer le statut du match :
     *   - 1 : Match juste créé
     *   - 2 : Composition saisie
     *   - 3 : Match terminé (score ou événements présents)
     *
     * La date du match est formatée en français, et des propriétés supplémentaires sont ajoutées
     * à l'objet FootballMatch retourné (noms des équipes, lieu, abréviations, etc.).
     *
     * @param int $id L'identifiant du match à rechercher.
     * @return FootballMatch|null L'objet FootballMatch trouvé, ou null si aucun match ne correspond.
     */
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

        // Calcul du nombre de joueurs selectionnés
        $stmtJ = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM l_joueurs_selectionnes_matchs 
            WHERE id_match = :id
        ");
        $stmtJ->execute([':id' => $row['id_match']]);
        $nb_joueurs_selectionnes = (int)$stmtJ->fetchColumn();

        // Calcul du nombre d'événements liés à ce match
        $stmtE = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM evenements_match 
            WHERE id_match = :id
        ");
        $stmtE->execute([':id' => $row['id_match']]);
        $nb_evenements = (int)$stmtE->fetchColumn();

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

    /**
     * Met à jour le statut d'un match dans la base de données.
     *
     * @param int $matchId L'identifiant unique du match à mettre à jour.
     * @param int $statut Le nouveau statut du match :
     * 1 = à compléter,
     * 2 = à conclure,
     * 3 = terminée.
     *
     * @return void
     */
    public function markSubmitted(int $matchId, int $statut): void
    {
        $stmt = $this->pdo->prepare("UPDATE matchs SET statut = :st WHERE id_match = :id");
        $stmt->execute([
            ':id' => $matchId,
            ':st' => $statut
        ]);
    }
    /**
     * Récupère le statut actuel d'un match donné.
     *
     * @param int $matchId L'identifiant unique du match.
     * @return int|null Le statut du match (1, 2, ou 3) ou null si le match n'existe pas.
     */
    public function getStatut(int $matchId): ?int
    {
        $stmt = $this->pdo->prepare("
            SELECT statut
            FROM matchs
            WHERE id_match = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $matchId]);
        $statut = $stmt->fetchColumn();

        return $statut !== false ? (int)$statut : null;
    }
}
