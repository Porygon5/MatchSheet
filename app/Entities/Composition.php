<?php
// Entities/Composition.php
namespace App\Entities;

final class Composition
{
    private int $idEquipe;
    private ?int $idCapitaine;
    private array $entries = [];
    private ?int $idSuppleant;

    public function __construct(
        int $idEquipe,
        ?int $idCapitaine = null,
        array $entries = [],
        ?int $idSuppleant = null
    ) {
        $this->idEquipe    = $idEquipe;
        $this->idCapitaine = $idCapitaine;
        $this->entries     = $entries;
        $this->idSuppleant = $idSuppleant;
    }

    // Getters
    public function getIdEquipe(): int
    {
        return $this->idEquipe;
    }

    public function getIdCapitaine(): ?int
    {
        return $this->idCapitaine;
    }

    public function getEntries(): array
    {
        return $this->entries;
    }

    public function getIdSuppleant(): ?int
    {
        return $this->idSuppleant;
    }

    // Setters
    public function setIdEquipe(int $idEquipe): self
    {
        $this->idEquipe = $idEquipe;
        return $this;
    }

    public function setIdCapitaine(?int $idCapitaine): self
    {
        $this->idCapitaine = $idCapitaine;
        return $this;
    }

    public function setEntries(array $entries): self
    {
        $this->entries = $entries;
        return $this;
    }

    public function setIdSuppleant(?int $idSuppleant): self
    {
        $this->idSuppleant = $idSuppleant;
        return $this;
    }
}
