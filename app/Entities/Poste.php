<?php
// Entities/Poste.php

namespace App\Entities;

class Poste
{
    public int $id_poste;
    public string $nom;

    public function __construct(
        int $id_poste,
        string $nom,
    ) {
        $this->id_poste = $id_poste;
        $this->nom = $nom;
    }
}
