<?php
// config/routes.php

return [
    '/' => ['controller' => 'HomeController', 'method' => 'index'],
    '/login' => ['controller' => 'LoginController', 'method' => 'showLoginForm'],
    '/login/submit' => ['controller' => 'LoginController', 'method' => 'login'],
    '/logout' => ['controller' => 'LoginController', 'method' => 'logout'],
    '/matchs' => ['controller' => 'FootballMatchController', 'method' => 'index'],
    '/matchs/view' => ['controller' => 'FootballMatchController', 'method' => 'detailView'],
    '/matchs/create' => ['controller' => 'FootballMatchController', 'method' => 'createForm'],
    '/matchs/store' => ['controller' => 'FootballMatchController', 'method' => 'store'],
    '/matchs/selection' => ['controller' => 'FootballMatchController', 'method' => 'selection'],
    '/matchs/selection/save' => ['controller' => 'FootballMatchController', 'method' => 'updateComposition'],
    '/matchs/arbitrage' => ['controller' => 'FootballMatchController', 'method' => 'arbitrageForm'],
    '/matchs/arbitrage/save' => ['controller' => 'FootballMatchController', 'method' => 'saveArbitrage'],
    '/joueurs' => ['controller' => 'CrudController', 'method' => 'index'],
    
    // Gestion des équipes (admin seulement)
    '/joueurs/equipe/add' => ['controller' => 'CrudController', 'method' => 'addEquipe'],
    '/joueurs/equipe/edit' => ['controller' => 'CrudController', 'method' => 'editEquipe'],
    '/joueurs/equipe/delete' => ['controller' => 'CrudController', 'method' => 'deleteEquipe'],
    
    // Gestion des clubs (admin seulement)
    '/joueurs/club/add' => ['controller' => 'CrudController', 'method' => 'addClub'],
    
    // Gestion des joueurs
    '/joueurs/add' => ['controller' => 'CrudController', 'method' => 'addJoueur'],
    '/joueurs/edit' => ['controller' => 'CrudController', 'method' => 'editJoueur'],
    '/joueurs/delete' => ['controller' => 'CrudController', 'method' => 'deleteJoueur'],
    '/joueurs/transfer' => ['controller' => 'CrudController', 'method' => 'transferJoueur'],
];
