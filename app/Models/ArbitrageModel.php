<?php
// app/Models/ArbitrageModel.php

namespace App\Models;

use PDO;
use App\Entities\Arbitrage;

require_once __DIR__ . '/../Entities/Arbitrage.php';

final class ArbitrageModel
{
    private PDO $pdo;

    public function __construct(PDO $db)
    {
        $this->pdo = $db;
    }

    /**
     * Sauvegarde l’arbitrage d’un match :
     * - met à jour les scores si fournis,
     * - supprime les événements existants,
     * - insère buts et cartons (liés à un id_joueur_selectionne).
     *
     * IMPORTANT : côté formulaire, les <option> doivent porter la valeur
     * id_joueur_selectionne (et non id_joueur), puisque la table de lien
     * l_evenements_joueurs référence id_joueur_selectionne.
     */
    public function save(Arbitrage $arbitrage): void
    {
        // Mise à jour des scores (si fournis)
        if ($arbitrage->scoreDom !== null || $arbitrage->scoreExt !== null) {
            $sets   = [];
            $params = [':match' => $arbitrage->idMatch];
            if ($arbitrage->scoreDom !== null) { $sets[] = 'score_equipe_dom = :sd'; $params[':sd'] = $arbitrage->scoreDom; }
            if ($arbitrage->scoreExt !== null) { $sets[] = 'score_equipe_ext = :se'; $params[':se'] = $arbitrage->scoreExt; }
            $sql = 'UPDATE matchs SET ' . implode(', ', $sets) . ' WHERE id_match = :match';
            $this->pdo->prepare($sql)->execute($params);
        }

        // Suppression des événements liés au match (et des liens joueurs)
        $this->pdo->prepare("
            DELETE FROM l_evenements_joueurs
            WHERE id_evenement_match IN (
                SELECT id_evenement_match FROM evenements_match WHERE id_match = :match
            )
        ")->execute([':match' => $arbitrage->idMatch]);

        $this->pdo->prepare("DELETE FROM evenements_match WHERE id_match = :match")
                  ->execute([':match' => $arbitrage->idMatch]);

        // Résolution des IDs des types d’événements
        $typeBut         = $this->resolveTypeIdByLabel('But');
        $typeCartonJaune = $this->resolveTypeIdByLabel('Carton jaune');
        $typeCartonRouge = $this->resolveTypeIdByLabel('Carton rouge');

        // Insertion des nouveaux événements.
        $insertEvenement = $this->pdo->prepare("
            INSERT INTO evenements_match (id_type_evenement, minute, id_match)
            VALUES (:type, :minute, :match)
        ");

        $insertLien = $this->pdo->prepare("
            INSERT INTO l_evenements_joueurs (id_evenement_match, id_joueur_selectionne)
            VALUES (:evenement, :id_js)
        ");

        // Buts (domicile + extérieur)
        foreach ($arbitrage->butsDom as $but) {
            $insertEvenement->execute([':type' => $typeBut, ':minute' => (int)$but['minute'], ':match' => $arbitrage->idMatch]);
            $evenementId = (int)$this->pdo->lastInsertId();
            $insertLien->execute([':evenement' => $evenementId, ':id_js' => (int)$but['joueur_id']]);
        }
        foreach ($arbitrage->butsExt as $but) {
            $insertEvenement->execute([':type' => $typeBut, ':minute' => (int)$but['minute'], ':match' => $arbitrage->idMatch]);
            $evenementId = (int)$this->pdo->lastInsertId();
            $insertLien->execute([':evenement' => $evenementId, ':id_js' => (int)$but['joueur_id']]);
        }

        // Cartons (domicile + extérieur)
        foreach ($arbitrage->cartonsDom as $carton) {
            $label = (strtolower($carton['type']) === 'rouge') ? $typeCartonRouge : $typeCartonJaune;
            $insertEvenement->execute([':type' => $label, ':minute' => (int)$carton['minute'], ':match' => $arbitrage->idMatch]);
            $evenementId = (int)$this->pdo->lastInsertId();
            $insertLien->execute([':evenement' => $evenementId, ':id_js' => (int)$carton['joueur_id']]);
        }
        foreach ($arbitrage->cartonsExt as $carton) {
            $label = (strtolower($carton['type']) === 'rouge') ? $typeCartonRouge : $typeCartonJaune;
            $insertEvenement->execute([':type' => $label, ':minute' => (int)$carton['minute'], ':match' => $arbitrage->idMatch]);
            $evenementId = (int)$this->pdo->lastInsertId();
            $insertLien->execute([':evenement' => $evenementId, ':id_js' => (int)$carton['joueur_id']]);
        }
    }

    /**
     * Retourne l’arbitrage d’un match sous forme d’entité Arbitrage.
     */
    public function getByMatch(int $matchId): Arbitrage
    {
        // Scores
        $stmtScores = $this->pdo->prepare("
            SELECT score_equipe_dom AS score_dom, score_equipe_ext AS score_ext
            FROM matchs
            WHERE id_match = :match
            LIMIT 1
        ");
        $stmtScores->execute([':match' => $matchId]);
        $scores = $stmtScores->fetch() ?: ['score_dom' => null, 'score_ext' => null];

        $data = [
            'id_match'    => $matchId,
            'score_dom'   => $scores['score_dom'],
            'score_ext'   => $scores['score_ext'],
            'buts_dom'    => [],
            'buts_ext'    => [],
            'cartons_dom' => [],
            'cartons_ext' => [],
        ];

        // Événements + lien joueur sélectionné
        $stmt = $this->pdo->prepare("
            SELECT 
                evenements_match.id_evenement_match,
                evenements_match.minute,
                types_evenement.nom AS type_nom,
                l_evenements_joueurs.id_joueur_selectionne
            FROM evenements_match
            JOIN types_evenement
              ON types_evenement.id_type_evenement = evenements_match.id_type_evenement
            LEFT JOIN l_evenements_joueurs
              ON l_evenements_joueurs.id_evenement_match = evenements_match.id_evenement_match
            WHERE evenements_match.id_match = :match
            ORDER BY evenements_match.minute ASC, evenements_match.id_evenement_match ASC
        ");
        $stmt->execute([':match' => $matchId]);

        while ($row = $stmt->fetch()) {
            $typeNom             = strtolower(trim((string)$row['type_nom']));
            $minute              = (int)$row['minute'];
            $idJoueurSelectionne = isset($row['id_joueur_selectionne']) ? (int)$row['id_joueur_selectionne'] : 0;

            $isBut   = ($typeNom === 'but');
            $isJaune = ($typeNom === 'carton jaune');
            $isRouge = ($typeNom === 'carton rouge');

            if ($isBut && $idJoueurSelectionne > 0) {
                $data['buts_dom'][] = ['joueur_id' => $idJoueurSelectionne, 'minute' => $minute, 'points' => 1];
            } elseif (($isJaune || $isRouge) && $idJoueurSelectionne > 0) {
                $data['cartons_dom'][] = [
                    'joueur_id' => $idJoueurSelectionne,
                    'minute'    => $minute,
                    'type'      => $isRouge ? 'rouge' : 'jaune'
                ];
            }
        }

        return new Arbitrage($data);
    }

    /** Passe le match en statut "terminé". */
    public function markFinished(int $matchId): void
    {
        $this->pdo->prepare("UPDATE matchs SET statut = 3 WHERE id_match = :match")
                  ->execute([':match' => $matchId]);
    }

    /**
     * Résout l’ID d’un type d’événement à partir de son libellé.
     * Retourne null si le type n’existe pas.
     */
    private function resolveTypeIdByLabel(string $label): ?int
    {
        $stmt = $this->pdo->prepare("
            SELECT id_type_evenement
            FROM types_evenement
            WHERE nom = :nom
            LIMIT 1
        ");
        $stmt->execute([':nom' => $label]);
        $id = $stmt->fetchColumn();

        return $id !== false ? (int)$id : null;
    }
}
