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
        $matchs = $this->model->getAll();
        require __DIR__ . '/../Views/feuilles_match.php';
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
}
