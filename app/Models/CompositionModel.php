<?php
// Models/CompositionModel.php

namespace App\Models;

use PDO;
use App\Entities\Composition;

require_once __DIR__ . '/../Entities/Composition.php';

class CompositionModel
{
    private PDO $pdo;

    /**
     * Constructeur du modèle CompositionModel.
     *
     * @param PDO $db Instance de connexion à la base de données.
     */
    public function __construct(PDO $db)
    {
        $this->pdo = $db;
    }

    /**
     * Enregistre la composition d'une équipe dans la table joueurs_selectionnes et crée le lien avec le match.
     *
     * Supprime d'abord la composition existante pour l'équipe et le match donnés, puis insère les nouveaux joueurs sélectionnés
     * ainsi que leur rôle (capitaine, vice-capitaine, titulaire, remplaçant).
     *
     * @param Composition $c Objet Composition contenant les informations de la composition à enregistrer.
     * @param int|null $idViceCapitaine Identifiant du vice-capitaine (optionnel).
     * 
     * @return void
     */
    public function enregistrerCompositionEquipe(Composition $c, ?int $idViceCapitaine = null): void
    {
        // Supprimer la compo existante pour cette équipe sur le match actuel
        $del = $this->pdo->prepare("
            DELETE lj
            FROM l_joueurs_selectionnes_matchs lj
            JOIN joueurs_selectionnes js ON js.id_joueur_selectionne = lj.id_joueur_selectionne
            WHERE lj.id_match = :m AND js.id_equipe = :e
        ");
        $del->execute([
            'm' => $c->getIdMatch(),
            'e' => $c->getIdEquipe()
        ]);

        $insJs = $this->pdo->prepare("
            INSERT INTO joueurs_selectionnes (id_poste_definitif, id_placement_definitif, id_role, titulaire, id_joueur, id_equipe)
            VALUES (:poste, :placement, :role, :titulaire, :joueur, :equipe)
        ");

        $insLink = $this->pdo->prepare("
            INSERT INTO l_joueurs_selectionnes_matchs (id_match, id_joueur_selectionne)
            VALUES (:m, :id_js)
        ");

        // Insérer toutes les entrées
        foreach ($c->getEntries() as $row) {
            $idJoueur  = (int)($row['id_joueur'] ?? 0);
            $titulaire = (int)($row['is_titulaire'] ?? 0);

            $idPoste     = isset($row['id_poste']) ? (int)$row['id_poste'] : null;
            $idPlacement = isset($row['id_placement']) ? (int)$row['id_placement'] : null;

            // On calcule le rôle
            $idRole = $this->determineRoleId(
                $idJoueur,
                $titulaire === 1,
                $c->getIdCapitaine(),
                $idViceCapitaine
            );

            $insJs->execute([
                'poste' => $idPoste,
                'placement' => $idPlacement,
                'role' => $idRole,
                'titulaire' => $titulaire,
                'joueur' => $idJoueur,
                'equipe' => $c->getIdEquipe(),
            ]);

            $idJs = (int)$this->pdo->lastInsertId();

            $insLink->execute([
                'm'     => $c->getIdMatch(),
                'id_js' => $idJs
            ]);
        }
    }

    /**
     * Détermine l'identifiant du rôle d'un joueur dans la composition.
     *
     * @param int $idJoueur Identifiant du joueur.
     * @param bool $estTitulaire Indique si le joueur est titulaire.
     * @param int|null $idCapitaine Identifiant du capitaine (optionnel).
     * @param int|null $idViceCapitaine Identifiant du vice-capitaine (optionnel).
     * 
     * @return int Identifiant du rôle (1: capitaine, 2: vice-capitaine, 3: titulaire, 4: remplaçant).
     */
    private function determineRoleId(
        int $idJoueur,
        bool $estTitulaire,
        ?int $idCapitaine,
        ?int $idViceCapitaine
    ): int {
        if ($idCapitaine !== null && $idJoueur === $idCapitaine) {
            return 1; // Capitaine
        }
        if ($idViceCapitaine !== null && $idJoueur === $idViceCapitaine) {
            return 2; // Suppléant
        }
        return $estTitulaire ? 3 : 4; // Joueur titulaire sinon Remplaçant
    }
    
    /**
     * Récupère la composition d'une équipe pour un match donné.
     *
     * Retourne un tableau associatif contenant les identifiants du capitaine, du vice-capitaine, la liste des titulaires,
     * des remplaçants, ainsi que les postes et placements associés à chaque joueur.
     *
     * @param int $idEquipe Identifiant de l'équipe.
     * @param int $idMatch Identifiant du match.
     * 
     * @return array Tableau associatif des informations de composition (capitaine, vice, tit, rem, poste, placement).
     */
    public function getComposition(int $idEquipe, int $idMatch): array
    {
        // Valeurs par défaut si aucune donnée en BDD
        $data = [
            'capitaine' => null,   // id du capitaine
            'vice'      => null,   // id du vice-capitaine (suppléant)
            'tit'       => [],     // titulaires
            'rem'       => [],     // remplaçants
            'poste'     => [],     // id_poste_definitif
            'placement' => [],     // id_placement_definitif
        ];

        $sql = "SELECT js.id_joueur, js.titulaire, js.id_poste_definitif, js.id_placement_definitif, js.id_role
                FROM joueurs_selectionnes js
                JOIN l_joueurs_selectionnes_matchs lj ON lj.id_joueur_selectionne = js.id_joueur_selectionne
                WHERE js.id_equipe = :e AND lj.id_match = :m";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'e' => $idEquipe,
            'm' => $idMatch
        ]);

        while ($row = $stmt->fetch()) {
            $idJoueur  = (int)$row['id_joueur'];
            $titulaire = ((int)$row['titulaire'] === 1);

            // Maps pour les <select>
            if ($row['id_poste_definitif'] !== null) {
                $data['poste'][$idJoueur] = (int)$row['id_poste_definitif'];
            }
            if ($row['id_placement_definitif'] !== null) {
                $data['placement'][$idJoueur] = (int)$row['id_placement_definitif'];
            }

            // Rôles
            $idRole = (int)$row['id_role'];
            if ($idRole === 1) {
                $data['capitaine'] = $idJoueur;
            } elseif ($idRole === 2) {
                $data['vice'] = $idJoueur;
            }

            // Cases à cocher
            if ($titulaire) {
                $data['tit'][] = $idJoueur;
            } else {
                $data['rem'][] = $idJoueur;
            }
        }

        return $data;
    }
}
