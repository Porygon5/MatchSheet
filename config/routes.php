<?php
// config/routes.php

return [
    '/' => ['controller' => 'HomeController', 'method' => 'index'],
    '/login' => ['controller' => 'LoginController', 'method' => 'showLoginForm'],
    '/login/submit' => ['controller' => 'LoginController', 'method' => 'login'],
    '/logout' => ['controller' => 'LoginController', 'method' => 'logout'],
    '/matchs' => ['controller' => 'FootballMatchController', 'method' => 'index'],
    '/matchs/view' => ['controller' => 'FootballMatchController', 'method' => 'view'],
    '/matchs/create' => ['controller' => 'FootballMatchController', 'method' => 'createForm'],
    '/matchs/store' => ['controller' => 'FootballMatchController', 'method' => 'store'],
    '/matchs/selection' => ['controller' => 'FootballMatchController', 'method' => 'selection'],
    '/matchs/selection/save' => ['controller' => 'FootballMatchController', 'method' => 'updateComposition'],
    '/matchs/arbitrage' => ['controller' => 'FootballMatchController', 'method' => 'arbitrageForm'],
    '/matchs/arbitrage/save' => ['controller' => 'FootballMatchController', 'method' => 'saveArbitrage'],
];
