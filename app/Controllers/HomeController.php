<?php
// Controllers/HomeController.php

use App\Models\FootballMatchModel;

class HomeController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function index()
    {
        $title = "Accueil";
        $pageCss = "/assets/pages/index.css";
        $view = __DIR__ . '/../Views/index.php';

        require_once __DIR__ . '/../Models/FootballMatchModel.php';
        $matchModel = new \App\Models\FootballMatchModel($this->db);

        // Récupération des matchs
        $allMatches = $matchModel->getAll();

        // Filtrage pour les prochains matchs (statut = 2) et limitation à 3
        $upcomingMatches = array_filter($allMatches, fn($m) => $m->statut === 2);
        $upcomingMatches = array_slice($upcomingMatches, 0, 3);

        // Filtrage pour les derniers résultats (statut = 3) et limitation à 3
        $lastMatches = array_filter($allMatches, fn($m) => $m->statut === 3);
        $lastMatches = array_slice($lastMatches, 0, 3);

        require __DIR__ . '/../Views/layout.php';
    }
}
