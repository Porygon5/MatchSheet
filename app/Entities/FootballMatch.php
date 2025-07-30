<?php
// Entities/FootballMatch.php

namespace App\Entities;

use DateTime;
use IntlDateFormatter;

class FootballMatch
{
    public int $id;
    public DateTime $dateHeure;
    public int $idLieu;
    public int $idArbitrePrincipal;
    public int $idArbitreAssistant1;
    public int $idArbitreAssistant2;
    public int $idEquipeExt;
    public ?int $scoreEquipeExt = null;
    public int $idEquipeDom;
    public ?int $scoreEquipeDom = null;
    public ?int $idJoueurSelectionne = null;

    public ?string $equipe_dom_nom = null;
    public ?string $equipe_ext_nom = null;
    public ?string $lieu_nom = null;

    public ?object $equipeDom = null;
    public ?object $equipeExt = null;
    public ?object $lieu = null;

    public string $dateFormatee;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? 0;
        $this->dateHeure = new DateTime($data['date_heure'] ?? 'now');
        $this->idLieu = $data['id_lieu'];
        $this->idArbitrePrincipal = $data['id_arbitre_principal'];
        $this->idArbitreAssistant1 = $data['id_arbitre_assistant_1'];
        $this->idArbitreAssistant2 = $data['id_arbitre_assistant_2'];
        $this->idEquipeExt = $data['id_equipe_ext'];
        $this->scoreEquipeExt = $data['score_equipe_ext'] ?? null;
        $this->idEquipeDom = $data['id_equipe_dom'];
        $this->scoreEquipeDom = $data['score_equipe_dom'] ?? null;
        $this->idJoueurSelectionne = $data['id_joueur_selectionne'] ?? null;

        $this->equipe_dom_nom = $data['equipe_dom_nom'] ?? null;
        $this->equipe_ext_nom = $data['equipe_ext_nom'] ?? null;
        $this->lieu_nom = $data['lieu_nom'] ?? null;

        // Formatage de la date.
        $formatter = new IntlDateFormatter(
            'fr_FR',
            IntlDateFormatter::FULL,
            IntlDateFormatter::SHORT,
            'Europe/Paris',
            IntlDateFormatter::GREGORIAN,
            'EEEE d MMMM y - HH:mm'
        );
        $this->dateFormatee = ucfirst($formatter->format($this->dateHeure));
    }
}
