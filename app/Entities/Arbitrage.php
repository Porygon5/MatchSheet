<?php
// app/Entities/Arbitrage.php

namespace App\Entities;

final class Arbitrage
{
    /** Identifiant du match arbitré */
    public int $idMatch;
    /** Scores finaux (null si non saisis) */
    public ?int $scoreDom = null;
    public ?int $scoreExt = null;
    /** Durée de jeu en minutes (null si non saisie) */
    public ?int $tempsJeu = null;

    /**
     * Liste des buts côté domicile.
     * @var array<int, array{joueur_id:int, minute:int, points:int}>
     */
    public array $butsDom = [];

    /** Liste des buts côté extérieur (même structure que $butsDom) */
    public array $butsExt = [];

    /**
     * Liste des cartons côté domicile.
     * @var array<int, array{joueur_id:int, minute:int, type:string}>
     */
    public array $cartonsDom = [];

    /**
     * Liste des cartons côté extérieur.
     * @var array<int, array{joueur_id:int, minute:int, type:string}>
     */
    public array $cartonsExt = [];

    /**
     * Constructeur de l'entité Arbitrage.
     * 
     * @param array{
     *   id_match:int,
     *   score_dom?:int|string|null,
     *   score_ext?:int|string|null,
     *   temps_jeu?:int|string|null,
     *   buts_dom?:array<int, array{joueur_id:int|string, minute:int|string, points?:int|string}>,
     *   buts_ext?:array<int, array{joueur_id:int|string, minute:int|string, points?:int|string}>,
     *   cartons_dom?:array<int, array{joueur_id:int|string, minute:int|string, type:string}>,
     *   cartons_ext?:array<int, array{joueur_id:int|string, minute:int|string, type:string}>
     * } $data
     */
    public function __construct(array $data)
    {
        $this->idMatch  = (int)($data['id_match'] ?? 0);

        if (array_key_exists('score_dom', $data)) {
            $this->scoreDom = $data['score_dom'] !== '' ? (int)$data['score_dom'] : null;
        }

        if (array_key_exists('score_ext', $data)) {
            $this->scoreExt = $data['score_ext'] !== '' ? (int)$data['score_ext'] : null;
        }

        if (array_key_exists('temps_jeu', $data)) {
            $this->tempsJeu = $data['temps_jeu'] !== '' ? (int)$data['temps_jeu'] : null;
        }

        // Affectation buts et cartons.
        $this->butsDom    = $data['buts_dom'] ?? [];
        $this->butsExt    = $data['buts_ext'] ?? [];
        $this->cartonsDom = $data['cartons_dom'] ?? [];
        $this->cartonsExt = $data['cartons_ext'] ?? [];
    }

    /** Retourne true si les scores sont saisis. */
    public function hasScores(): bool
    {
        return $this->scoreDom !== null && $this->scoreExt !== null;
    }
}
