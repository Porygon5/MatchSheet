<?php
class AuthController {
    public function login() {
        require_once __DIR__ . '/../Views/login.php';
    }

    public function doLogin() {
        if (!empty($_POST['username']) && !empty($_POST['password'])) {
            $pdo = require_once __DIR__ . '/../../config/db.php';
            $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE nom_utilisateur = ?');
            $stmt->execute([$_POST['username']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            var_dump($user);
        }
    }

    public function logout() {
        return false;
    }
}
