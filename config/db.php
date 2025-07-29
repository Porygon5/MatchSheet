<?php
// config/db.php

// Configuration des accès à la base de données
$host = 'localhost';             // adresse d'hôte
$db   = 'matchsheet';            // nom de la base de données
$user = 'root';                  // nom d'utilisateur
$pass = '';                      // mot de passe
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // erreurs explicites
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetch par défaut en tableau associatif
    PDO::ATTR_EMULATE_PREPARES   => false,                  // sécurité + perf
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    return $pdo;
} catch (\PDOException $e) {
    // Pour dev uniquement, à retirer en prod !
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
