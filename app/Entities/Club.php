<?php
// Entities/Club.php
namespace App\Entities;

final class Club
{
    public int $id;
    public string $nom;
    public string $ville;
    public string $pays;

    public function __construct(array $data)
    {
        $this->id = $data['id_club'];
        $this->nom = $data['nom'];
        $this->ville = $data['ville'];
        $this->pays = $data['pays'];
    }
}