<?php
// config/routes.php

return [
    '/' => ['controller' => 'HomeController', 'method' => 'index'],
    '/login' => ['controller' => 'AuthController', 'method' => 'login'],
    '/logout' => ['controller' => 'AuthController', 'method' => 'logout'],
    '/matchs' => ['controller' => 'FootballMatchController', 'method' => 'index'],
    '/matchs/view' => ['controller' => 'FootballMatchController', 'method' => 'view'],
    '/matchs/create' => ['controller' => 'FootballMatchController', 'method' => 'createForm'],
    '/matchs/store' => ['controller' => 'FootballMatchController', 'method' => 'store'],
    '/matchs/edit' => ['controller' => 'FootballMatchController', 'method' => 'editForm'],
    '/matchs/update' => ['controller' => 'FootballMatchController', 'method' => 'update'],
];
