<?php
// app/Controllers/Api/MatchApiController.php

require_once __DIR__ . '/../../Models/FootballMatchModel.php';
require_once __DIR__ . '/../../Models/ArbitrageModel.php';
require_once __DIR__ . '/../../Models/CompositionModel.php';
require_once __DIR__ . '/../../Models/JoueurModel.php';
require_once __DIR__ . '/../../Models/ArbitreModel.php';

class MatchApiController
{
    private $pdo;
    private $matchModel;
    private $arbitrageModel;
    private $compositionModel;
    private $joueurModel;
    private $arbitreModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->matchModel = new \App\Models\FootballMatchModel($pdo);
        $this->arbitrageModel = new \App\Models\ArbitrageModel($pdo);
        $this->compositionModel = new \App\Models\CompositionModel($pdo);
        $this->joueurModel = new \App\Models\JoueurModel($pdo);
        $this->arbitreModel = new \App\Models\ArbitreModel($pdo);
    }

    /**
     * GET /api/matchs
     * Retourne la liste des matchs avec filtrage possible
     */
    public function getMatchs()
    {
        try {
            // Paramètres de requête
            $status = $_GET['status'] ?? null; // upcoming, completed, all
            $equipeId = isset($_GET['equipe_id']) ? (int)$_GET['equipe_id'] : null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

            $sql = "
                SELECT 
                    m.id_match,
                    m.date_heure,
                    m.statut,
                    m.score_equipe_dom,
                    m.score_equipe_ext,
                    ed.nom AS equipe_dom_nom,
                    ed.id_equipe AS equipe_dom_id,
                    ee.nom AS equipe_ext_nom,
                    ee.id_equipe AS equipe_ext_id,
                    l.nom AS lieu_nom,
                    l.id_lieu
                FROM matchs m
                LEFT JOIN equipes ed ON m.id_equipe_dom = ed.id_equipe
                LEFT JOIN equipes ee ON m.id_equipe_ext = ee.id_equipe
                LEFT JOIN lieux l ON m.id_lieu = l.id_lieu
                WHERE 1=1
            ";

            $params = [];

            // Filtrage par statut
            if ($status === 'upcoming') {
                $sql .= " AND m.statut IN (1, 2)";
            } elseif ($status === 'completed') {
                $sql .= " AND m.statut = 3";
            }

            // Filtrage par équipe
            if ($equipeId) {
                $sql .= " AND (m.id_equipe_dom = :equipe_id OR m.id_equipe_ext = :equipe_id)";
                $params['equipe_id'] = $equipeId;
            }

            $sql .= " ORDER BY m.date_heure DESC LIMIT :limit OFFSET :offset";
            $params['limit'] = $limit;
            $params['offset'] = $offset;

            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            
            $matchs = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $matchs[] = [
                    'id' => (int)$row['id_match'],
                    'date_heure' => $row['date_heure'],
                    'statut' => (int)$row['statut'],
                    'statut_libelle' => $this->getStatutLibelle((int)$row['statut']),
                    'score_dom' => $row['score_equipe_dom'] !== null ? (int)$row['score_equipe_dom'] : null,
                    'score_ext' => $row['score_equipe_ext'] !== null ? (int)$row['score_equipe_ext'] : null,
                    'equipe_domicile' => [
                        'id' => (int)$row['equipe_dom_id'],
                        'nom' => $row['equipe_dom_nom'],
                        'abreviation' => strtoupper(substr($row['equipe_dom_nom'], 0, 3))
                    ],
                    'equipe_exterieure' => [
                        'id' => (int)$row['equipe_ext_id'],
                        'nom' => $row['equipe_ext_nom'],
                        'abreviation' => strtoupper(substr($row['equipe_ext_nom'], 0, 3))
                    ],
                    'lieu' => [
                        'id' => (int)$row['id_lieu'],
                        'nom' => $row['lieu_nom']
                    ]
                ];
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $matchs,
                'pagination' => [
                    'limit' => $limit,
                    'offset' => $offset,
                    'count' => count($matchs)
                ]
            ]);
        } catch (Exception $e) {
            $this->jsonError('Erreur lors de la récupération des matchs: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/matchs/:id
     * Retourne le détail complet d'un match
     */
    public function getMatchDetails($id)
    {
        try {
            $matchId = (int)$id;
            
            // Récupération du match
            $match = $this->matchModel->findById($matchId);
            if (!$match) {
                $this->jsonError('Match non trouvé', 404);
                return;
            }

            // Récupération des arbitres
            $arbitres = $this->getArbitresDetails($matchId);
            
            // Récupération de l'arbitrage (scores + événements)
            $arbitrage = null;
            try {
                $arbitrage = $this->arbitrageModel->getByMatch($matchId);
            } catch (Exception $e) {
                // Pas d'arbitrage encore
            }

            // Récupération des compositions
            $compositionDom = $this->getCompositionDetails($match->idEquipeDom, $matchId);
            $compositionExt = $this->getCompositionDetails($match->idEquipeExt, $matchId);

            // Construction de la réponse
            $response = [
                'id' => $match->id,
                'date_heure' => $match->dateHeure->format('Y-m-d H:i:s'),
                'statut' => $match->statut,
                'statut_libelle' => $this->getStatutLibelle($match->statut),
                'duree' => $arbitrage ? $arbitrage->tempsJeu : null,
                'score_dom' => $arbitrage ? $arbitrage->scoreDom : null,
                'score_ext' => $arbitrage ? $arbitrage->scoreExt : null,
                'equipe_domicile' => [
                    'id' => $match->idEquipeDom,
                    'nom' => $match->equipe_dom_nom,
                    'abreviation' => strtoupper(substr($match->equipe_dom_nom, 0, 3)),
                    'composition' => $compositionDom
                ],
                'equipe_exterieure' => [
                    'id' => $match->idEquipeExt,
                    'nom' => $match->equipe_ext_nom,
                    'abreviation' => strtoupper(substr($match->equipe_ext_nom, 0, 3)),
                    'composition' => $compositionExt
                ],
                'lieu' => [
                    'id' => $match->idLieu,
                    'nom' => $match->lieu_nom
                ],
                'arbitrage' => [
                    'arbitre_principal' => $arbitres['principal'],
                    'arbitres_assistants' => $arbitres['assistants']
                ],
                'evenements' => $this->getEvenementsTimeline($arbitrage, $match)
            ];

            $this->jsonResponse([
                'success' => true,
                'data' => $response
            ]);
        } catch (Exception $e) {
            $this->jsonError('Erreur lors de la récupération du match: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Récupère les détails des arbitres d'un match
     */
    private function getArbitresDetails($matchId)
    {
        $sql = "
            SELECT 
                m.id_arbitre_principal,
                m.id_arbitre_assistant_1,
                m.id_arbitre_assistant_2,
                ap.nom AS arbitre_principal_nom,
                aa1.nom AS arbitre_assistant_1_nom,
                aa2.nom AS arbitre_assistant_2_nom
            FROM matchs m
            LEFT JOIN arbitres ap ON m.id_arbitre_principal = ap.id_arbitre
            LEFT JOIN arbitres aa1 ON m.id_arbitre_assistant_1 = aa1.id_arbitre
            LEFT JOIN arbitres aa2 ON m.id_arbitre_assistant_2 = aa2.id_arbitre
            WHERE m.id_match = :match_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['match_id' => $matchId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'principal' => $row && $row['arbitre_principal_nom'] ? [
                'id' => (int)$row['id_arbitre_principal'],
                'nom' => $row['arbitre_principal_nom']
            ] : null,
            'assistants' => array_filter([
                $row && $row['arbitre_assistant_1_nom'] ? [
                    'id' => (int)$row['id_arbitre_assistant_1'],
                    'nom' => $row['arbitre_assistant_1_nom']
                ] : null,
                $row && $row['arbitre_assistant_2_nom'] ? [
                    'id' => (int)$row['id_arbitre_assistant_2'],
                    'nom' => $row['arbitre_assistant_2_nom']
                ] : null
            ])
        ];
    }

    /**
     * Récupère la composition détaillée d'une équipe
     */
    private function getCompositionDetails($equipeId, $matchId)
    {
        $sql = "
            SELECT 
                js.id_joueur_selectionne,
                js.titulaire,
                js.id_role,
                js.id_poste_definitif,
                js.id_placement_definitif,
                j.id_joueur,
                j.nom,
                j.prenom,
                j.numero,
                r.nom AS role_nom,
                p.nom AS poste_nom,
                pl.nom AS placement_nom
            FROM joueurs_selectionnes js
            JOIN l_joueurs_selectionnes_matchs ljm ON ljm.id_joueur_selectionne = js.id_joueur_selectionne
            JOIN joueurs j ON j.id_joueur = js.id_joueur
            LEFT JOIN roles r ON r.id_role = js.id_role
            LEFT JOIN postes p ON p.id_poste = js.id_poste_definitif
            LEFT JOIN placements pl ON pl.id_placement = js.id_placement_definitif
            WHERE js.id_equipe = :equipe_id AND ljm.id_match = :match_id
            ORDER BY js.titulaire DESC, j.numero ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'equipe_id' => $equipeId,
            'match_id' => $matchId
        ]);

        $titulaires = [];
        $remplacants = [];
        $capitaine = null;
        $viceCapitaine = null;

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $joueur = [
                'id' => (int)$row['id_joueur'],
                'nom' => $row['nom'],
                'prenom' => $row['prenom'],
                'numero' => (int)$row['numero'],
                'poste' => $row['poste_nom'],
                'placement' => $row['placement_nom'],
                'role' => $row['role_nom']
            ];

            // Déterminer le rôle spécial
            if ((int)$row['id_role'] === 1) { // Capitaine
                $capitaine = $joueur;
            } elseif ((int)$row['id_role'] === 2) { // Vice-capitaine/Suppléant
                $viceCapitaine = $joueur;
            }

            // Répartir en titulaires/remplaçants
            if ((int)$row['titulaire'] === 1) {
                $titulaires[] = $joueur;
            } else {
                $remplacants[] = $joueur;
            }
        }

        return [
            'titulaires' => $titulaires,
            'remplacants' => $remplacants,
            'capitaine' => $capitaine,
            'vice_capitaine' => $viceCapitaine
        ];
    }

    /**
     * Génère la timeline des événements du match
     */
    private function getEvenementsTimeline($arbitrage, $match)
    {
        if (!$arbitrage) {
            return [];
        }

        $events = [];

        // Buts domicile
        foreach ($arbitrage->butsDom as $but) {
            $joueur = $this->getJoueurById($but['joueur_id']);
            $events[] = [
                'minute' => (int)$but['minute'],
                'type' => 'but',
                'equipe' => 'domicile',
                'equipe_id' => $match->idEquipeDom,
                'joueur' => $joueur ? [
                    'id' => $joueur->id,
                    'nom' => $joueur->nom,
                    'prenom' => $joueur->prenom,
                    'numero' => $joueur->numero
                ] : null
            ];
        }

        // Buts extérieur
        foreach ($arbitrage->butsExt as $but) {
            $joueur = $this->getJoueurById($but['joueur_id']);
            $events[] = [
                'minute' => (int)$but['minute'],
                'type' => 'but',
                'equipe' => 'exterieur',
                'equipe_id' => $match->idEquipeExt,
                'joueur' => $joueur ? [
                    'id' => $joueur->id,
                    'nom' => $joueur->nom,
                    'prenom' => $joueur->prenom,
                    'numero' => $joueur->numero
                ] : null
            ];
        }

        // Cartons domicile
        foreach ($arbitrage->cartonsDom as $carton) {
            $joueur = $this->getJoueurById($carton['joueur_id']);
            $events[] = [
                'minute' => (int)$carton['minute'],
                'type' => 'carton_' . $carton['type'],
                'equipe' => 'domicile',
                'equipe_id' => $match->idEquipeDom,
                'joueur' => $joueur ? [
                    'id' => $joueur->id,
                    'nom' => $joueur->nom,
                    'prenom' => $joueur->prenom,
                    'numero' => $joueur->numero
                ] : null
            ];
        }

        // Cartons extérieur
        foreach ($arbitrage->cartonsExt as $carton) {
            $joueur = $this->getJoueurById($carton['joueur_id']);
            $events[] = [
                'minute' => (int)$carton['minute'],
                'type' => 'carton_' . $carton['type'],
                'equipe' => 'exterieur',
                'equipe_id' => $match->idEquipeExt,
                'joueur' => $joueur ? [
                    'id' => $joueur->id,
                    'nom' => $joueur->nom,
                    'prenom' => $joueur->prenom,
                    'numero' => $joueur->numero
                ] : null
            ];
        }

        // Remplacements domicile
        foreach ($arbitrage->subsDom as $sub) {
            $joueurSortant = $this->getJoueurById($sub['out']);
            $joueurEntrant = $this->getJoueurById($sub['in']);
            $events[] = [
                'minute' => (int)$sub['minute'],
                'type' => 'remplacement',
                'equipe' => 'domicile',
                'equipe_id' => $match->idEquipeDom,
                'joueur_sortant' => $joueurSortant ? [
                    'id' => $joueurSortant->id,
                    'nom' => $joueurSortant->nom,
                    'prenom' => $joueurSortant->prenom,
                    'numero' => $joueurSortant->numero
                ] : null,
                'joueur_entrant' => $joueurEntrant ? [
                    'id' => $joueurEntrant->id,
                    'nom' => $joueurEntrant->nom,
                    'prenom' => $joueurEntrant->prenom,
                    'numero' => $joueurEntrant->numero
                ] : null
            ];
        }

        // Remplacements extérieur
        foreach ($arbitrage->subsExt as $sub) {
            $joueurSortant = $this->getJoueurById($sub['out']);
            $joueurEntrant = $this->getJoueurById($sub['in']);
            $events[] = [
                'minute' => (int)$sub['minute'],
                'type' => 'remplacement',
                'equipe' => 'exterieur',
                'equipe_id' => $match->idEquipeExt,
                'joueur_sortant' => $joueurSortant ? [
                    'id' => $joueurSortant->id,
                    'nom' => $joueurSortant->nom,
                    'prenom' => $joueurSortant->prenom,
                    'numero' => $joueurSortant->numero
                ] : null,
                'joueur_entrant' => $joueurEntrant ? [
                    'id' => $joueurEntrant->id,
                    'nom' => $joueurEntrant->nom,
                    'prenom' => $joueurEntrant->prenom,
                    'numero' => $joueurEntrant->numero
                ] : null
            ];
        }

        // Trier par minute
        usort($events, function($a, $b) {
            return $a['minute'] <=> $b['minute'];
        });

        return $events;
    }

    /**
     * Récupère un joueur par son ID
     */
    private function getJoueurById($id)
    {
        return $this->joueurModel->findById($id);
    }

    /**
     * Retourne le libellé du statut
     */
    private function getStatutLibelle($statut)
    {
        switch ($statut) {
            case 1: return 'À compléter';
            case 2: return 'À conclure';
            case 3: return 'Terminé';
            default: return 'Inconnu';
        }
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