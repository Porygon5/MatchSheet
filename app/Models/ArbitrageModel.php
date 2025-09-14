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
     * Sauvegarde l'arbitrage d'un match :
     * - met à jour les scores si fournis,
     * - supprime les événements existants,
     * - insère buts et cartons (liés à un id_joueur_selectionne).
     */
    public function save(Arbitrage $arbitrage): void
    {
        // Mise à jour des scores (si fournis)
        if ($arbitrage->scoreDom !== null || $arbitrage->scoreExt !== null) {
            $sets   = [];
            $params = [':match' => $arbitrage->idMatch];
            if ($arbitrage->scoreDom !== null) {
                $sets[] = 'score_equipe_dom = :sd';
                $params[':sd'] = $arbitrage->scoreDom;
            }
            if ($arbitrage->scoreExt !== null) {
                $sets[] = 'score_equipe_ext = :se';
                $params[':se'] = $arbitrage->scoreExt;
            }
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

        // Mise à jour du temps total du match si fourni
        if ($arbitrage->tempsJeu !== null) {
            $this->pdo->prepare("UPDATE matchs SET duree = :duree WHERE id_match = :match")
                ->execute([':duree' => $arbitrage->tempsJeu, ':match' => $arbitrage->idMatch]);
        }

        // Résolution des IDs des types d'événements
        $typeBut = $this->resolveTypeIdByLabel('But');
        $typeCartonJaune = $this->resolveTypeIdByLabel('Carton jaune');
        $typeCartonRouge = $this->resolveTypeIdByLabel('Carton rouge');

        // Vérification que les types existent
        if ($typeBut === null || $typeCartonJaune === null || $typeCartonRouge === null) {
            throw new \Exception("Types d'événements manquants dans la base de données");
        }

        // Préparation des requêtes d'insertion
        $insertEvenement = $this->pdo->prepare("
            INSERT INTO evenements_match (id_type_evenement, minute, id_match)
            VALUES (:type, :minute, :match)
        ");

        $insertLien = $this->pdo->prepare("
            INSERT INTO l_evenements_joueurs (id_evenement_match, id_joueur_selectionne)
            VALUES (:evenement, :id_js)
        ");
        
        // Requête pour résoudre l'id_joueur_selectionne en fonction du match
        $resolveJoueurSelectionneId = $this->pdo->prepare("
            SELECT js.id_joueur_selectionne 
            FROM joueurs_selectionnes js
            INNER JOIN l_joueurs_selectionnes_matchs ljm ON ljm.id_joueur_selectionne = js.id_joueur_selectionne
            WHERE js.id_joueur = :id_joueur
            AND js.id_equipe = :id_equipe 
            AND ljm.id_match = :id_match
            LIMIT 1
        ");

        // Buts domicile
        foreach ($arbitrage->butsDom as $but) {
            if (isset($but['joueur_id']) && $but['joueur_id'] !== null && $but['joueur_id'] !== '') {
                $resolveJoueurSelectionneId->execute([
                    ':id_joueur' => (int)$but['joueur_id'], 
                    ':id_equipe' => $arbitrage->idEquipeDom,
                    ':id_match'  => $arbitrage->idMatch
                ]);

                $idJoueurSelectionne = $resolveJoueurSelectionneId->fetchColumn();
                
                if ($idJoueurSelectionne) {
                    $insertEvenement->execute([
                        ':type' => $typeBut, 
                        ':minute' => (int)$but['minute'], 
                        ':match' => $arbitrage->idMatch
                    ]);
                    $evenementId = (int)$this->pdo->lastInsertId();
                    $insertLien->execute([
                        ':evenement' => $evenementId, 
                        ':id_js' => (int)$idJoueurSelectionne
                    ]);
                }
            }
        }

        // Buts extérieur
        foreach ($arbitrage->butsExt as $but) {
            if (isset($but['joueur_id']) && $but['joueur_id'] !== null && $but['joueur_id'] !== '') {
                $resolveJoueurSelectionneId->execute([
                    ':id_joueur' => (int)$but['joueur_id'], 
                    ':id_equipe' => $arbitrage->idEquipeExt,
                    ':id_match' => $arbitrage->idMatch
                ]);
                $idJoueurSelectionne = $resolveJoueurSelectionneId->fetchColumn();

                if ($idJoueurSelectionne) {
                    $insertEvenement->execute([
                        ':type' => $typeBut, 
                        ':minute' => (int)$but['minute'], 
                        ':match' => $arbitrage->idMatch
                    ]);
                    $evenementId = (int)$this->pdo->lastInsertId();
                    $insertLien->execute([
                        ':evenement' => $evenementId, 
                        ':id_js' => (int)$idJoueurSelectionne
                    ]);
                }
            }
        }

        // Cartons domicile
        foreach ($arbitrage->cartonsDom as $carton) {
            if (isset($carton['joueur_id']) && $carton['joueur_id'] !== null && $carton['joueur_id'] !== '') {
                $resolveJoueurSelectionneId->execute([
                    ':id_joueur' => (int)$carton['joueur_id'], 
                    ':id_equipe' => $arbitrage->idEquipeDom,
                    ':id_match' => $arbitrage->idMatch
                ]);
                $idJoueurSelectionne = $resolveJoueurSelectionneId->fetchColumn();

                if ($idJoueurSelectionne) {
                    $typeCarton = (strtolower($carton['type']) === 'rouge') ? $typeCartonRouge : $typeCartonJaune;
                    $insertEvenement->execute([
                        ':type' => $typeCarton, 
                        ':minute' => (int)$carton['minute'], 
                        ':match' => $arbitrage->idMatch
                    ]);
                    $evenementId = (int)$this->pdo->lastInsertId();
                    $insertLien->execute([
                        ':evenement' => $evenementId, 
                        ':id_js' => (int)$idJoueurSelectionne
                    ]);
                }
            }
        }

        // Cartons extérieur
        foreach ($arbitrage->cartonsExt as $carton) {
            if (isset($carton['joueur_id']) && $carton['joueur_id'] !== null && $carton['joueur_id'] !== '') {
                $resolveJoueurSelectionneId->execute([
                    ':id_joueur' => (int)$carton['joueur_id'], 
                    ':id_equipe' => $arbitrage->idEquipeExt,
                    ':id_match' => $arbitrage->idMatch
                ]);
                $idJoueurSelectionne = $resolveJoueurSelectionneId->fetchColumn();
                
                if ($idJoueurSelectionne) {
                    $typeCarton = (strtolower($carton['type']) === 'rouge') ? $typeCartonRouge : $typeCartonJaune;
                    $insertEvenement->execute([
                        ':type' => $typeCarton, 
                        ':minute' => (int)$carton['minute'], 
                        ':match' => $arbitrage->idMatch
                    ]);
                    $evenementId = (int)$this->pdo->lastInsertId();
                    $insertLien->execute([
                        ':evenement' => $evenementId, 
                        ':id_js' => (int)$idJoueurSelectionne
                    ]);
                }
            }
        }
    }

    /**
     * Retourne l'arbitrage d'un match sous forme d'entité Arbitrage.
     */
    public function getByMatch(int $matchId): Arbitrage
    {
        // Récupération des informations de base du match
        $stmtScores = $this->pdo->prepare("
            SELECT 
                score_equipe_dom AS score_dom, 
                score_equipe_ext AS score_ext,
                duree,
                id_equipe_dom,
                id_equipe_ext
            FROM matchs
            WHERE id_match = :match
            LIMIT 1
        ");
        $stmtScores->execute([':match' => $matchId]);
        $scores = $stmtScores->fetch();
        
        if (!$scores) {
            throw new \Exception("Match non trouvé : ID $matchId");
        }

        $data = [
            'id_match'      => $matchId,
            'score_dom'     => $scores['score_dom'],
            'score_ext'     => $scores['score_ext'],
            'temps_jeu'     => $scores['duree'],
            'buts_dom'      => [],
            'buts_ext'      => [],
            'cartons_dom'   => [],
            'cartons_ext'   => [],
            'id_equipe_dom' => (int)$scores['id_equipe_dom'],
            'id_equipe_ext' => (int)$scores['id_equipe_ext'],
        ];

        $idEquipeDom = (int)$scores['id_equipe_dom'];
        $idEquipeExt = (int)$scores['id_equipe_ext'];
        
        // Événements + lien joueur sélectionné
        $stmt = $this->pdo->prepare("
            SELECT 
                em.id_evenement_match,
                em.minute,
                te.nom AS type_nom,
                js.id_joueur_selectionne,
                js.id_equipe,
                j.id_joueur,
                j.nom,
                j.prenom
            FROM evenements_match em
            JOIN types_evenement te ON te.id_type_evenement = em.id_type_evenement
            LEFT JOIN l_evenements_joueurs lej ON lej.id_evenement_match = em.id_evenement_match
            LEFT JOIN joueurs_selectionnes js ON js.id_joueur_selectionne = lej.id_joueur_selectionne
            LEFT JOIN joueurs j ON j.id_joueur = js.id_joueur
            WHERE em.id_match = :match
            ORDER BY em.minute ASC, em.id_evenement_match ASC
        ");
        $stmt->execute([':match' => $matchId]);

        while ($row = $stmt->fetch()) {
            $typeNom        = strtolower(trim((string)$row['type_nom']));
            $minute         = (int)$row['minute'];
            $idJoueur       = $row['id_joueur'] !== null ? (int)$row['id_joueur'] : null;
            $idEquipeJoueur = $row['id_equipe'] !== null ? (int)$row['id_equipe'] : null;

            $isBut   = ($typeNom === 'but');
            $isJaune = ($typeNom === 'carton jaune');
            $isRouge = ($typeNom === 'carton rouge');

            if ($idJoueur && $idEquipeJoueur) {
                if ($isBut) {
                    $but = [
                        'joueur_id' => $idJoueur,
                        'minute'    => $minute,
                        'points'    => 1
                    ];

                    if ($idEquipeJoueur === $idEquipeDom) {
                        $data['buts_dom'][] = $but;
                    } elseif ($idEquipeJoueur === $idEquipeExt) {
                        $data['buts_ext'][] = $but;
                    }
                } elseif ($isJaune || $isRouge) {
                    $carton = [
                        'joueur_id' => $idJoueur,
                        'minute'    => $minute,
                        'type'      => $isRouge ? 'rouge' : 'jaune'
                    ];

                    if ($idEquipeJoueur === $idEquipeDom) {
                        $data['cartons_dom'][] = $carton;
                    } elseif ($idEquipeJoueur === $idEquipeExt) {
                        $data['cartons_ext'][] = $carton;
                    }
                }
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
     * Résout l'ID d'un type d'événement à partir de son libellé.
     * Retourne null si le type n'existe pas.
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