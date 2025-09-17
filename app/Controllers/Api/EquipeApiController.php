<?php
// app/Controllers/Api/EquipeApiController.php

require_once __DIR__ . '/../../Models/EquipeModel.php';
require_once __DIR__ . '/../../Models/JoueurModel.php';
require_once __DIR__ . '/../../Models/ClubModel.php';
require_once __DIR__ . '/../../Models/EntraineurModel.php';

class EquipeApiController
{
    private $pdo;
    private $equipeModel;
    private $joueurModel;
    private $clubModel;
    private $entraineurModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->equipeModel = new \App\Models\EquipeModel($pdo);
        $this->joueurModel = new \App\Models\JoueurModel($pdo);
        $this->clubModel = new \App\Models\ClubModel($pdo);
        $this->entraineurModel = new \App\Models\EntraineurModel($pdo);
    }

    /**
     * GET /api/equipes
     * Retourne la liste des équipes
     */
    public function getEquipes()
    {
        try {
            $sql = "
                SELECT 
                    e.id_equipe,
                    e.nom,
                    e.domicile,
                    c.nom AS club_nom,
                    c.ville AS club_ville,
                    ent.nom AS entraineur_nom
                FROM equipes e
                LEFT JOIN clubs c ON e.id_club = c.id_club
                LEFT JOIN entraineurs ent ON e.id_entraineur = ent.id_entraineur
                ORDER BY e.nom ASC
            ";

            $stmt = $this->pdo->query($sql);
            $equipes = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $equipes[] = [
                    'id' => (int)$row['id_equipe'],
                    'nom' => $row['nom'],
                    'domicile' => (bool)$row['domicile'],
                    'abreviation' => strtoupper(substr($row['nom'], 0, 3)),
                    'club' => [
                        'nom' => $row['club_nom'],
                        'ville' => $row['club_ville']
                    ],
                    'entraineur' => $row['entraineur_nom']
                ];
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $equipes
            ]);
        } catch (Exception $e) {
            $this->jsonError('Erreur lors de la récupération des équipes: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/equipes/:id
     * Retourne les statistiques détaillées d'une équipe
     */
    public function getEquipeStats($id)
    {
        try {
            $equipeId = (int)$id;

            // Vérification existence équipe
            $equipe = $this->equipeModel->getById($equipeId);
            if (!$equipe) {
                $this->jsonError('Équipe non trouvée', 404);
                return;
            }

            // Informations de base de l'équipe
            $equipeInfo = $this->getEquipeInfo($equipeId);
            
            // Statistiques de la saison
            $stats = $this->getEquipeStatsSaison($equipeId);
            
            // Joueurs de l'équipe
            $joueurs = $this->getJoueursEquipe($equipeId);
            
            // Meilleurs buteurs
            $buteurs = $this->getMeilleursButeurs($equipeId);
            
            // Meilleure victoire et pire défaite
            $matchsRemarkables = $this->getMatchsRemarkables($equipeId);

            $response = [
                'id' => $equipeId,
                'nom' => $equipeInfo['nom'],
                'abreviation' => strtoupper(substr($equipeInfo['nom'], 0, 3)),
                'domicile' => (bool)$equipeInfo['domicile'],
                'club' => [
                    'nom' => $equipeInfo['club_nom'],
                    'ville' => $equipeInfo['club_ville']
                ],
                'entraineur' => $equipeInfo['entraineur_nom'],
                'statistiques_saison' => $stats,
                'joueurs' => $joueurs,
                'meilleurs_buteurs' => $buteurs,
                'matchs_remarkables' => $matchsRemarkables
            ];

            $this->jsonResponse([
                'success' => true,
                'data' => $response
            ]);
        } catch (Exception $e) {
            $this->jsonError('Erreur lors de la récupération des stats équipe: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Récupère les informations de base d'une équipe
     */
    private function getEquipeInfo($equipeId)
    {
        $sql = "
            SELECT 
                e.nom,
                e.domicile,
                c.nom AS club_nom,
                c.ville AS club_ville,
                ent.nom AS entraineur_nom
            FROM equipes e
            LEFT JOIN clubs c ON e.id_club = c.id_club
            LEFT JOIN entraineurs ent ON e.id_entraineur = ent.id_entraineur
            WHERE e.id_equipe = :equipe_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['equipe_id' => $equipeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Calcule les statistiques de saison d'une équipe
     */
    private function getEquipeStatsSaison($equipeId)
    {
        try {
            // Matchs joués - Version corrigée avec des paramètres uniques
            $sql = "
            SELECT 
                COUNT(*) AS matchs_joues,
                SUM(CASE 
                    WHEN (id_equipe_dom = :equipe_id_1 AND score_equipe_dom > score_equipe_ext) 
                      OR (id_equipe_ext = :equipe_id_2 AND score_equipe_ext > score_equipe_dom) 
                    THEN 1 ELSE 0 END) AS matchs_gagnes,
                SUM(CASE 
                    WHEN (id_equipe_dom = :equipe_id_3 AND score_equipe_dom < score_equipe_ext) 
                      OR (id_equipe_ext = :equipe_id_4 AND score_equipe_ext < score_equipe_dom) 
                    THEN 1 ELSE 0 END) AS matchs_perdus,
                SUM(CASE 
                    WHEN score_equipe_dom = score_equipe_ext AND score_equipe_dom IS NOT NULL
                    THEN 1 ELSE 0 END) AS matchs_nuls
            FROM matchs 
            WHERE (id_equipe_dom = :equipe_id_5 OR id_equipe_ext = :equipe_id_6)
            AND statut = 3 
            AND score_equipe_dom IS NOT NULL 
            AND score_equipe_ext IS NOT NULL
        ";

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                'equipe_id_1' => $equipeId,
                'equipe_id_2' => $equipeId,
                'equipe_id_3' => $equipeId,
                'equipe_id_4' => $equipeId,
                'equipe_id_5' => $equipeId,
                'equipe_id_6' => $equipeId
            ]);

            if (!$result) {
                throw new Exception("Erreur lors de l'exécution de la requête matchs: " . print_r($stmt->errorInfo(), true));
            }

            $matchStats = $stmt->fetch(PDO::FETCH_ASSOC);

            // Points marqués et encaissés - Version corrigée
            $sql = "
            SELECT 
                SUM(CASE WHEN id_equipe_dom = :equipe_id_1 THEN COALESCE(score_equipe_dom, 0) ELSE COALESCE(score_equipe_ext, 0) END) AS points_marques,
                SUM(CASE WHEN id_equipe_dom = :equipe_id_2 THEN COALESCE(score_equipe_ext, 0) ELSE COALESCE(score_equipe_dom, 0) END) AS points_encaisses
            FROM matchs 
            WHERE (id_equipe_dom = :equipe_id_3 OR id_equipe_ext = :equipe_id_4)
            AND statut = 3 
            AND score_equipe_dom IS NOT NULL 
            AND score_equipe_ext IS NOT NULL
        ";

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                'equipe_id_1' => $equipeId,
                'equipe_id_2' => $equipeId,
                'equipe_id_3' => $equipeId,
                'equipe_id_4' => $equipeId
            ]);

            if (!$result) {
                throw new Exception("Erreur lors de l'exécution de la requête points: " . print_r($stmt->errorInfo(), true));
            }

            $pointStats = $stmt->fetch(PDO::FETCH_ASSOC);

            // Cartons reçus
            $cartons = $this->getCartonsEquipe($equipeId);

            $matchsJoues = (int)$matchStats['matchs_joues'];
            $matchsGagnes = (int)$matchStats['matchs_gagnes'];
            $matchsPerdus = (int)$matchStats['matchs_perdus'];
            $matchsNuls = (int)$matchStats['matchs_nuls'];

            return [
                'matchs_joues' => $matchsJoues,
                'matchs_gagnes' => $matchsGagnes,
                'matchs_perdus' => $matchsPerdus,
                'matchs_nuls' => $matchsNuls,
                'pourcentage_victoires' => $matchsJoues > 0 ? round(($matchsGagnes / $matchsJoues) * 100, 1) : 0,
                'points_marques' => (int)($pointStats['points_marques'] ?? 0),
                'points_encaisses' => (int)($pointStats['points_encaisses'] ?? 0),
                'cartons_jaunes' => $cartons['jaunes'],
                'cartons_rouges' => $cartons['rouges']
            ];
        } catch (PDOException $e) {
            error_log("Erreur PDO dans getEquipeStatsSaison: " . $e->getMessage());
            throw $e;
        } catch (Exception $e) {
            error_log("Erreur générale dans getEquipeStatsSaison: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calcule les cartons reçus par l'équipe
     */
    private function getCartonsEquipe($equipeId)
    {
        $sql = "
            SELECT 
                te.nom AS type_carton,
                COUNT(*) AS nombre
            FROM evenements_match em
            JOIN types_evenement te ON em.id_type_evenement = te.id_type_evenement
            JOIN l_evenements_joueurs lej ON em.id_evenement_match = lej.id_evenement_match
            JOIN joueurs_selectionnes js ON lej.id_joueur_selectionne = js.id_joueur_selectionne
            WHERE js.id_equipe = :equipe_id
            AND te.nom IN ('Carton jaune', 'Carton rouge')
            GROUP BY te.nom
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['equipe_id' => $equipeId]);

        $cartons = ['jaunes' => 0, 'rouges' => 0];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['type_carton'] === 'Carton jaune') {
                $cartons['jaunes'] = (int)$row['nombre'];
            } elseif ($row['type_carton'] === 'Carton rouge') {
                $cartons['rouges'] = (int)$row['nombre'];
            }
        }

        return $cartons;
    }

    /**
     * Récupère les joueurs de l'équipe
     */
    private function getJoueursEquipe($equipeId)
    {
        $sql = "
            SELECT 
                j.id_joueur,
                j.nom,
                j.prenom,
                j.numero,
                j.nationalite,
                p.nom AS poste_nom,
                pl.nom AS placement_nom
            FROM joueurs j
            LEFT JOIN postes p ON j.id_poste_predilection = p.id_poste
            LEFT JOIN placements pl ON j.id_placement_predilection = pl.id_placement
            WHERE j.id_equipe = :equipe_id
            ORDER BY j.numero ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['equipe_id' => $equipeId]);

        $joueurs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $joueurs[] = [
                'id' => (int)$row['id_joueur'],
                'nom' => $row['nom'],
                'prenom' => $row['prenom'],
                'numero' => (int)$row['numero'],
                'nationalite' => $row['nationalite'],
                'poste_predilection' => $row['poste_nom'],
                'placement_predilection' => $row['placement_nom']
            ];
        }

        return $joueurs;
    }

    /**
     * Récupère le top 5 des buteurs de l'équipe
     */
    private function getMeilleursButeurs($equipeId)
    {
        $sql = "
            SELECT 
                j.id_joueur,
                j.nom,
                j.prenom,
                j.numero,
                COUNT(em.id_evenement_match) AS buts_marques
            FROM joueurs j
            JOIN joueurs_selectionnes js ON j.id_joueur = js.id_joueur
            JOIN l_evenements_joueurs lej ON js.id_joueur_selectionne = lej.id_joueur_selectionne
            JOIN evenements_match em ON lej.id_evenement_match = em.id_evenement_match
            JOIN types_evenement te ON em.id_type_evenement = te.id_type_evenement
            WHERE js.id_equipe = :equipe_id
            AND te.nom = 'But'
            GROUP BY j.id_joueur, j.nom, j.prenom, j.numero
            ORDER BY buts_marques DESC
            LIMIT 5
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['equipe_id' => $equipeId]);

        $buteurs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $buteurs[] = [
                'id' => (int)$row['id_joueur'],
                'nom' => $row['nom'],
                'prenom' => $row['prenom'],
                'numero' => (int)$row['numero'],
                'buts_marques' => (int)$row['buts_marques']
            ];
        }

        return $buteurs;
    }

    /**
     * Récupère la meilleure victoire et la pire défaite
     */
    private function getMatchsRemarkables($equipeId)
    {
        // Meilleure victoire (plus gros écart) - Version corrigée avec paramètres uniques
        $sql = "
        SELECT 
            m.id_match,
            m.date_heure,
            CASE WHEN m.id_equipe_dom = :equipe_id_1 THEN ed.nom ELSE ee.nom END AS equipe_adverse,
            CASE WHEN m.id_equipe_dom = :equipe_id_2 
                 THEN (m.score_equipe_dom - m.score_equipe_ext)
                 ELSE (m.score_equipe_ext - m.score_equipe_dom) END AS ecart,
            CASE WHEN m.id_equipe_dom = :equipe_id_3 
                 THEN CONCAT(m.score_equipe_dom, '-', m.score_equipe_ext)
                 ELSE CONCAT(m.score_equipe_ext, '-', m.score_equipe_dom) END AS score
        FROM matchs m
        LEFT JOIN equipes ed ON m.id_equipe_dom = ed.id_equipe
        LEFT JOIN equipes ee ON m.id_equipe_ext = ee.id_equipe
        WHERE (m.id_equipe_dom = :equipe_id_4 OR m.id_equipe_ext = :equipe_id_5)
        AND m.statut = 3
        AND m.score_equipe_dom IS NOT NULL
        AND m.score_equipe_ext IS NOT NULL
        AND ((m.id_equipe_dom = :equipe_id_6 AND m.score_equipe_dom > m.score_equipe_ext)
             OR (m.id_equipe_ext = :equipe_id_7 AND m.score_equipe_ext > m.score_equipe_dom))
        ORDER BY ecart DESC
        LIMIT 1
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'equipe_id_1' => $equipeId,
            'equipe_id_2' => $equipeId,
            'equipe_id_3' => $equipeId,
            'equipe_id_4' => $equipeId,
            'equipe_id_5' => $equipeId,
            'equipe_id_6' => $equipeId,
            'equipe_id_7' => $equipeId
        ]);
        $meilleureVictoire = $stmt->fetch(PDO::FETCH_ASSOC);

        // Pire défaite (plus gros écart négatif) - Version corrigée avec paramètres uniques
        $sql = "
        SELECT 
            m.id_match,
            m.date_heure,
            CASE WHEN m.id_equipe_dom = :equipe_id_1 THEN ed.nom ELSE ee.nom END AS equipe_adverse,
            CASE WHEN m.id_equipe_dom = :equipe_id_2 
                 THEN (m.score_equipe_ext - m.score_equipe_dom)
                 ELSE (m.score_equipe_dom - m.score_equipe_ext) END AS ecart,
            CASE WHEN m.id_equipe_dom = :equipe_id_3 
                 THEN CONCAT(m.score_equipe_dom, '-', m.score_equipe_ext)
                 ELSE CONCAT(m.score_equipe_ext, '-', m.score_equipe_dom) END AS score
        FROM matchs m
        LEFT JOIN equipes ed ON m.id_equipe_dom = ed.id_equipe
        LEFT JOIN equipes ee ON m.id_equipe_ext = ee.id_equipe
        WHERE (m.id_equipe_dom = :equipe_id_4 OR m.id_equipe_ext = :equipe_id_5)
        AND m.statut = 3
        AND m.score_equipe_dom IS NOT NULL
        AND m.score_equipe_ext IS NOT NULL
        AND ((m.id_equipe_dom = :equipe_id_6 AND m.score_equipe_dom < m.score_equipe_ext)
             OR (m.id_equipe_ext = :equipe_id_7 AND m.score_equipe_ext < m.score_equipe_dom))
        ORDER BY ecart DESC
        LIMIT 1
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'equipe_id_1' => $equipeId,
            'equipe_id_2' => $equipeId,
            'equipe_id_3' => $equipeId,
            'equipe_id_4' => $equipeId,
            'equipe_id_5' => $equipeId,
            'equipe_id_6' => $equipeId,
            'equipe_id_7' => $equipeId
        ]);
        $pireDefaite = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'meilleure_victoire' => $meilleureVictoire ? [
                'match_id' => (int)$meilleureVictoire['id_match'],
                'date' => $meilleureVictoire['date_heure'],
                'adversaire' => $meilleureVictoire['equipe_adverse'],
                'score' => $meilleureVictoire['score'],
                'ecart' => (int)$meilleureVictoire['ecart']
            ] : null,
            'pire_defaite' => $pireDefaite ? [
                'match_id' => (int)$pireDefaite['id_match'],
                'date' => $pireDefaite['date_heure'],
                'adversaire' => $pireDefaite['equipe_adverse'],
                'score' => $pireDefaite['score'],
                'ecart' => (int)$pireDefaite['ecart']
            ] : null
        ];
    }

    /**
     * Réponse JSON standardisée
     */
    private function jsonResponse($data, $status = 200)
    {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Erreur JSON standardisée
     */
    private function jsonError($message, $status = 400)
    {
        $this->jsonResponse(['error' => $message, 'status' => $status], $status);
    }
}