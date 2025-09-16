<?php
// public/api/index.php - Point d'entrée de l'API REST

error_log("URI brut = " . $_SERVER['REQUEST_URI']);
error_log("URI normalisé = " . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Démarrer la session (si nécessaire pour certaines données)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Chargement de la configuration
$pdo = require_once __DIR__ . '/../config/db.php';

// Routes API
$routes = [
    'GET' => [
        '/api/matchs' => ['controller' => 'MatchApiController', 'method' => 'getMatchs'],
        '/api/matchs/(\d+)' => ['controller' => 'MatchApiController', 'method' => 'getMatchDetails'],
        '/api/equipes' => ['controller' => 'EquipeApiController', 'method' => 'getEquipes'],
        '/api/equipes/(\d+)' => ['controller' => 'EquipeApiController', 'method' => 'getEquipeStats'],
        '/api/joueurs/(\d+)' => ['controller' => 'JoueurApiController', 'method' => 'getJoueurDetails'],
    ]
];

// Récupération de l'URI et méthode HTTP
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Fonction pour répondre en JSON
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Fonction pour erreur
function jsonError($message, $status = 400) {
    jsonResponse(['error' => $message, 'status' => $status], $status);
}

// Vérification de la méthode
if (!isset($routes[$method])) {
    jsonError('Méthode HTTP non autorisée', 405);
}

$matched = false;

// Parcours des routes avec regex
foreach ($routes[$method] as $pattern => $route) {
    $pattern = str_replace('/', '\/', $pattern);
    $pattern = '/^' . $pattern . '$/';
    
    if (preg_match($pattern, $uri, $matches)) {
        $controllerName = $route['controller'];
        $methodName = $route['method'];
        
        // Chargement du contrôleur
        $controllerPath = __DIR__ . "/../app/Controllers/Api/$controllerName.php";
        
        if (!file_exists($controllerPath)) {
            jsonError('Contrôleur API non trouvé', 500);
        }
        
        require_once $controllerPath;
        
        if (!class_exists($controllerName)) {
            jsonError('Classe contrôleur non trouvée', 500);
        }
        
        $controller = new $controllerName($pdo);
        
        if (!method_exists($controller, $methodName)) {
            jsonError('Méthode contrôleur non trouvée', 500);
        }
        
        // Extraction des paramètres d'URL (sans le premier match complet)
        $params = array_slice($matches, 1);
        
        // Appel du contrôleur avec les paramètres
        call_user_func_array([$controller, $methodName], $params);
        $matched = true;
        break;
    }
}

if (!$matched) {
    jsonError('Endpoint non trouvé', 404);
}