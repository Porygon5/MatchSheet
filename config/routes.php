<?php
// config/routes.php

return [
    '/' => ['controller' => 'HomeController', 'method' => 'index'],
    '/login' => ['controller' => 'AuthController', 'method' => 'login'],
    '/logout' => ['controller' => 'AuthController', 'method' => 'logout'],
    '/matchs' => ['controller' => 'MatchController', 'method' => 'index'],
    '/matchs/view' => ['controller' => 'MatchController', 'method' => 'view'],
];
