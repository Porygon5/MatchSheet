<?php
// Entities/Placement.php

namespace App\Entities;

class Placement
{
    public int $id_placement;
    public string $nom;

    public function __construct(
        int $id_placement,
        string $nom,
    ) {
        $this->id_placement = $id_placement;
        $this->nom = $nom;
    }
}
