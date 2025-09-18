<?php
// app/Controllers/CrudController.php

use App\Models\EquipeModel;
use App\Models\JoueurModel;
use App\Models\PlacementModel;
use App\Models\PosteModel;
use App\Models\ClubModel;
use App\Models\EntraineurModel;
use App\Models\UtilisateurModel;

require_once __DIR__ . '/../Models/EquipeModel.php';
require_once __DIR__ . '/../Models/JoueurModel.php';
require_once __DIR__ . '/../Models/PlacementModel.php';
require_once __DIR__ . '/../Models/PosteModel.php';
require_once __DIR__ . '/../Models/ClubModel.php';
require_once __DIR__ . '/../Models/EntraineurModel.php';
require_once __DIR__ . '/../Models/UtilisateurModel.php';

class CrudController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Affiche la page principale de gestion (équipes + joueurs + utilisateurs)
     * Accessible aux admins et entraîneurs avec des permissions différentes
     */
    public function index()
    {
        // Vérification de connexion
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $user = $_SESSION['user'];
        $role = (int) $user['id_permission'];
        
        // 1 = admin, 2 = entraîneur
        $isAdmin = ($role === 1);
        
        // Récupération des données
        $equipeModel     = new \App\Models\EquipeModel($this->pdo);
        $joueurModel     = new \App\Models\JoueurModel($this->pdo);
        $clubModel       = new \App\Models\ClubModel($this->pdo);
        $entraineurModel = new \App\Models\EntraineurModel($this->pdo);
        $utilisateurModel = new \App\Models\UtilisateurModel($this->pdo);
        
        if ($isAdmin) {
            // Admin voit tout
            $equipes     = $equipeModel->getAll();
            $joueurs     = $joueurModel->getAll();
            $clubs       = $clubModel->getAll();
            $entraineurs = $entraineurModel->getAll();
            $utilisateurs = $utilisateurModel->getAll(); // Récupérer tous les utilisateurs pour l'admin
        } else {
            // Entraîneur voit seulement son équipe
            $coachEquipeId = $equipeModel->findTeamByCoachId($user['id']);
            if (!$coachEquipeId) {
                echo "Aucune équipe assignée.";
                return;
            }
            
            $equipes = [$equipeModel->getById($coachEquipeId)];
            $joueurs = $joueurModel->getByEquipe($coachEquipeId);
            $utilisateurs = []; // Les entraîneurs ne voient pas les utilisateurs
        }

        // Données pour les formulaires
        $placementModel = new \App\Models\PlacementModel($this->pdo);
        $posteModel = new \App\Models\PosteModel($this->pdo);
        $placements = $placementModel->getAll();
        $postes = $posteModel->getAll();

        $title = "Gestion des équipes et joueurs";
        $pageCss = "/assets/pages/crud.css";
        $view = __DIR__ . '/../Views/crud.php';

        require __DIR__ . '/../Views/layout.php';
    }

    /**
     * Ajoute une nouvelle équipe (admin seulement)
     */
    public function addEquipe()
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo "Accès refusé";
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $domicile = isset($_POST['domicile']) ? 1 : 0;
            $id_club = (int)($_POST['id_club'] ?? 0);
            $id_entraineur = !empty($_POST['id_entraineur']) ? (int)$_POST['id_entraineur'] : null;

            if ($nom && $id_club) {
                $equipeModel = new \App\Models\EquipeModel($this->pdo);
                $equipeModel->create($nom, $domicile, $id_club, $id_entraineur);
            }
        }

        header('Location: /joueurs');
        exit;
    }

    /**
     * Modifie une équipe existante (admin seulement)
     */
    public function editEquipe()
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo "Accès refusé";
            return;
        }

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            header('Location: /joueurs');
            exit;
        }

        $clubModel = new \App\Models\ClubModel($this->pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $domicile = isset($_POST['domicile']) ? 1 : 0;
            $id_club = (int)($_POST['id_club'] ?? 0);
            $id_entraineur = !empty($_POST['id_entraineur']) ? (int)$_POST['id_entraineur'] : null;

            if ($nom && $id_club) {
                $equipeModel = new \App\Models\EquipeModel($this->pdo);
                $equipeModel->update($id, $nom, $domicile, $id_club, $id_entraineur);
            }

            header('Location: /joueurs');
            exit;
        }

        // Affichage du formulaire de modification
        $equipeModel = new \App\Models\EquipeModel($this->pdo);
        $equipe = $equipeModel->getById($id);
        $clubs  = $clubModel->getAll();
        
        if (!$equipe) {
            header('Location: /joueurs');
            exit;
        }

        $title = "Modifier l'équipe";
        $pageCss = "/assets/pages/crud.css";
        $view = __DIR__ . '/../Views/edit_equipe.php';

        require __DIR__ . '/../Views/layout.php';
    }

    /**
     * Supprime une équipe (admin seulement)
     */
    public function deleteEquipe()
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo "Accès refusé";
            return;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $equipeModel = new \App\Models\EquipeModel($this->pdo);
            $equipeModel->delete($id);
        }

        header('Location: /joueurs');
        exit;
    }

    /**
     * Ajoute un nouveau joueur
     */
    public function addJoueur()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $nationalite = trim($_POST['nationalite'] ?? '');
            $numero = (int)($_POST['numero'] ?? 0);
            $id_poste = (int)($_POST['id_poste'] ?? 0);
            $id_placement = (int)($_POST['id_placement'] ?? 0);
            $id_equipe = (int)($_POST['id_equipe'] ?? 0);

            $equipeModel = new \App\Models\EquipeModel($this->pdo);

            // Vérifications de base
            if ($nom && $prenom && $numero && $id_poste && $id_placement && $id_equipe) {
                // Vérifier que l'entraîneur ne peut ajouter que dans son équipe
                if (!$this->isAdmin()) {
                    $coachEquipeId = $equipeModel->findTeamByCoachId($_SESSION['user']['id']);
                    if ($id_equipe !== $coachEquipeId) {
                        http_response_code(403);
                        echo "Vous ne pouvez ajouter des joueurs que dans votre équipe";
                        return;
                    }
                }

                $joueurModel = new \App\Models\JoueurModel($this->pdo);
                $joueurModel->create($nom, $prenom, $nationalite, $numero, $id_poste, $id_placement, $id_equipe);
            }
        }

        header('Location: /joueurs');
        exit;
    }

    /**
     * Modifie un joueur existant
     */
    public function editJoueur()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            header('Location: /joueurs');
            exit;
        }

        $joueurModel = new \App\Models\JoueurModel($this->pdo);
        $joueur = $joueurModel->findById($id);
        
        if (!$joueur) {
            header('Location: /joueurs');
            exit;
        }

        $equipeModel = new \App\Models\EquipeModel($this->pdo);

        // Vérifier les permissions entraîneur
        if (!$this->isAdmin()) {
            $coachEquipeId = $this->getCoachTeamId($_SESSION['user']['id']);
            if ($joueur->equipeId !== $coachEquipeId) {
                http_response_code(403);
                echo "Vous ne pouvez modifier que les joueurs de votre équipe";
                return;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $nationalite = trim($_POST['nationalite'] ?? '');
            $numero = (int)($_POST['numero'] ?? 0);
            $id_poste = (int)($_POST['id_poste'] ?? 0);
            $id_placement = (int)($_POST['id_placement'] ?? 0);
            $id_equipe = (int)($_POST['id_equipe'] ?? 0);

            // Pour les entraîneurs, forcer l'équipe actuelle
            if (!$this->isAdmin()) {
                $id_equipe = $joueur->equipeId;
            }

            if ($nom && $prenom && $numero && $id_poste && $id_placement && $id_equipe) {
                $joueurModel->update($id, $nom, $prenom, $nationalite, $numero, $id_poste, $id_placement, $id_equipe);
            }

            header('Location: /joueurs');
            exit;
        }

        $isAdmin = $this->isAdmin();

        // Affichage du formulaire
        $equipeModel = new \App\Models\EquipeModel($this->pdo);
        $placementModel = new \App\Models\PlacementModel($this->pdo);
        $posteModel = new \App\Models\PosteModel($this->pdo);
        
        $equipes = $this->isAdmin() ? $equipeModel->getAll() : [$equipeModel->getById($joueur->equipeId)];
        $placements = $placementModel->getAll();
        $postes = $posteModel->getAll();

        $title = "Modifier le joueur";
        $pageCss = "/assets/pages/crud.css";
        $view = __DIR__ . '/../Views/edit_joueur.php';

        require __DIR__ . '/../Views/layout.php';
    }

    /**
     * Supprime un joueur
     */
    public function deleteJoueur()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            header('Location: /joueurs');
            exit;
        }

        $equipeModel = new \App\Models\EquipeModel($this->pdo);
        $joueurModel = new \App\Models\JoueurModel($this->pdo);
        $joueur = $joueurModel->findById($id);
        
        if (!$joueur) {
            header('Location: /joueurs');
            exit;
        }

        // Vérifier les permissions entraîneur
        if (!$this->isAdmin()) {
            $coachEquipeId = $equipeModel->findTeamByCoachId($_SESSION['user']['id']);
            if ($joueur->equipeId !== $coachEquipeId) {
                http_response_code(403);
                echo "Vous ne pouvez supprimer que les joueurs de votre équipe";
                return;
            }
        }

        $joueurModel->delete($id);
        header('Location: /joueurs');
        exit;
    }

    /**
     * Transfère un joueur vers une autre équipe (admin seulement)
     */
    public function transferJoueur()
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo "Accès refusé";
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_joueur = (int)($_POST['id_joueur'] ?? 0);
            $id_equipe_cible = (int)($_POST['id_equipe_cible'] ?? 0);
            $nouveau_numero = (int)($_POST['nouveau_numero'] ?? 0);

            if ($id_joueur && $id_equipe_cible && $nouveau_numero) {
                $joueurModel = new \App\Models\JoueurModel($this->pdo);
                $joueurModel->transfer($id_joueur, $id_equipe_cible, $nouveau_numero);
            }
        }

        header('Location: /joueurs');
        exit;
    }

    /**
     * Ajoute un nouveau club (admin seulement)
     */
    public function addClub()
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo "Accès refusé";
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $ville = trim($_POST['ville'] ?? '');
            $pays = trim($_POST['pays'] ?? '');

            if ($nom && $ville && $pays) {
                $clubModel = new \App\Models\ClubModel($this->pdo);
                $clubModel->create($nom, $ville, $pays);
            }
        }

        header('Location: /joueurs');
        exit;
    }

    /**
     * Ajoute un nouvel utilisateur (admin seulement)
     */
    public function addUtilisateur()
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo "Accès refusé";
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom_utilisateur = trim($_POST['nom_utilisateur'] ?? '');
            $mot_de_passe = trim($_POST['mot_de_passe'] ?? '');
            $id_permission = (int)($_POST['id_permission'] ?? 1);

            if ($nom_utilisateur && $mot_de_passe) {
                $utilisateurModel = new \App\Models\UtilisateurModel($this->pdo);
                $utilisateurModel->create($nom_utilisateur, $mot_de_passe, $id_permission);
            }
        }

        header('Location: /joueurs');
        exit;
    }

    /**
     * Modifie un utilisateur existant (admin seulement)
     */
    public function editUtilisateur()
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo "Accès refusé";
            return;
        }

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            header('Location: /joueurs');
            exit;
        }

        $utilisateurModel = new \App\Models\UtilisateurModel($this->pdo);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom_utilisateur = trim($_POST['nom_utilisateur'] ?? '');
            $id_permission = (int)($_POST['id_permission'] ?? 1);
            $nouveau_mot_de_passe = trim($_POST['nouveau_mot_de_passe'] ?? '');

            if ($nom_utilisateur) {
                try {
                    $utilisateurModel->updateUser($id, $nom_utilisateur, $id_permission);
                    
                    // Mettre à jour le mot de passe si fourni
                    if ($nouveau_mot_de_passe) {
                        $utilisateurModel->updatePassword($id, $nouveau_mot_de_passe);
                    }
                } catch (Exception $e) {
                    $_SESSION['error'] = "Erreur lors de la modification : " . $e->getMessage();
                }
            }

            header('Location: /joueurs');
            exit;
        }

        // Affichage du formulaire de modification
        $utilisateur = $utilisateurModel->findById($id);
        if (!$utilisateur) {
            header('Location: /joueurs');
            exit;
        }

        $title = "Modifier l'utilisateur";
        $pageCss = "/assets/pages/crud.css";
        $view = __DIR__ . '/../Views/edit_utilisateur.php';

        require __DIR__ . '/../Views/layout.php';
    }

    /**
     * Supprime un utilisateur (admin seulement)
     */
    public function deleteUtilisateur()
    {
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo "Accès refusé";
            return;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            // Empêcher la suppression de son propre compte
            if ($id === (int)$_SESSION['user']['id']) {
                $_SESSION['error'] = "Vous ne pouvez pas supprimer votre propre compte";
                header('Location: /joueurs');
                exit;
            }

            $utilisateurModel = new \App\Models\UtilisateurModel($this->pdo);
            $utilisateurModel->delete($id);
        }

        header('Location: /joueurs');
        exit;
    }

    private function isAdmin(): bool
    {
        return isset($_SESSION['user']) && (int)$_SESSION['user']['id_permission'] === 1;
    }
}