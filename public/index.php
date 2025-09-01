<?php
// public/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Chargement base de données
$pdo = require_once __DIR__ . '/../config/db.php';

// Chargement des routes
$routes = require_once __DIR__ . '/../config/routes.php';

// Récupération propre de l’URI
$uri = $_SERVER['REQUEST_URI'];
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$uri = str_replace($scriptName, '', $uri);
$uri = strtok($uri, '?'); // supprime GET
$uri = '/' . trim($uri, '/'); // s'assure que ça commence par /

// Pages accessibles sans connexion
$publicPages = ['/', '/login', '/accueil'];
$isLoggedIn = isset($_SESSION['user']);

// Vérification de session
$allowed = false;
foreach ($publicPages as $page) {
    if ($uri === $page || str_starts_with($uri, $page . '/')) {
        $allowed = true;
        break;
    }
}

if (!$isLoggedIn && !$allowed) {
    header('Location: /login');
    exit;
}

// Vérifie que la route existe
if (isset($routes[$uri])) {
    $controllerName = $routes[$uri]['controller'];
    $methodName = $routes[$uri]['method'];

    // Auto-chargement simplifié
    $controllerPath = __DIR__ . "/../app/Controllers/$controllerName.php";
    if (!file_exists($controllerPath)) {
        http_response_code(500);
        die("Contrôleur non trouvé : $controllerName");
    }

    require_once $controllerPath;
    $controller = new $controllerName($pdo);

    if (!method_exists($controller, $methodName)) {
        http_response_code(500);
        die("Méthode $methodName non trouvée dans $controllerName");
    }

    $controller->$methodName();
} else {
    http_response_code(404);
    echo "404 - Page non trouvée (URI : $uri)";
}
