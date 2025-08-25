<?php
// Controllers/LoginController.php

use App\Models\UtilisateurModel;
use App\Entities\Utilisateur;

require_once __DIR__ . '/../Models/UtilisateurModel.php';
require_once __DIR__ . '/../Entities/Utilisateur.php';

class LoginController
{
    private \PDO $pdo;
    private UtilisateurModel $users;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->users = new UtilisateurModel($pdo);
    }

    /**
     * Affiche le formulaire de connexion.
     * Génère un token CSRF et inclut la vue de connexion dans le layout principal.
     * 
     * @return void
     */
    public function showLoginForm()
    {
        // Génère un token CSRF simple
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $title = "Connexion";
        $pageCss = "/assets/pages/login.css";
        $view = __DIR__ . '/../Views/login.php';

        require __DIR__ . '/../Views/layout.php';
    }

    /**
     * Traite la soumission du formulaire de connexion.
     * Vérifie le token CSRF, valide les identifiants, authentifie l'utilisateur et crée la session.
     * Redirige vers la page des matchs en cas de succès.
     * 
     * @return void
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Méthode non autorisée";
            return;
        }

        // Vérif CSRF
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(400);
            echo "Jeton CSRF invalide.";
            return;
        }

        // Validation basique
        $login    = trim($_POST['nom_utilisateur'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($login === '' || $password === '') {
            echo "Identifiants requis.";
            return;
        }

        // Recherche de l’utilisateur
        $user = $this->users->findByNomUtilisateur($login);
        if (!$user || !$user->verifierMotDePasse($password)) {
            echo "Identifiants incorrects.";
            return;
        }

        // OK-> crée la session utilisateur
        $_SESSION['user'] = [
            'id'              => $user->id(),
            'nom_utilisateur' => $user->nomUtilisateur(),
            'id_permission'   => $user->idPermission(), // ex: 1=admin, 2=coach…
        ];

        // Regénérer l'ID de session
        session_regenerate_id(true);

        // Redirection après login
        header("Location: /matchs");
        exit;
    }

    /**
     * Déconnecte l'utilisateur.
     * Vide la session, supprime le cookie de session et redirige vers la page de connexion.
     * 
     * @return void
     */
    public function logout()
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        header("Location: /login");
        exit;
    }
}
