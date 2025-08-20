<?php
// Controllers/FootballMatchController.php

use App\Models\ArbitreModel;
use App\Models\EquipeModel;
use App\Models\LieuModel;
use App\Models\FootballMatchModel;

require_once __DIR__ . '/../Models/FootballMatchModel.php';

class FootballMatchController
{
    private $model;

    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->model = new FootballMatchModel($pdo);
    }

    public function index()
    {
        // Récupère tous les matchs enrichis
        $matchs = $this->model->getAll();

        // Classe les matchs par statut
        $matchsACompleter = [];
        $matchsAConclure = [];
        $matchsTermines = [];

        foreach ($matchs as $match) {
            if ($match->scoreEquipeDom !== null && $match->scoreEquipeExt !== null) {
                $matchsTermines[] = $match;
            } elseif ($match->idJoueurSelectionne !== null) {
                $matchsAConclure[] = $match;
            } else {
                $matchsACompleter[] = $match;
            }
        }

        $title = "Feuilles de match";
        $pageCss = "/assets/pages/feuilles_match.css";
        $view = __DIR__ . '/../Views/feuilles_match.php';

        require __DIR__ . '/../Views/layout.php';
    }

    public function createForm()
    {
        require_once __DIR__ . '/../Models/EquipeModel.php';
        require_once __DIR__ . '/../Entities/Equipe.php';

        require_once __DIR__ . '/../Models/LieuModel.php';
        require_once __DIR__ . '/../Entities/Lieu.php';

        require_once __DIR__ . '/../Models/ArbitreModel.php';
        require_once __DIR__ . '/../Entities/Arbitre.php';

        $equipeModel = new EquipeModel($this->pdo);

        $equipes = $equipeModel->getAll();

        $lieuModel = new LieuModel($this->pdo);

        $lieux = $lieuModel->getAll();

        $arbitreModel = new ArbitreModel($this->pdo);

        $arbitres = $arbitreModel->getAll();

        $title = "Créer une feuille de match";
        $pageCss = "/assets/pages/creation_feuille.css";
        $view = __DIR__ . '/../Views/creer_feuille.php';

        require __DIR__ . '/../Views/layout.php';
    }

    /**
     * Enregistre les informations du formulaire en base de données.
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupère les champs du formulaire
            $date = $_POST['date'];
            $heure = $_POST['heure'];
            $equipe_dom = $_POST['equipe_dom'];
            $equipe_ext = $_POST['equipe_ext'];
            $lieu = $_POST['lieu_rencontre'];
            $arbitre_central = $_POST['arbitre_central'];
            $assistant1 = $_POST['arbitre_assistant1'];
            $assistant2 = $_POST['arbitre_assistant2'];

            // Combine date + heure
            $dateTime = date('Y-m-d H:i:s', strtotime("$date $heure"));

            // Appelle le model
            $this->model->create([
                'date_heure' => $dateTime,
                'equipe_dom_id' => $equipe_dom,
                'equipe_ext_id' => $equipe_ext,
                'lieu_id' => $lieu,
                'arbitre_central_id' => $arbitre_central,
                'arbitre_assistant_1_id' => $assistant1,
                'arbitre_assistant_2_id' => $assistant2
            ]);

            // Redirection après insertion
            header('Location: /matchs');
            exit;
        }
    }

    public function selection()
    {
        require_once __DIR__ . '/../Entities/Joueur.php';
        require_once __DIR__ . '/../Models/JoueurModel.php';

        require_once __DIR__ . '/../Entities/Placement.php';
        require_once __DIR__ . '/../Models/PlacementModel.php';

        require_once __DIR__ . '/../Entities/Poste.php';
        require_once __DIR__ . '/../Models/PosteModel.php';

        require_once __DIR__ . '/../Models/CompositionModel.php';

        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo "Paramètre 'id' manquant";
            return;
        }

        $matchId = intval($_GET['id']);
        $match = $this->model->findById($matchId);
        
        if (!$match) {
            http_response_code(404);
            echo "Match introuvable";
            return;
        }

        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $user = $_SESSION['user'];
        $role = (int) $user['id_permission'];

        $canEditDom = true;
        $canEditExt = true;
        
        // Si l'utilisateur à les permissions d'entraineur
        if ($role === 2) {
            // On récupère l'équipe depuis la session.
            $coachEquipeId = $_SESSION['user']['coach_equipe_id'] ?? null;

            // Sinon, on la cherche depuis l'identifiant de l'utilisateur
            if ($coachEquipeId === null) {
                require_once __DIR__ . '/../Models/EquipeModel.php';
                $equipeModel = new \App\Models\EquipeModel($this->pdo);
                $coachEquipeId = $equipeModel->findTeamByCoachId( (int) $user['id']);
            }

            // On vérifie que le match contient bien une équipe de l'entraîneur
            if ($coachEquipeId === null ||
            ((int) $match->idEquipeDom !== (int) $coachEquipeId
            && (int) $match->idEquipeExt !== (int) $coachEquipeId)) {
                http_response_code(403);
                echo "Accès refusé : vous n'êtes pas l'entraîneur d'une des équipes de ce match.";
                return;
            }

            $canEditDom = ((int)$match->idEquipeDom === (int)$coachEquipeId);
            $canEditExt = ((int)$match->idEquipeExt === (int)$coachEquipeId);
        }

        $joueurModel = new \App\Models\JoueurModel($this->pdo);
        $joueursDom = $joueurModel->getByEquipe($match->idEquipeDom);
        $joueursExt = $joueurModel->getByEquipe($match->idEquipeExt);

        $placementModel = new \App\Models\PlacementModel($this->pdo);

        $placements = $placementModel->getAll();

        $posteModel = new \App\Models\PosteModel($this->pdo);

        $postes = $posteModel->getAll();

        $compModel = new \App\Models\CompositionModel($this->pdo);

        $domData = $compModel->getComposition((int) $match->idEquipeDom);
        $extData = $compModel->getComposition((int) $match->idEquipeExt);

        $capitaineDom = $domData['capitaine'] ?? null;
        $viceDom = $domData['vice'] ?? null;
        $titDom = $domData['tit'] ?? [];
        $remDom = $domData['rem'] ?? [];
        $posteDomMap = $domData['poste'] ?? [];
        $placeDomMap = $domData['placement'] ?? [];

        $capitaineExt = $extData['capitaine'] ?? null;
        $viceExt = $extData['vice'] ?? null;
        $titExt = $extData['tit'] ?? [];
        $remExt = $extData['rem'] ?? [];
        $posteExtMap = $extData['poste'] ?? [];
        $placeExtMap = $extData['placement'] ?? [];

        $title = "Compléter une feuille de match";
        $pageCss = "/assets/pages/completer_feuille.css";
        $view = __DIR__ . '/../Views/completer_feuille.php';

        require __DIR__ . '/../Views/layout.php';
    }

    /**
     * POST /matchs/selection/submit?id={id_match}
     */
    public function updateComposition()
    {
        $matchId = (int)($_GET['id'] ?? 0);

        // Récupérer le match
        $match = $this->model->findById($matchId);
        if (!$match) {
            header('Location: /matchs');
            exit;
        }

        require_once __DIR__ . '/../Models/CompositionModel.php';
        require_once __DIR__ . '/../Entities/Composition.php';

        // Construire les 2 côtés depuis $_POST
        [$compoDom, $viceDom] = $this->buildCompositionFromPost('dom', (int)$match->idEquipeDom);
        [$compoExt, $viceExt] = $this->buildCompositionFromPost('ext', (int)$match->idEquipeExt);

        $hasDom = !empty($compoDom->getEntries());
        $hasExt = !empty($compoExt->getEntries());

        $compositionModel = new \App\Models\CompositionModel($this->pdo);

        // Rôle utilisateur
        $role = (int)($_SESSION['user']['id_permission'] ?? 0);

        if ($role === 1) {
            // ADMIN peut sauvegarder dom et/ou ext
            if ($hasDom) $compositionModel->enregistrerCompositionEquipe($compoDom, $viceDom);
            if ($hasExt) $compositionModel->enregistrerCompositionEquipe($compoExt, $viceExt);

        } elseif ($role === 2) {
            // COACH peut sauvegarder que son équipe
            $coachEquipeId = $_SESSION['user']['coach_equipe_id'] ?? null;
            if ($coachEquipeId === null) {
                require_once __DIR__ . '/../Models/EquipeModel.php';
                $coachEquipeId = (new \App\Models\EquipeModel($this->pdo))
                    ->findTeamByCoachId((int)$_SESSION['user']['id']);
            }

            if ((int)$match->idEquipeDom === (int)$coachEquipeId) {
                if ($hasDom) $compositionModel->enregistrerCompositionEquipe($compoDom, $viceDom);
            } elseif ((int)$match->idEquipeExt === (int)$coachEquipeId) {
                if ($hasExt) $compositionModel->enregistrerCompositionEquipe($compoExt, $viceExt);
            }
            // sinon : rien à faire
        }

        header('Location: /matchs/selection?id=' . $matchId);
        exit;
    }

    /**
     * Construit une Composition pour un côté à partir de $_POST
     */
    private function buildCompositionFromPost(string $side, int $idEquipe): array
    {
        $tit = array_map('intval', $_POST["titulaire_{$side}"] ?? []);
        $rem = array_map('intval', $_POST["remplacants_{$side}"] ?? []);
        $poste = array_map('intval', $_POST["poste_{$side}"] ?? []);
        $placement = array_map('intval', $_POST["placement_{$side}"] ?? []);

        $cap  = (isset($_POST["capitaine_{$side}"]) && $_POST["capitaine_{$side}"] !== '') ? (int)$_POST["capitaine_{$side}"] : null;
        $vice = (isset($_POST["vice_capitaine_{$side}"]) && $_POST["vice_capitaine_{$side}"] !== '') ? (int)$_POST["vice_capitaine_{$side}"] : null;

        // Fusionne titulaires + remplaçants
        $ids = array_unique(array_merge($tit, $rem));

        if ($cap !== null && !in_array($cap, $ids, true)) {
            $ids[] = $cap;
            $tit[] = $cap;
        }

        if ($vice !== null && !in_array($vice, $ids, true)) {
            $ids[] = $vice;
            // On le considère comme remplaçant par défaut.
        }

        $entries = [];
        foreach ($ids as $idJoueur) {
            $idJoueur = (int)$idJoueur;
            $entries[] = [
                'id_joueur' => $idJoueur,
                'is_titulaire' => in_array($idJoueur, $tit, true) ? 1 : 0,
                'id_poste' => $poste[$idJoueur] ?? null,
                'id_placement' => $placement[$idJoueur] ?? null,
            ];
        }

        $compo = new \App\Entities\Composition($idEquipe, $cap, $entries, $vice);

        return [$compo, $vice];
    }
}
