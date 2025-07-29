<?php
// Entities/Arbitre.php

namespace App\Entities;

class Arbitre
{
    public int $id;
    public string $nom;
    public string $nationalite;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? 0;
        $this->nom = $data['nom'] ?? '';
        $this->nationalite = $data['nationalite'] ?? '';
    }
}
