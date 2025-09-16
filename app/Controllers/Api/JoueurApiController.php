<?php
// app/Controllers/Api/JoueurApiController.php

require_once __DIR__ . '/../../Models/JoueurModel.php';

class JoueurApiController
{
    private $pdo;
    private $joueurModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->joueurModel = new \App\Models\JoueurModel($pdo);
    }

    /**
     * GET /api/joueurs/:id
     * Retourne le détail complet d'un joueur avec ses statistiques
     */
    public function getJoueurDetails($id)
    {
        try {
            $joueurId = (int)$id;

            // Récupération du joueur
            $joueur = $this->joueurModel->findById($joueurId);
            if (!$joueur) {
                $this->jsonError('Joueur non trouvé', 404);
                return;
            }

            // Informations de base du joueur avec équipe
            $joueurInfo = $this->getJoueurInfoComplete($joueurId);
            
            // Statistiques du joueur
            $stats = $this->getJoueurStats($joueurId);
            
            // Historique des matchs
            $historiqueMatchs = $this->getHistoriqueMatchs($joueurId);

            $response = [
                'id' => $joueur->id,
                'nom' => $joueur->nom,
                'prenom' => $joueur->prenom,
                'numero' => $joueur->numero,
                'nationalite' => $joueur->nationalite,
                'equipe' => [
                    'id' => $joueurInfo['id_equipe'],
                    'nom' => $joueurInfo['equipe_nom']
                ],
                'poste_predilection' => $joueurInfo['poste_nom'],
                'placement_predilection' => $joueurInfo['placement_nom'],
                'statistiques' => $stats,
                'historique_matchs' => $historiqueMatchs
            ];

            $this->jsonResponse([
                'success' => true,
                'data' => $response
            ]);
        } catch (Exception $e) {
            $this->jsonError('Erreur lors de la récupération du joueur: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Récupère les informations complètes du joueur
     */
    private function getJoueurInfoComplete($joueurId)
    {
        $sql = "
            SELECT 
                j.id_equipe,
                e.nom AS equipe_nom,
                p.nom AS poste_nom,
                pl.nom AS placement_nom
            FROM joueurs j
            LEFT JOIN equipes e ON j.id_equipe = e.id_equipe
            LEFT JOIN postes p ON j.id_poste_predilection = p.id_poste
            LEFT JOIN placements pl ON j.id_placement_predilection = pl.id_placement
            WHERE j.id_joueur = :joueur_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['joueur_id' => $joueurId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Calcule les statistiques globales du joueur
     */
    private function getJoueurStats($joueurId)
    {
        // Buts marqués
        $buts = $this->getButsJoueur($joueurId);
        
        // Cartons reçus
        $cartons = $this->getCartonsJoueur($joueurId);
        
        // Participations aux matchs
        $participations = $this->getParticipationsJoueur($joueurId);

        return [
            'buts_marques' => $buts,
            'cartons_jaunes' => $cartons['jaunes'],
            'cartons_rouges' => $cartons['rouges'],
            'matchs_joues' => $participations['total'],
            'titularisations' => $participations['titularisations'],
            'entrees_en_jeu' => $participations['remplacements'],
            'pourcentage_titularisations' => $participations['total'] > 0 
                ? round(($participations['titularisations'] / $participations['total']) * 100, 1) 
                : 0
        ];
    }

    /**
     * Compte les buts marqués par le joueur
     */
    private function getButsJoueur($joueurId)
    {
        $sql = "
            SELECT COUNT(*) as buts
            FROM evenements_match em
            JOIN types_evenement te ON em.id_type_evenement = te.id_type_evenement
            JOIN l_evenements_joueurs lej ON em.id_evenement_match = lej.id_evenement_match
            JOIN joueurs_selectionnes js ON lej.id_joueur_selectionne = js.id_joueur_selectionne
            WHERE js.id_joueur = :joueur_id
            AND te.nom = 'But'
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['joueur_id' => $joueurId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)$result['buts'];
    }

    /**
     * Compte les cartons reçus par le joueur
     */
    private function getCartonsJoueur($joueurId)
    {
        $sql = "
            SELECT 
                te.nom AS type_carton,
                COUNT(*) AS nombre
            FROM evenements_match em
            JOIN types_evenement te ON em.id_type_evenement = te.id_type_evenement
            JOIN l_evenements_joueurs lej ON em.id_evenement_match = lej.id_evenement_match
            JOIN joueurs_selectionnes js ON lej.id_joueur_selectionne = js.id_joueur_selectionne
            WHERE js.id_joueur = :joueur_id
            AND te.nom IN ('Carton jaune', 'Carton rouge')
            GROUP BY te.nom
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['joueur_id' => $joueurId]);
        
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
     * Calcule les participations du joueur aux matchs
     */
    private function getParticipationsJoueur($joueurId)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_matchs,
                SUM(CASE WHEN js.titulaire = 1 THEN 1 ELSE 0 END) as titularisations
            FROM joueurs_selectionnes js
            JOIN l_joueurs_selectionnes_matchs ljm ON js.id_joueur_selectionne = ljm.id_joueur_selectionne
            JOIN matchs m ON ljm.id_match = m.id_match
            WHERE js.id_joueur = :joueur_id
            AND m.statut = 3
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['joueur_id' => $joueurId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $total = (int)$result['total_matchs'];
        $titularisations = (int)$result['titularisations'];
        
        return [
            'total' => $total,
            'titularisations' => $titularisations,
            'remplacements' => $total - $titularisations
        ];
    }

    /**
     * Récupère l'historique des matchs du joueur
     */
    private function getHistoriqueMatchs($joueurId, $limit = 10)
    {
        $sql = "
            SELECT 
                m.id_match,
                m.date_heure,
                js.titulaire,
                js.id_role,
                js.id_poste_definitif,
                js.id_placement_definitif,
                ed.nom AS equipe_dom_nom,
                ee.nom AS equipe_ext_nom,
                m.score_equipe_dom,
                m.score_equipe_ext,
                p.nom AS poste_nom,
                pl.nom AS placement_nom,
                r.nom AS role_nom,
                -- Sous-requêtes pour compter les événements du joueur dans ce match
                (SELECT COUNT(*) 
                 FROM evenements_match em2 
                 JOIN types_evenement te2 ON em2.id_type_evenement = te2.id_type_evenement 
                 JOIN l_evenements_joueurs lej2 ON em2.id_evenement_match = lej2.id_evenement_match
                 WHERE lej2.id_joueur_selectionne = js.id_joueur_selectionne
                 AND te2.nom = 'But') AS buts_match,
                (SELECT COUNT(*) 
                 FROM evenements_match em2 
                 JOIN types_evenement te2 ON em2.id_type_evenement = te2.id_type_evenement 
                 JOIN l_evenements_joueurs lej2 ON em2.id_evenement_match = lej2.id_evenement_match
                 WHERE lej2.id_joueur_selectionne = js.id_joueur_selectionne
                 AND te2.nom = 'Carton jaune') AS cartons_jaunes_match,
                (SELECT COUNT(*) 
                 FROM evenements_match em2 
                 JOIN types_evenement te2 ON em2.id_type_evenement = te2.id_type_evenement 
                 JOIN l_evenements_joueurs lej2 ON em2.id_evenement_match = lej2.id_evenement_match
                 WHERE lej2.id_joueur_selectionne = js.id_joueur_selectionne
                 AND te2.nom = 'Carton rouge') AS cartons_rouges_match
            FROM joueurs_selectionnes js
            JOIN l_joueurs_selectionnes_matchs ljm ON js.id_joueur_selectionne = ljm.id_joueur_selectionne
            JOIN matchs m ON ljm.id_match = m.id_match
            LEFT JOIN equipes ed ON m.id_equipe_dom = ed.id_equipe
            LEFT JOIN equipes ee ON m.id_equipe_ext = ee.id_equipe
            LEFT JOIN postes p ON js.id_poste_definitif = p.id_poste
            LEFT JOIN placements pl ON js.id_placement_definitif = pl.id_placement
            LEFT JOIN roles r ON js.id_role = r.id_role
            WHERE js.id_joueur = :joueur_id
            AND m.statut = 3
            ORDER BY m.date_heure DESC
            LIMIT :limit
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':joueur_id', $joueurId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $matchs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Déterminer si le joueur était dans l'équipe domicile ou extérieure
            $estDomicile = $this->isJoueurEquipeDomicile($joueurId, (int)$row['id_match']);
            $equipeJoueur = $estDomicile ? $row['equipe_dom_nom'] : $row['equipe_ext_nom'];
            $equipeAdverse = $estDomicile ? $row['equipe_ext_nom'] : $row['equipe_dom_nom'];
            $scoreJoueur = $estDomicile ? $row['score_equipe_dom'] : $row['score_equipe_ext'];
            $scoreAdverse = $estDomicile ? $row['score_equipe_ext'] : $row['score_equipe_dom'];

            // Déterminer le résultat
            $resultat = 'nul';
            if ($scoreJoueur !== null && $scoreAdverse !== null) {
                if ($scoreJoueur > $scoreAdverse) {
                    $resultat = 'victoire';
                } elseif ($scoreJoueur < $scoreAdverse) {
                    $resultat = 'defaite';
                }
            }

            $matchs[] = [
                'match_id' => (int)$row['id_match'],
                'date' => $row['date_heure'],
                'equipe_joueur' => $equipeJoueur,
                'equipe_adverse' => $equipeAdverse,
                'score_joueur' => $scoreJoueur !== null ? (int)$scoreJoueur : null,
                'score_adverse' => $scoreAdverse !== null ? (int)$scoreAdverse : null,
                'resultat' => $resultat,
                'titulaire' => (bool)$row['titulaire'],
                'poste_joue' => $row['poste_nom'],
                'placement_joue' => $row['placement_nom'],
                'role' => $row['role_nom'],
                'statistiques_match' => [
                    'buts' => (int)$row['buts_match'],
                    'cartons_jaunes' => (int)$row['cartons_jaunes_match'],
                    'cartons_rouges' => (int)$row['cartons_rouges_match']
                ]
            ];
        }

        return $matchs;
    }

    /**
     * Détermine si le joueur était dans l'équipe domicile pour un match donné
     */
    private function isJoueurEquipeDomicile($joueurId, $matchId)
    {
        $sql = "
            SELECT m.id_equipe_dom, js.id_equipe
            FROM matchs m
            JOIN l_joueurs_selectionnes_matchs ljm ON m.id_match = ljm.id_match
            JOIN joueurs_selectionnes js ON ljm.id_joueur_selectionne = js.id_joueur_selectionne
            WHERE js.id_joueur = :joueur_id AND m.id_match = :match_id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'joueur_id' => $joueurId,
            'match_id' => $matchId
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result && (int)$result['id_equipe_dom'] === (int)$result['id_equipe'];
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