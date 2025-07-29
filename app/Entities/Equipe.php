<?php
// Entities/Equipe.php

namespace App\Entities;

class Equipe
{
    public int $id;
    public string $nom;
    public bool $domicile;
    public int $id_club;
    public ?int $id_entraineur;

    public function __construct(
        int $id,
        string $nom,
        bool $domicile,
        int $id_club,
        ?int $id_entraineur = null
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->domicile = $domicile;
        $this->id_club = $id_club;
        $this->id_entraineur = $id_entraineur;
    }

    public function isDomicile(): bool
    {
        return $this->domicile;
    }

    public function __toString(): string
    {
        return $this->nom . ($this->domicile ? ' (domicile)' : ' (extérieur)');
    }
}
